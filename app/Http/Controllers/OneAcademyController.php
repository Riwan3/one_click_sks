<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OneAcademyController extends Controller
{
    private const TAHAPAN_OPTIONS = [
        'Pengajuan Baru', 'Menunggu Approval', 'Menunggu Jadwal',
        'Sedang Berlangsung', 'Selesai', 'Lulus', 'Dibatalkan',
    ];

    private const JENIS_OPTIONS = [
        'Fresh Training', 'Skill Up Training', 'Cross Training', 'Similar Training',
        'Uji Kompetensi', 'Refresh Training', 'Orientasi', 'Simperring', 'Groundtest', 'ODP'
    ];

    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $tahapan = (string) $request->string('tahapan');

        $query = DB::table('training_pengajuan as p')
            ->select(
                'p.*',
                DB::raw('(SELECT COUNT(*) FROM training_pengajuan_peserta WHERE pengajuan_id = p.id) as total_peserta')
            )
            ->orderBy('p.created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.type_unit', 'like', "%{$search}%")
                  ->orWhere('p.diajukan_nama', 'like', "%{$search}%")
                  ->orWhere('p.jenis_pengajuan', 'like', "%{$search}%");
            });
        }

        if ($tahapan !== '' && $tahapan !== 'Semua Tahapan') {
            $query->where('p.tahapan', $tahapan);
        }

        $submissionsRaw = $query->get();

        $stats = [
            'total' => DB::table('training_pengajuan')->count(),
            'baru' => DB::table('training_pengajuan')->where('tahapan', 'Pengajuan Baru')->count(),
            'berlangsung' => DB::table('training_pengajuan')->whereIn('tahapan', ['Sedang Berlangsung', 'Menunggu Jadwal', 'Menunggu Approval'])->count(),
            'selesai' => DB::table('training_pengajuan')->whereIn('tahapan', ['Selesai', 'Lulus'])->count(),
        ];

        $formattedSubmissions = $submissionsRaw->map(function ($row) {
            return [
                'id' => $row->id,
                'tanggal' => date('d M Y', strtotime($row->created_at)),
                'jam' => date('H:i', strtotime($row->created_at)),
                'jenisPengajuan' => $row->jenis_pengajuan,
                'typeUnit' => $row->type_unit,
                'diajukanNama' => $row->diajukan_nama,
                'totalPeserta' => (int) $row->total_peserta,
                'tahapan' => $row->tahapan,
                'catatan' => $row->catatan ?: 'Menunggu konfirmasi instruktur.',
            ];
        });

        // Fetch Section Heads, Dept Heads, PJO, Instructors for Approval Dropdowns
        $approvers = [
            'sectionHeads' => DB::table('employees')->select('name', 'nrp')->where('jabatan_ss6', 'like', '%SECTION HEAD%')->orWhere('jabatan_ss6', 'like', '%SH %')->limit(30)->get(),
            'deptHeads' => DB::table('employees')->select('name', 'nrp')->where('jabatan_ss6', 'like', '%DEPT HEAD%')->orWhere('jabatan_ss6', 'like', '%DH %')->limit(30)->get(),
            'pjos' => DB::table('employees')->select('name', 'nrp')->where('jabatan_ss6', 'like', '%PROJECT MANAGER%')->orWhere('jabatan_ss6', 'like', '%PJO%')->limit(30)->get(),
            'instructors' => DB::table('employees')->select('name', 'nrp')->where('jabatan_ss6', 'like', '%OPERATION INSTRUCT%')->limit(30)->get(),
        ];

        return Inertia::render('OneAcademy/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'tahapan' => $tahapan,
                'tahapanOptions' => array_merge(['Semua Tahapan'], self::TAHAPAN_OPTIONS),
            ],
            'submissions' => $formattedSubmissions,
            'jenisOptions' => self::JENIS_OPTIONS,
            'approvers' => $approvers,
        ]);
    }

    public function storePengajuan(Request $request)
    {
        $validated = $request->validate([
            'jenis_pengajuan' => 'required|string',
            'type_unit' => 'required|string',
            'diajukan_nama' => 'required|string',
            'diajukan_nik' => 'nullable|string',
            'diperiksa_nama' => 'required|string',
            'diperiksa_nik' => 'nullable|string',
            'disetujui_nama' => 'required|string',
            'disetujui_nik' => 'nullable|string',
            'diterima_nama' => 'required|string',
            'diterima_nik' => 'nullable|string',
            'peserta' => 'required|array|min:1',
            'peserta.*.nama' => 'required|string',
            'peserta.*.nrp' => 'nullable|string',
            'peserta.*.nik' => 'nullable|string',
            'peserta.*.no_hp' => 'nullable|string',
            'peserta.*.jabatan' => 'nullable|string',
        ]);

        $pengajuanId = DB::table('training_pengajuan')->insertGetId([
            'jenis_pengajuan' => $validated['jenis_pengajuan'],
            'type_unit' => $validated['type_unit'],
            'tahapan' => 'Pengajuan Baru',
            'diajukan_nama' => $validated['diajukan_nama'],
            'diajukan_nik' => $validated['diajukan_nik'] ?? '',
            'diperiksa_nama' => $validated['diperiksa_nama'],
            'diperiksa_nik' => $validated['diperiksa_nik'] ?? '',
            'disetujui_nama' => $validated['disetujui_nama'],
            'disetujui_nik' => $validated['disetujui_nik'] ?? '',
            'diterima_nama' => $validated['diterima_nama'],
            'diterima_nik' => $validated['diterima_nik'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated['peserta'] as $p) {
            if (!empty($p['nama'])) {
                DB::table('training_pengajuan_peserta')->insert([
                    'pengajuan_id' => $pengajuanId,
                    'nama' => $p['nama'],
                    'nrp' => $p['nrp'] ?? '',
                    'nik' => $p['nik'] ?? '',
                    'no_hp' => $p['no_hp'] ?? '',
                    'jabatan' => $p['jabatan'] ?? '',
                    'perusahaan' => 'PUTRA PERKASA ABADI / PRODUKSI',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pengajuan pelatihan berhasil dibuat.');
    }

    public function deletePengajuan($id)
    {
        DB::table('training_pengajuan')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Pengajuan pelatihan berhasil dihapus.');
    }

    public function odpList(Request $request): Response
    {
        $search = trim((string) $request->string('q'));

        $query = DB::table('odp_karyawan as k')
            ->select('k.*', DB::raw('(SELECT COUNT(*) FROM odp_pelatihan WHERE karyawan_id = k.id) as jml_pelatihan'))
            ->orderBy('k.nama', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('k.nama', 'like', "%{$search}%")
                  ->orWhere('k.nrp', 'like', "%{$search}%");
            });
        }

        $karyawanList = $query->paginate(20);

        return Inertia::render('OneAcademy/OdpDatabase', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'search' => $search,
            'karyawanList' => $karyawanList,
        ]);
    }

    public function storeOdpKaryawan(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'nrp' => 'required|string',
            'jabatan' => 'nullable|string',
        ]);

        DB::table('odp_karyawan')->insert([
            'nama' => $validated['nama'],
            'nrp' => $validated['nrp'],
            'jabatan' => $validated['jabatan'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Data ODP Karyawan berhasil disimpan.');
    }
}
