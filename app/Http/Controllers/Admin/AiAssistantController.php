<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // System prompt to set the persona
        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah Asisten AI cerdas untuk Desa Tanjung Kesuma. Kamu membantu administrator desa mengelola data penduduk, agenda, dan informasi publik.
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
                    return response()->json(['message' => $content]);
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
}
