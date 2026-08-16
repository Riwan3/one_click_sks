<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    /**
     * Dummy data — ganti dengan query database begitu backend siap.
     */
    public function index(): Response
    {
        $now = Carbon::now();

        return Inertia::render('Portal/Index', [
            'user' => [
                'displayName' => session('dummy_user', 'Guest User'),
                'username' => session('dummy_username', '00000'),
                'jabatan' => 'Data Analyst',
                'unit' => 'MASTER',
                'foto' => null,
            ],
            'greeting' => $this->greetingFor($now),
            'today' => [
                'tanggal' => $this->tanggalIndonesia($now),
                'jam' => $now->format('H:i'),
                'shift' => $now->hour >= 6 && $now->hour < 18 ? 'Shift Pagi' : 'Shift Malam',
            ],
            'modules' => config('modules.list'),
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
        $bulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

        return sprintf(
            '%s, %s %s %s',
            $hari[$now->format('l')],
            $now->format('d'),
            $bulan[$now->format('F')],
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
