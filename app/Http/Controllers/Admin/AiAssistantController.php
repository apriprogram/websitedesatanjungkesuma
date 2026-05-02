<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use App\Models\Keluarga;
use App\Models\Agenda;
use App\Models\News;
use App\Models\Page;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $apiKey = env('SAMOPOD_API_KEY');
        $baseUrl = rtrim(env('SAMOPOD_BASE_URL', 'https://ai.sumopod.com/v1'), '/');
        $model = env('SAMOPOD_MODEL', 'seed-2-0-lite');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key tidak ditemukan di konfigurasi server.'], 500);
        }

        // Get system context
        $context = $this->getSystemContext();

        // System prompt to set the persona
        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah Asisten AI cerdas untuk Desa Tanjung Kesuma. Kamu membantu administrator desa mengelola data penduduk, agenda, dan informasi publik.
                 
                 DATA REAL-TIME DESA SAAT INI:
                 ' . $context . '
                 
                 INSTRUKSI PENGELOLAAN DATA:
                 Kamu bisa mengelola data dengan menyertakan tag aksi di akhir jawabanmu jika user memintanya:
                 - `[[ACTION:MARK_COMPLETE|id=ID_AGENDA]]` -> Gunakan ini untuk menandai agenda tertentu sebagai selesai.
                 - `[[ACTION:ADD_AGENDA|title=JUDUL|date=YYYY-MM-DD|priority=high/medium/low]]` -> Gunakan ini untuk menambah agenda baru.
                 
                 PENTING: Selalu gunakan format Markdown agar jawaban rapi:
                 - Gunakan **Teks Tebal** untuk poin penting atau judul.
                 - Gunakan List (poin-poin atau angka) untuk rincian.
                 - Gunakan Tabel jika menyajikan data jadwal atau perbandingan.
                 - Gunakan baris baru (line break) yang cukup agar tidak menumpuk.
                 Berikan jawaban yang ramah, profesional, dan dalam Bahasa Indonesia yang baik.'
            ]
        ];

        // Add history if exists
        if ($request->history) {
            foreach ($request->history as $chat) {
                $messages[] = [
                    'role' => $chat['role'],
                    'content' => $chat['content']
                ];
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $request->message
        ];

        try {
            $response = Http::withoutVerifying()  // bypass SSL for local dev
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->timeout(30)
                ->post($baseUrl . '/chat/completions', [
                    'model'       => $model,
                    'messages'    => $messages,
                    'temperature' => 0.7,
                    'max_tokens'  => 1000,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;
                
                if ($content) {
                    // Process any actions found in the content
                    $this->processActions($content);
                    
                    // Clean up the content from action tags for the user
                    $cleanContent = preg_replace('/\[\[ACTION:.*?\]\]/', '', $content);
                    
                    return response()->json(['message' => trim($cleanContent)]);
                }
                Log::warning('AI: Respon kosong dari server. Body: ' . $response->body());
                return response()->json(['error' => 'Server AI memberikan respon kosong.'], 502);
            }

            $errBody = $response->body();
            Log::error('SamoPod AI HTTP Error [' . $response->status() . ']: ' . $errBody);
            return response()->json([
                'error' => 'Server AI: HTTP ' . $response->status() . ' — ' . substr($errBody, 0, 200)
            ], 502);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AI Connection Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Tidak bisa terhubung ke server AI. Pastikan URL endpoint benar: ' . $baseUrl
            ], 503);
        } catch (\Exception $e) {
            Log::error('AI Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Error sistem: ' . $e->getMessage()], 500);
        }
    }

    private function processActions($content)
    {
        preg_match_all('/\[\[ACTION:(.*?)\]\]/', $content, $matches);
        
        if (empty($matches[1])) return;

        foreach ($matches[1] as $actionStr) {
            try {
                $parts = explode('|', $actionStr);
                $actionType = $parts[0];
                $params = [];
                
                for ($i = 1; $i < count($parts); $i++) {
                    if (str_contains($parts[$i], '=')) {
                        list($key, $val) = explode('=', $parts[$i], 2);
                        $params[$key] = $val;
                    }
                }

                switch ($actionType) {
                    case 'MARK_COMPLETE':
                        if (isset($params['id'])) {
                            Agenda::where('id', $params['id'])->update(['is_completed' => true]);
                            Log::info("AI Action: Agenda #{$params['id']} marked as complete.");
                        }
                        break;
                        
                    case 'ADD_AGENDA':
                        if (isset($params['title'])) {
                            Agenda::create([
                                'title' => $params['title'],
                                'due_date' => $params['date'] ?? now()->addDay(),
                                'priority' => $params['priority'] ?? 'medium',
                                'is_completed' => false,
                                'created_by' => auth()->id() ?? 1
                            ]);
                            Log::info("AI Action: New agenda '{$params['title']}' created.");
                        }
                        break;
                }
            } catch (\Exception $e) {
                Log::error("AI Action Failed ({$actionStr}): " . $e->getMessage());
            }
        }
    }

    private function getSystemContext()
    {
        try {
            $totalPenduduk = Penduduk::count();
            $totalLaki = Penduduk::whereHas('jenisKelamin', function($q) { $q->where('nama', 'Laki-laki'); })->count();
            $totalPerempuan = Penduduk::whereHas('jenisKelamin', function($q) { $q->where('nama', 'Perempuan'); })->count();
            $totalKeluarga = Keluarga::count();
            
            $totalNews = News::count();
            $totalPage = Page::count();
            $totalAnnouncement = Announcement::count();
            
            $upcomingAgendas = Agenda::where('is_completed', false)
                ->where('due_date', '>=', now())
                ->orderBy('due_date', 'asc')
                ->take(5)
                ->get()
                ->map(function($a) {
                    return "- [ID: {$a->id}] {$a->title} (Jatuh tempo: " . ($a->due_date ? $a->due_date->format('d-m-Y') : 'N/A') . ")";
                })->implode("\n");

            $context = "
            - Total Penduduk: {$totalPenduduk} (Laki-laki: {$totalLaki}, Perempuan: {$totalPerempuan})
            - Total Keluarga: {$totalKeluarga}
            - Konten: {$totalNews} Berita, {$totalPage} Halaman Statis, {$totalAnnouncement} Pengumuman.
            - Agenda Mendatang:
            " . ($upcomingAgendas ?: "Tidak ada agenda mendatang.") . "
            
            TANGGAL HARI INI: " . now()->format('d-m-Y') . "
            ";

            return $context;
        } catch (\Exception $e) {
            Log::error('AI Context Error: ' . $e->getMessage());
            return "Data sistem tidak tersedia saat ini.";
        }
    }
}
