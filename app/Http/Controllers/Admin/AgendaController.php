<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $priorityFilter = $request->query('priority');
        $statusFilter = $request->query('status');

        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);

        $baseQuery = Agenda::query()->with(['creator', 'updater']);

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($priorityFilter && in_array($priorityFilter, ['low', 'medium', 'high'], true)) {
            $baseQuery->where('priority', $priorityFilter);
        }

        $activeQuery = (clone $baseQuery)->where('is_completed', false);
        $historyQuery = (clone $baseQuery)->where('is_completed', true);

        if ($statusFilter === 'overdue') {
            $activeQuery->whereNotNull('due_date')->where('due_date', '<', $today);
        } elseif ($statusFilter === 'completed') {
            // Hide active list when only completed agendas are requested
            $activeQuery->whereRaw('1 = 0');
        }

        $perPage = (int) $request->query('limit', 5);
        $perPageAktif = 50; // Larger limit to support internal scrolling

        $activeAgendas = $activeQuery
            ->orderByRaw('COALESCE(due_date, CURRENT_DATE + INTERVAL 365 DAY)')
            ->orderByDesc('created_at')
            ->paginate($perPageAktif, ['*'], 'aktif_page')
            ->withQueryString();

        $historyAgendas = $historyQuery
            ->orderByDesc(DB::raw('COALESCE(updated_at, created_at)'))
            ->paginate($perPage, ['*'], 'riwayat_page')
            ->withQueryString();

        $totalAgendas = Agenda::count();
        $activeCount = Agenda::where('is_completed', false)->count();
        $completedCount = Agenda::where('is_completed', true)->count();
        $overdueAgendas = Agenda::where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->count();
        $upcomingAgendas = Agenda::where('is_completed', false)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $nextWeek])
            ->count();

        $priorityBreakdown = Agenda::select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $recentUpdates = Agenda::orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'title', 'updated_at', 'is_completed', 'priority']);

        return view('admin.agenda.index', [
            'activeAgendas' => $activeAgendas,
            'historyAgendas' => $historyAgendas,
            'filters' => [
                'search' => $search,
                'priority' => $priorityFilter,
                'status' => $statusFilter,
                'limit' => $perPage,
            ],
            'stats' => [
                'total' => $totalAgendas,
                'active' => $activeCount,
                'completed' => $completedCount,
                'overdue' => $overdueAgendas,
                'upcoming' => $upcomingAgendas,
                'completion_rate' => $totalAgendas > 0
                    ? round(($completedCount / max($totalAgendas, 1)) * 100)
                    : 0,
            ],
            'priorityBreakdown' => $priorityBreakdown,
            'recentUpdates' => $recentUpdates,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'in:low,medium,high'],
        ]);

        $agenda = Agenda::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'is_completed' => false,
            'created_by' => Auth::id(),
        ]);

        ActivityLogger::log('agenda.created', $agenda, 'Agenda desa ditambahkan', [
            'title' => $agenda->title,
            'due_date' => $agenda->due_date ? Carbon::parse($agenda->due_date)->toDateString() : null,
        ]);

        $message = 'Agenda desa berhasil ditambahkan.';
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'status' => 'success',
                'agenda' => $agenda,
            ]);
        }

        return back()
            ->with('status', $message)
            ->with('status_variant', 'success')
            ->with('status_description', 'Agenda baru tersimpan dan tampil pada daftar.');
    }

    public function update(Request $request, Agenda $agenda): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'in:low,medium,high'],
        ]);

        $agenda->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'] ?? $agenda->priority,
            'updated_by' => Auth::id(),
        ]);

        ActivityLogger::log('agenda.updated', $agenda, 'Agenda desa diperbarui', [
            'title' => $agenda->title,
        ]);

        $message = 'Agenda desa berhasil diperbarui.';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'status' => 'success',
                'agenda' => $agenda,
            ]);
        }

        return back()
            ->with('status', $message)
            ->with('status_variant', 'info')
            ->with('status_description', 'Perubahan agenda telah disimpan.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $agenda->delete();

        ActivityLogger::log('agenda.deleted', $agenda, 'Agenda desa dihapus', [
            'title' => $agenda->title,
        ]);

        return back()
            ->with('status', 'Agenda desa berhasil dihapus.')
            ->with('status_variant', 'warning')
            ->with('status_description', 'Agenda tidak lagi tampil di daftar aktif.');
    }

    public function toggle(Request $request, Agenda $agenda): RedirectResponse
    {
        $agenda->forceFill([
            'is_completed' => ! $agenda->is_completed,
            'updated_by' => Auth::id(),
        ])->save();

        $message = $agenda->is_completed ? 'Agenda ditandai selesai.' : 'Agenda dibuka kembali.';
        $variant = $agenda->is_completed ? 'success' : 'info';
        $description = $agenda->is_completed
            ? 'Agenda dipindahkan ke riwayat selesai.'
            : 'Agenda aktif kembali dan muncul di daftar utama.';

        ActivityLogger::log(
            $agenda->is_completed ? 'agenda.completed' : 'agenda.reopened',
            $agenda,
            $agenda->is_completed ? 'Agenda ditandai selesai' : 'Agenda dibuka kembali',
            ['title' => $agenda->title]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'status_variant' => $variant,
                'status_description' => $description,
            ]);
        }

        return back()
            ->with('status', $message)
            ->with('status_variant', $variant)
            ->with('status_description', $description);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['required', 'integer', 'exists:agendas,id'],
        ]);

        $agendas = Agenda::whereIn('id', $validated['selected_ids'])->get();
        $count = $agendas->count();

        foreach ($agendas as $agenda) {
            $agenda->delete();

            ActivityLogger::log('agenda.deleted', $agenda, 'Agenda desa dihapus (bulk)', [
                'title' => $agenda->title,
            ]);
        }

        return back()
            ->with('status', "{$count} agenda desa berhasil dihapus.")
            ->with('status_variant', 'warning')
            ->with('status_description', 'Agenda terpilih sudah dihapus dari daftar.');
    }
}
