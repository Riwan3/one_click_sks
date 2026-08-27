<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Employee;
use App\Models\OdrMasterAktivitas;
use App\Models\Procedure;
use App\Models\Reward;
use App\Models\Role;
use App\Models\RoleModuleAccess;
use App\Models\RolePermission;
use App\Models\RosterEntry;
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
            'announcements' => $this->activeAnnouncements(),
            'rewards' => $this->activeRewards(),
            'quickAccess' => $this->activeQuickAccess(),
            'notifCounts' => $this->notifCounts(),
        ]);
    }

    /**
     * Beranda mobile untuk role non-Home Portal. Daftar shortcut modul mengikuti
     * akses yang diatur admin di One-Core > Role & Akses (RoleModuleAccess).
     */
    private function mobileHome(string $jabatan, string $role): Response
    {
        $user = Auth::user();
        $isOperator = $jabatan === 'Operator';

        $employee = Employee::where('nrp', $user?->nrp)->first();
        $today = now()->format('Y-m-d');

        $rosterEntry = $employee ? RosterEntry::where('employee_id', $employee->id)->where('tanggal', $today)->first() : null;

        $rosterCode = $rosterEntry?->kode ?? ($employee?->jabatan_roster ?? ($isOperator ? 'N' : '-'));
        $unitKerja = $rosterEntry?->unit ?? ($employee?->unit ? 'MASTER ' . $employee->unit : ($isOperator ? 'MASTER ' . $this->authUnit() : null));

        return Inertia::render('GroupLeader/Home', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'tanggal' => now()->format('d M Y'),
            'roster' => $rosterCode,
            'shift' => $isOperator ? 'N' : null,
            'unitKerja' => $unitKerja,
            'speakUp' => [
                'label' => 'Password Speak Up Check Point · Shift ' . (now()->hour >= 6 && now()->hour < 18 ? '1' : '2'),
                'title' => 'PPA Jadi Nomor Satu',
            ],
            'shortcuts' => $this->allowedModules($role)->map(fn(array $module, int $i) => [
                'label' => $module['title'],
                'icon' => $module['icon'],
                'href' => "/{$module['key']}",
                'active' => $i === 0,
            ])->values(),
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    public function mobileData(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';
        $now = Carbon::now();

        return Inertia::render('GroupLeader/Data', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'atr' => [
                'label' => 'ATR Baik',
                'period' => $isOperator ? 'Kuartal 2 (Apr-Jun) 2026' : 'Bulan ' . self::BULAN_INDONESIA[$now->format('F')],
            ],
            'compliance' => [
                'label' => 'Status Bersih',
                'desc' => 'Tidak ada SP/SK',
            ],
            'menuItems' => $isOperator ? [
                ['label' => 'Roster Saya', 'icon' => 'Wallet', 'color' => 'bg-amber-100 text-amber-500', 'href' => '/one-power/roster/personal'],
                ['label' => 'HM Saya', 'icon' => 'Gauge', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-power/personal-data/hm'],
                ['label' => 'Absensi Saya', 'icon' => 'CalendarCheck', 'color' => 'bg-violet-100 text-violet-500', 'href' => '/one-power/personal-data/attendance'],
                ['label' => 'MOR Saya', 'icon' => 'BarChart2', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => '/one-power/personal-data/mpr'],
                ['label' => 'Izin Karyawan', 'icon' => 'ClipboardCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => '/one-power/personal-data/attendance'],
                ['label' => 'Assessment Saya', 'icon' => 'PenLine', 'color' => 'bg-amber-100 text-amber-500', 'href' => '/one-procedure/evaluasi'],
                ['label' => 'Nomor Penting', 'icon' => 'Phone', 'color' => 'bg-blue-100 text-blue-500', 'href' => '#'],
            ] : [
                ['label' => 'Anak Asuh', 'icon' => 'Users', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-power/employees/list-gl'],
                ['label' => 'Pembinaan', 'icon' => 'ShieldCheck', 'color' => 'bg-amber-100 text-amber-500', 'href' => '/one-power/pembinaan/coaching-counseling'],
                ['label' => 'Izin Karyawan', 'icon' => 'CalendarCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => '/one-power/personal-data/attendance'],
                ['label' => 'Roster', 'icon' => 'Wallet', 'color' => 'bg-violet-100 text-violet-500', 'href' => '/one-power'],
                ['label' => 'Setting Manpower & Fleet', 'icon' => 'Wrench', 'color' => 'bg-slate-200 text-slate-500', 'href' => '/one-setting/settingan'],
                ['label' => 'OJK & Checkpoint', 'icon' => 'ClipboardCheck', 'color' => 'bg-rose-100 text-rose-500', 'href' => '/one-reporting/ojk'],
                ['label' => 'Assessment Saya', 'icon' => 'PenLine', 'color' => 'bg-amber-100 text-amber-500', 'href' => '/one-procedure/evaluasi'],
                ['label' => 'Coaching Clinic V2', 'icon' => 'Activity', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-power/coaching-clinic'],
                ['label' => 'Monthly Op. Report', 'icon' => 'BarChart2', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => '/one-power/personal-data/mpr'],
                ['label' => 'OP. Compliance Report', 'icon' => 'FileBarChart2', 'color' => 'bg-blue-100 text-blue-500', 'href' => '/one-reporting/daily'],
                ['label' => 'Transport & P5M', 'icon' => 'Camera', 'color' => 'bg-emerald-100 text-emerald-500', 'href' => '/one-power/personal-data/attendance'],
                ['label' => 'Nomor Penting', 'icon' => 'Phone', 'color' => 'bg-blue-100 text-blue-500', 'href' => '#'],
            ],
            'footerNote' => $isOperator ? ['title' => 'Oneclick Beta', 'subtitle' => 'Un. Released'] : null,
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    public function aktivity(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';

        $dbCategories = OdrMasterAktivitas::where('aktif', true)
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->toArray();

        $procedureCategories = Procedure::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $mergedCategories = array_values(array_unique(array_filter(array_merge($dbCategories, $procedureCategories))));

        if (empty($mergedCategories)) {
            $mergedCategories = [
                'Change Shift pada CSA Support',
                'Dewatering',
                'Peraturan Perusahaan PT. Putra Perkasa Abadi',
                'IBPR / HIRA',
            ];
        }

        $activities = OdrMasterAktivitas::where('aktif', true)
            ->get(['id', 'nama', 'kategori', 'keterangan'])
            ->toArray();

        return Inertia::render('GroupLeader/Aktivity', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'category' => 'Aktivitas Operasional',
            'categories' => $mergedCategories,
            'activities' => $activities,
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    /**
     * Profil Karyawan Lengkap Parity V1
     */
    public function profil(): Response
    {
        $jabatan = $this->authJabatan();
        $isOperator = $jabatan === 'Operator';
        $unit = $this->authUnit();
        $user = Auth::user();

        return Inertia::render('GroupLeader/Profil', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'profile' => [
                'statusSp' => 'TIDAK ADA SP AKTIF',
                'nrp' => $user->username ?? '260104',
                'unit' => $unit ?: '-',
                'lokasi' => 'Monte Support',
                'atasan' => 'BELUM ADA',
                'telepon' => '081217480286',
                'jabatanLengkap' => strtoupper($jabatan ?: 'ADMIN PRODUKSI'),
                'status' => 'FIT',
                'mulaiBekerja' => '27 Januari 2026',
                'masaKerja' => '7 Bln, 1 Hari',
                'catatan' => 'Tidak ada catatan.',
                'daftarMesin' => [],
                'atrYear' => 2026,
                'atrPerBulan' => [
                    ['label' => 'Feb', 'value' => '100%'],
                    ['label' => 'Mar', 'value' => '100%'],
                    ['label' => 'Apr', 'value' => '100%'],
                    ['label' => 'Mei', 'value' => '100%'],
                    ['label' => 'Jun', 'value' => '100%'],
                    ['label' => 'Jul', 'value' => '100%'],
                    ['label' => 'Agu', 'value' => '100%'],
                ],
                'atrDetailTable' => [
                    ['bulan' => 'Februari', 'atr' => '100%', 'd' => 15, 'n' => 6, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'Maret', 'atr' => '100%', 'd' => 13, 'n' => 4, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'April', 'atr' => '100%', 'd' => 11, 'n' => 17, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'Mei', 'atr' => '100%', 'd' => 12, 'n' => 13, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'Juni', 'atr' => '100%', 'd' => 11, 'n' => 9, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'Juli', 'atr' => '100%', 'd' => 15, 'n' => 13, 's' => 0, 'i' => 0, 'a' => 0],
                    ['bulan' => 'Agustus', 'atr' => '100%', 'd' => 9, 'n' => 10, 's' => 0, 'i' => 0, 'a' => 0],
                ],
            ],
            'navItems' => $this->navItemsFor($jabatan),
            'accentClass' => $isOperator ? 'text-[#7B112C]' : 'text-blue-500',
        ]);
    }

    private function allowedModules(string $role): Collection
    {
        $allowedKeys = RoleModuleAccess::allowedModuleKeys($role);

        return collect(config('modules.list'))->filter(fn(array $module) => $allowedKeys->contains($module['key']))->values();
    }

    private function authJabatan(): ?string
    {
        return Auth::user()->loadMissing('employee')->employee?->jabatan_ss6 ?? Auth::user()->jabatan;
    }

    private function authUnit(): ?string
    {
        return Auth::user()->loadMissing('employee')->employee?->unit ?? Auth::user()->unit;
    }

    private function navItemsFor(?string $jabatan): array
    {
        if ($jabatan === 'Operator') {
            return [
                ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'Home', 'href' => '/portal'],
                ['key' => 'data', 'label' => 'Data', 'icon' => 'LayoutGrid', 'href' => '/portal/data'],
                ['key' => 'aktivity', 'label' => 'Aktivity', 'icon' => 'FileText', 'href' => '/portal/aktivity'],
                ['key' => 'profil', 'label' => 'Profil', 'icon' => 'UserRound', 'href' => '/profil'],
                ['key' => 'no-penting', 'label' => 'No. Penting', 'icon' => 'Phone', 'href' => null],
            ];
        }

        return [
            ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'Home', 'href' => '/portal'],
            ['key' => 'data', 'label' => 'Data', 'icon' => 'LayoutGrid', 'href' => '/portal/data'],
            ['key' => 'gmp', 'label' => 'GMP', 'icon' => 'BadgeCheck', 'href' => '/one-gmp'],
            ['key' => 'aktivity', 'label' => 'Aktivity', 'icon' => 'FileText', 'href' => '/portal/aktivity'],
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

    private function activeAnnouncements(): Collection
    {
        $BULAN_INDONESIA = self::BULAN_INDONESIA;

        $dbAnnouncements = Announcement::latest()
            ->take(5)
            ->get()
            ->map(function ($item) use ($BULAN_INDONESIA) {
                $created = Carbon::parse($item->created_at);
                $bulanIndo = $BULAN_INDONESIA[$created->format('F')] ?? $created->format('F');

                $typeStyle = match (true) {
                    str_contains($item->type, 'Peringatan') => 'warning',
                    str_contains($item->type, 'Informasi') => 'success',
                    default => 'info',
                };

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'date' => sprintf('%s %s %s', $created->format('d'), $bulanIndo, $created->format('Y')),
                    'content' => $item->content,
                    'type' => $typeStyle,
                ];
            });

        return $dbAnnouncements;
    }

    private function activeRewards(): Collection
    {
        $BULAN_INDONESIA = self::BULAN_INDONESIA;

        return Reward::latest('reward_date')
            ->take(5)
            ->get()
            ->map(function ($item) use ($BULAN_INDONESIA) {
                $date = Carbon::parse($item->reward_date);
                $bulanIndo = $BULAN_INDONESIA[$date->format('F')] ?? $date->format('F');

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->category,
                    'date' => sprintf('%s %s %s', $date->format('d'), $bulanIndo, $date->format('Y')),
                    'type' => $item->type,
                    'icon' => match ($item->type) {
                        'birthday' => '🎂',
                        'achievement' => '🏆',
                        'performance' => '🏅',
                        default => '⭐',
                    },
                ];
            });
    }

    private function activeQuickAccess(): array
    {
        return [
            ['label' => 'Kehadiran', 'icon' => 'CalendarCheck', 'url' => '/one-power/personal-data/attendance', 'action' => null],
            ['label' => 'Compliance', 'icon' => 'FileText', 'url' => '/one-reporting/daily', 'action' => null],
            ['label' => 'Sett Manpower', 'icon' => 'UserCheck', 'url' => '/one-setting/settingan', 'action' => null],
            ['label' => 'Coaching Clinic', 'icon' => 'ClipboardCheck', 'url' => '/one-power/coaching-clinic', 'action' => null],
            ['label' => 'Pelatihan', 'icon' => 'BookOpen', 'url' => '/one-academy', 'action' => null],
            ['label' => 'Pengumuman', 'icon' => 'Megaphone', 'url' => '/pengumuman', 'action' => null],
            ['label' => 'Reward Center', 'icon' => 'Trophy', 'url' => '/rewards', 'action' => null],
            ['label' => 'Nomor Penting', 'icon' => 'Phone', 'url' => '#', 'action' => 'nomor-penting'],
        ];
    }
}
