<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleModuleAccess;
use App\Models\RolePermission;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    private const BULAN_INDONESIA = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

    /**
     * Titik masuk setelah login. Kalau role punya landing_path (diatur admin di
     * One-Core > Role & Akses), langsung diarahkan ke situ. Selain itu mengikuti
     * pengaturan is_mobile: Home Portal atau dashboard mobile.
     */
    public function index(): Response|RedirectResponse
    {
        $role = Auth::user()->role;

        $landingPath = Role::where('code', $role)->value('landing_path');
        if ($landingPath) {
            return redirect($landingPath);
        }

        if (RolePermission::isMobile($role)) {
            return $this->mobileHome($this->authJabatan() ?? $role, $role);
        }

        $now = Carbon::now();
        $modules = $this->allowedModules($role);

        return Inertia::render('Portal/Index', [
            'user' => $this->currentUser(),
            'greeting' => $this->greetingFor($now),
            'today' => [
                'tanggal' => $this->tanggalIndonesia($now),
                'jam' => $now->format('H:i'),
                'shift' => $now->hour >= 6 && $now->hour < 18 ? 'Shift Pagi' : 'Shift Malam',
            ],
            'modules' => $modules,
            'announcements' => $this->dummyAnnouncements(),
            'rewards' => $this->dummyRewards(),
            'quickAccess' => $this->dummyQuickAccess(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
        ]);
    }

    /**
     * Beranda mobile untuk role non-Home Portal. Daftar shortcut modul mengikuti
     * akses yang diatur admin di One-Core > Role & Akses (RoleModuleAccess).
     */
    private function mobileHome(string $jabatan, string $role): Response
    {
        $isOperator = $jabatan === 'Operator';

        return Inertia::render('GroupLeader/Home', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'tanggal' => now()->format('d M Y'),
            'roster' => $isOperator ? 'N' : '-',
            'shift' => $isOperator ? 'N' : null,
            'unitKerja' => $isOperator ? 'MASTER '.$this->authUnit() : null,
            'speakUp' => $isOperator ? [
                'label' => 'Password Speak Up Check Point · Shift 2',
                'title' => 'Bekerja Harus Sesuai Prosedur',
            ] : [
                'label' => 'Password Speak Up Check Point · Shift 1',
                'title' => 'PPA Jadi Nomor Satu',
            ],
            'shortcuts' => $this->allowedModules($role)->map(fn (array $module, int $i) => [
                'label' => $module['title'],
                'icon' => $module['icon'],
                'href' => "/{$module['key']}",
                'active' => $i === 0,
            ])->values(),
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    /**
     * Modul dari config('modules.list') yang diizinkan untuk role tersebut
     * (diatur dari One-Core > Role & Akses).
     */
    private function allowedModules(string $role): Collection
    {
        $allowedKeys = RoleModuleAccess::allowedModuleKeys($role);

        return collect(config('modules.list'))->filter(fn (array $module) => $allowedKeys->contains($module['key']))->values();
    }

    /**
     * Dummy data — menu Data khusus role Group Leader & Operator, ganti dengan query database begitu backend siap.
     */
    public function mobileData(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';
        $now = Carbon::now();

        return Inertia::render('GroupLeader/Data', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'atr' => [
                'label' => 'ATR Baik',
                'period' => $isOperator ? 'Kuartal 2 (Apr-Jun) 2026' : 'Bulan '.self::BULAN_INDONESIA[$now->format('F')],
            ],
            'compliance' => [
                'label' => 'Status Bersih',
                'desc' => 'Tidak ada SP/SK',
            ],
            'menuItems' => $isOperator ? [
                ['label' => 'Roster Saya', 'icon' => 'Wallet', 'color' => 'bg-amber-100 text-amber-500', 'href' => null],
                ['label' => 'HM Saya', 'icon' => 'Gauge', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-power'],
                ['label' => 'Absensi Saya', 'icon' => 'CalendarCheck', 'color' => 'bg-violet-100 text-violet-500', 'href' => null],
                ['label' => 'MOR Saya', 'icon' => 'BarChart2', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => null],
                ['label' => 'Izin Karyawan', 'icon' => 'ClipboardCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => null],
                ['label' => 'Assessment Saya', 'icon' => 'PenLine', 'color' => 'bg-amber-100 text-amber-500', 'href' => null],
                ['label' => 'Nomor Penting', 'icon' => 'Phone', 'color' => 'bg-blue-100 text-blue-500', 'href' => null],
            ] : [
                ['label' => 'Anak Asuh', 'icon' => 'Users', 'color' => 'bg-blue-100 text-blue-500', 'href' => null],
                ['label' => 'Pembinaan', 'icon' => 'ShieldCheck', 'color' => 'bg-amber-100 text-amber-500', 'href' => null],
                ['label' => 'Izin Karyawan', 'icon' => 'CalendarCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => null],
                ['label' => 'Roster', 'icon' => 'Wallet', 'color' => 'bg-violet-100 text-violet-500', 'href' => null],
                ['label' => 'Setting Manpower & Fleet', 'icon' => 'Wrench', 'color' => 'bg-slate-200 text-slate-500', 'href' => '/one-setting'],
                ['label' => 'OJK & Checkpoint', 'icon' => 'ClipboardCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => null],
                ['label' => 'Assessment Saya', 'icon' => 'PenLine', 'color' => 'bg-amber-100 text-amber-500', 'href' => null],
                ['label' => 'Coaching Clinic V2', 'icon' => 'Activity', 'color' => 'bg-blue-100 text-blue-500', 'href' => null],
                ['label' => 'Monthly Op. Report', 'icon' => 'BarChart2', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => null],
                ['label' => 'OP. Compliance Report', 'icon' => 'FileBarChart2', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-reporting'],
                ['label' => 'Transport & P5M', 'icon' => 'Camera', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => null],
                ['label' => 'Nomor Penting', 'icon' => 'Phone', 'color' => 'bg-blue-100 text-blue-500', 'href' => null],
            ],
            'footerNote' => $isOperator ? ['title' => 'Oneclick Beta', 'subtitle' => 'Un. Released'] : null,
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    /**
     * Dummy data — daftar dokumen aktivity, ganti dengan query database begitu backend siap.
     */
    public function aktivity(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';

        return Inertia::render('GroupLeader/Aktivity', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'category' => 'aktivity_select',
            'categories' => [
                'Change Shift pada CSA Support',
                'Dewatering',
                'Peraturan Perusahaan PT. Putra Perkasa Abadi',
                'IBPR / HIRA',
            ],
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    /**
     * Dummy data — profil operator, ganti dengan query database begitu backend siap.
     */
    public function profil(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';
        $unit = $this->authUnit();

        return Inertia::render('GroupLeader/Profil', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'profile' => [
                'statusSp' => 'Tidak Ada SP Aktif',
                'nrp' => Auth::user()->username,
                'unit' => 'MASTER '.$unit,
                'lokasi' => 'Monte Support',
                'atasan' => 'Belum Ada',
                'telepon' => '081217480286',
                'jabatanLengkap' => $jabatan.' '.$unit,
                'status' => 'Fit',
                'mulaiBekerja' => '-',
                'masaKerja' => '-',
                'atrYear' => 2026,
                'atrPerBulan' => [
                    ['label' => 'Jul', 'value' => null],
                    ['label' => 'Agu', 'value' => '100%'],
                ],
                'catatan' => 'Tidak ada catatan.',
            ],
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    /**
     * Jabatan dari Employee (data HR) kalau akun tertaut ke employee, fallback ke
     * kolom jabatan milik User sendiri.
     */
    private function authJabatan(): ?string
    {
        return Auth::user()->loadMissing('employee')->employee?->jabatan_ss6 ?? Auth::user()->jabatan;
    }

    private function authUnit(): ?string
    {
        return Auth::user()->loadMissing('employee')->employee?->unit ?? Auth::user()->unit;
    }

    /**
     * Item bottom-nav berbeda per role — href null berarti halamannya belum dibuat.
     */
    private function navItemsFor(?string $jabatan): array
    {
        if ($jabatan === 'Operator') {
            return [
                ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'Home', 'href' => '/portal'],
                ['key' => 'data', 'label' => 'Data', 'icon' => 'LayoutGrid', 'href' => '/portal/data'],
                ['key' => 'aktivity', 'label' => 'Aktivity', 'icon' => 'FileText', 'href' => '/portal/aktivity'],
                ['key' => 'profil', 'label' => 'Profil', 'icon' => 'UserRound', 'href' => '/portal/profil'],
                ['key' => 'no-penting', 'label' => 'No. Penting', 'icon' => 'Phone', 'href' => null],
            ];
        }

        return [
            ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'Home', 'href' => '/portal'],
            ['key' => 'data', 'label' => 'Data', 'icon' => 'LayoutGrid', 'href' => '/portal/data'],
            ['key' => 'gmp', 'label' => 'GMP', 'icon' => 'BadgeCheck', 'href' => '/one-gmp'],
            ['key' => 'aktivity', 'label' => 'Aktivity', 'icon' => 'FileText', 'href' => null],
        ];
    }

    private function greetingFor(Carbon $now): string
    {
        $hour = $now->hour;

        return match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }

    private function tanggalIndonesia(Carbon $now): string
    {
        $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];

        return sprintf(
            '%s, %s %s %s',
            $hari[$now->format('l')],
            $now->format('d'),
            self::BULAN_INDONESIA[$now->format('F')],
            $now->format('Y')
        );
    }

    private function dummyAnnouncements(): array
    {
        return [
            ['title' => 'Update Jadwal Roster Bulan Depan', 'date' => now()->subDays(1)->format('d M Y'), 'content' => 'Silakan cek jadwal roster terbaru di menu One-Power sebelum akhir bulan.', 'type' => 'info'],
            ['title' => 'Reminder Absensi Harian', 'date' => now()->subDays(3)->format('d M Y'), 'content' => 'Pastikan absensi harian diinput sebelum pukul 17:00 setiap harinya.', 'type' => 'warning'],
            ['title' => 'Sistem Berhasil Diperbarui', 'date' => now()->subDays(5)->format('d M Y'), 'content' => 'Perbaikan performa dan tampilan pada modul One-Reporting telah selesai.', 'type' => 'success'],
        ];
    }

    private function dummyRewards(): array
    {
        return [
            ['title' => 'Safety Award Q3 2026', 'type' => 'safety', 'date' => now()->subDays(2)->format('d M Y')],
            ['title' => 'Selamat Ulang Tahun!', 'type' => 'birthday', 'date' => now()->subDays(4)->format('d M Y')],
            ['title' => 'Achievement Zero Accident', 'type' => 'achievement', 'date' => now()->subDays(7)->format('d M Y')],
        ];
    }

    private function dummyQuickAccess(): array
    {
        return [
            ['label' => 'Kehadiran', 'icon' => 'CalendarCheck'],
            ['label' => 'Compliance', 'icon' => 'FileText'],
            ['label' => 'Sett Manpower', 'icon' => 'UserCheck'],
            ['label' => 'Coaching Clinic', 'icon' => 'ClipboardCheck'],
            ['label' => 'Pelatihan', 'icon' => 'BookOpen'],
            ['label' => 'Performa', 'icon' => 'BarChart'],
            ['label' => 'Nomor Penting', 'icon' => 'Phone'],
        ];
    }
}
