<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Support\AvatarStorage;
use App\Support\ActivityLogger;

class PegawaiController extends Controller
{
    private const ALLOWED_STATUSES = ['Aktif', 'Cuti', 'Pensiun', 'Tidak Aktif'];

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $validated = $request->validate(
            $this->rules(),
            [],
            $this->attributes()
        );

        $payload = $this->basePayload($validated);
        $payload['status'] = $this->normalizeStatus($validated['status'] ?? 'Aktif');
        $payload['created_by'] = $request->user()->id;
        $payload['updated_by'] = $request->user()->id;

        AvatarStorage::normalizePegawaiAvatars();

        $payload = array_merge($payload, $this->handleUploadedFiles($request));

        $pegawai = Pegawai::create($payload);

        ActivityLogger::log('pegawai.created', $pegawai, 'Pegawai desa ditambahkan', [
            'nama' => $pegawai->nama,
            'jabatan' => $pegawai->jabatan,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'pegawai'])
            ->with('status', 'Data pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $validated = $request->validate(
            $this->rules($pegawai),
            [],
            $this->attributes()
        );

        $payload = $this->basePayload($validated);
        $payload['status'] = $this->normalizeStatus($validated['status'] ?? 'Aktif');
        $payload['updated_by'] = $request->user()->id;

        AvatarStorage::normalizePegawaiAvatars();

        $payload = array_merge($payload, $this->handleUploadedFiles($request, $pegawai));

        $pegawai->update($payload);

        ActivityLogger::log('pegawai.updated', $pegawai, 'Data pegawai diperbarui', [
            'nama' => $pegawai->nama,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'pegawai'])
            ->with('status', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Request $request, Pegawai $pegawai): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        AvatarStorage::deletePegawaiAvatar($pegawai->gambar);
        $this->deleteFile($pegawai->foto_ktp);
        $this->deleteFile($pegawai->sk_pengangkatan);
        $this->deleteFile($pegawai->sk_pemberhentian);

        $pegawaiName = $pegawai->nama;
        $pegawaiJabatan = $pegawai->jabatan;

        $pegawai->delete();

        ActivityLogger::log('pegawai.deleted', $pegawai, 'Pegawai dihapus', [
            'nama' => $pegawaiName,
            'jabatan' => $pegawaiJabatan,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'pegawai'])
            ->with('status', 'Data pegawai berhasil dihapus.');
    }

    private function rules(?Pegawai $pegawai = null): array
    {
        $nikRule = ['required', 'string', 'max:20'];
        $nikUnique = Rule::unique('pegawais', 'nik');
        if ($pegawai) {
            $nikUnique->ignore($pegawai->id);
        }
        $nikRule[] = $nikUnique;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => $nikRule,
            'nip' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nomor_hp' => ['required', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'status' => ['required', Rule::in(self::ALLOWED_STATUSES)],
            'masa_jabatan_mulai' => ['nullable', 'date'],
            'masa_jabatan_selesai' => ['nullable', 'date', 'after_or_equal:masa_jabatan_mulai'],
            'universitas' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'tahun_lulus' => ['nullable', 'regex:/^(?:\d{4})?$/'],
            'sertifikat_pelatihan' => ['nullable', 'string'],
            'bahasa' => ['nullable', 'string', 'max:255'],
            'last_login' => ['nullable', 'date'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_ktp' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'sk_pengangkatan' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'sk_pemberhentian' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'remove_gambar' => ['nullable', 'boolean'],
            'remove_foto_ktp' => ['nullable', 'boolean'],
            'remove_sk_pengangkatan' => ['nullable', 'boolean'],
            'remove_sk_pemberhentian' => ['nullable', 'boolean'],
        ];
    }

    private function attributes(): array
    {
        return [
            'nama' => 'Nama',
            'nik' => 'NIK',
            'nip' => 'NIP',
            'jabatan' => 'Jabatan',
            'email' => 'Email',
            'nomor_hp' => 'Nomor HP',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'jenis_kelamin' => 'Jenis kelamin',
            'agama' => 'Agama',
            'alamat' => 'Alamat',
            'status' => 'Status kepegawaian',
            'masa_jabatan_mulai' => 'Masa jabatan mulai',
            'masa_jabatan_selesai' => 'Masa jabatan selesai',
            'universitas' => 'Universitas',
            'pendidikan_terakhir' => 'Pendidikan terakhir',
            'tahun_lulus' => 'Tahun lulus',
            'sertifikat_pelatihan' => 'Sertifikat pelatihan',
            'bahasa' => 'Bahasa',
            'last_login' => 'Terakhir login',
            'gambar' => 'Foto profil',
            'foto_ktp' => 'Foto KTP',
            'sk_pengangkatan' => 'SK Pengangkatan',
            'sk_pemberhentian' => 'SK Pemberhentian',
        ];
    }

    private function basePayload(array $validated): array
    {
        return [
            'nama' => $validated['nama'],
            'nik' => $validated['nik'],
            'nip' => $validated['nip'] ?? null,
            'jabatan' => $validated['jabatan'],
            'email' => $validated['email'],
            'nomor_hp' => $validated['nomor_hp'],
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $this->parseDate($validated['tanggal_lahir'] ?? null),
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'agama' => $validated['agama'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'masa_jabatan_mulai' => $this->parseDate($validated['masa_jabatan_mulai'] ?? null),
            'masa_jabatan_selesai' => $this->parseDate($validated['masa_jabatan_selesai'] ?? null),
            'universitas' => $validated['universitas'] ?? null,
            'pendidikan_terakhir' => $validated['pendidikan_terakhir'] ?? null,
            'tahun_lulus' => $validated['tahun_lulus'] ?? null,
            'sertifikat_pelatihan' => $validated['sertifikat_pelatihan'] ?? null,
            'bahasa' => $validated['bahasa'] ?? null,
            'last_login' => $this->parseDateTime($validated['last_login'] ?? null),
        ];
    }

    private function handleUploadedFiles(Request $request, ?Pegawai $pegawai = null): array
    {
        $result = [];

        if ($request->boolean('remove_gambar') && $pegawai?->gambar) {
            AvatarStorage::deletePegawaiAvatar($pegawai->gambar);
            $result['gambar'] = null;
        }

        if ($request->boolean('remove_foto_ktp') && $pegawai?->foto_ktp) {
            $this->deleteFile($pegawai->foto_ktp);
            $result['foto_ktp'] = null;
        }

        if ($request->boolean('remove_sk_pengangkatan') && $pegawai?->sk_pengangkatan) {
            $this->deleteFile($pegawai->sk_pengangkatan);
            $result['sk_pengangkatan'] = null;
        }

        if ($request->boolean('remove_sk_pemberhentian') && $pegawai?->sk_pemberhentian) {
            $this->deleteFile($pegawai->sk_pemberhentian);
            $result['sk_pemberhentian'] = null;
        }

        if ($request->hasFile('gambar')) {
            AvatarStorage::deletePegawaiAvatar($pegawai?->gambar);
            $result['gambar'] = AvatarStorage::storePegawaiAvatar($request->file('gambar'));
        }

        if ($request->hasFile('foto_ktp')) {
            if ($pegawai?->foto_ktp) {
                $this->deleteFile($pegawai->foto_ktp);
            }
            $result['foto_ktp'] = safe_store($request->file('foto_ktp'), 'pegawai/foto-ktp');
        }

        if ($request->hasFile('sk_pengangkatan')) {
            if ($pegawai?->sk_pengangkatan) {
                $this->deleteFile($pegawai->sk_pengangkatan);
            }
            $result['sk_pengangkatan'] = safe_store($request->file('sk_pengangkatan'), 'pegawai/dokumen');
        }

        if ($request->hasFile('sk_pemberhentian')) {
            if ($pegawai?->sk_pemberhentian) {
                $this->deleteFile($pegawai->sk_pemberhentian);
            }
            $result['sk_pemberhentian'] = safe_store($request->file('sk_pemberhentian'), 'pegawai/dokumen');
        }

        return $result;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            try {
                if (file_exists(public_path('storage/' . $path))) {
                    @unlink(public_path('storage/' . $path));
                }
            } catch (\Exception $e) {
                // Abaikan error
            }
        }
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function normalizeStatus(?string $status): string
    {
        $value = trim((string) $status);
        if ($value === '') {
            return 'Aktif';
        }

        $normalized = strtolower(str_replace(['_', '-'], ' ', $value));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?: $normalized;

        return match ($normalized) {
            'aktif', 'active', 'masih bekerja' => 'Aktif',
            'cuti' => 'Cuti',
            'pensiun', 'purna tugas', 'purna bakti' => 'Pensiun',
            'tidak aktif', 'non aktif', 'nonaktif', 'sudah tidak bekerja', 'tidak bekerja', 'berhenti', 'berhenti bekerja' => 'Tidak Aktif',
            default => 'Aktif',
        };
    }
}
