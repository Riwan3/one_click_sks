<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MonitoringGradeSyncService
{
    private const GRADE_COLUMNS = [
        'nrp', 'nama', 'posisi', 'eg', 'sub_area', 'atr_6_bulan', 'join_date', 'efektif_grade',
        'efektif_position', 'grade_now', 'min_level', 'max_level', 'status_level', 'suite',
        'level_posisi', 'jumlah_versatility', 'requirement_versatility', 'gap_versatility',
        'kesesuaian_versatility', 'masa_kerja', 'masa_kerja_grade_sekarang', 'masa_kerja_posisi_sekarang',
        'masa_grade_3_th_up', 'masa_position_3th_up', 'sertifikasi', 'status_sertifikasi',
        'expired_date_sertifikasi', 'remaining_time', 'remarks_grade',
    ];

    /** Kolom versality TANPA jabatan (kolom ke-3, di-slot terpisah karena posisinya bisa digeser kolom ekstra di sheet). */
    private const VERSALITY_COLUMNS = [
        'nrp', 'nama', 'jabatan', 'versality',
        'grp1_wt_ft_lt', 'grp1_forklift', 'grp1_slurry_pump',
        'grp2_pc200', 'grp2_crane_3t', 'grp2_tyre_handler', 'grp2_bw211d', 'grp2_wa200', 'grp2_sv700',
        'grp3_pc200_amphibi', 'grp3_pc500', 'grp3_d85', 'grp3_gd511', 'grp3_dt_20_30t',
        'grp4_crane_10_20t', 'grp4_telecopic', 'grp4_wa400', 'grp4_pc750', 'grp4_adt_hm400', 'grp4_d155',
        'grp4_wa500', 'grp4_gd705', 'grp4_dt_35_50t', 'grp4_lowboy', 'grp4_dm245',
        'grp5_pc1250', 'grp5_hd465', 'grp5_adt_volvo', 'grp5_crane_50_70t', 'grp5_d375', 'grp5_gd825',
        'grp5_wa600', 'grp5_single_trailer',
        'grp6_pc2000', 'grp6_hd785', 'grp6_wa800', 'grp6_double_trailer',
        'grp7_pc3400', 'grp7_hd1500', 'grp7_d475',
    ];

    public function getStoredUrl(string $key): ?string
    {
        return DB::table('odp_mg_settings')->where('setting_key', $key)->value('setting_value');
    }

    private function storeUrl(string $key, string $url): void
    {
        DB::table('odp_mg_settings')->updateOrInsert(['setting_key' => $key], ['setting_value' => $url]);
    }

    /**
     * Tarik data dari Google Sheets publik (format CSV export) dan replace penuh tabel
     * target. Return null bila sukses, atau pesan error untuk ditampilkan ke admin.
     */
    public function syncGrade(?string $newUrl): ?string
    {
        [$url, $error] = $this->resolveUrl('grade_url', $newUrl);
        if ($error) {
            return $error;
        }

        [$rows, $error] = $this->fetchCsvRows($url);
        if ($error) {
            return $error;
        }

        $inserted = 0;
        $headerFound = false;
        $startIndex = 0;
        $records = [];

        foreach ($rows as $row) {
            if (! $headerFound) {
                foreach ($row as $idx => $colVal) {
                    if (strtolower(trim((string) $colVal)) === 'nrp') {
                        $startIndex = $idx;
                        $headerFound = true;
                        break;
                    }
                }
                continue;
            }

            if ($startIndex > 0) {
                $row = array_slice($row, $startIndex);
            }
            if (trim((string) ($row[0] ?? '')) === '') {
                continue;
            }

            $record = ['created_at' => now(), 'updated_at' => now()];
            foreach (self::GRADE_COLUMNS as $i => $col) {
                $record[$col] = trim((string) ($row[$i] ?? ''));
            }
            $records[] = $record;
            $inserted++;
        }

        if ($inserted === 0) {
            return 'Tidak ada data yang diproses. Pastikan format kolom benar.';
        }

        DB::transaction(function () use ($records) {
            // DELETE (bukan TRUNCATE) supaya tetap transactional & bisa di-rollback penuh jika chunk berikutnya gagal.
            DB::table('odp_mg_grade_v4')->delete();
            foreach (array_chunk($records, 200) as $chunk) {
                DB::table('odp_mg_grade_v4')->insert($chunk);
            }
        });

        if ($newUrl) {
            $this->storeUrl('grade_url', $newUrl);
        }

        return null;
    }

    public function syncVersatility(?string $newUrl): ?string
    {
        [$url, $error] = $this->resolveUrl('versality_url', $newUrl);
        if ($error) {
            return $error;
        }

        [$rows, $error] = $this->fetchCsvRows($url);
        if ($error) {
            return $error;
        }

        $inserted = 0;
        $headerFound = false;
        $startIndex = false;
        $versalityIdx = false;
        $extraCols = 0;
        $records = [];

        foreach ($rows as $row) {
            if (! $headerFound) {
                foreach ($row as $idx => $colVal) {
                    $val = strtolower(trim((string) $colVal));
                    if ($val === 'nrp') {
                        $startIndex = $idx;
                    }
                    if ($val === 'versality') {
                        $versalityIdx = $idx;
                    }
                }
                if ($startIndex !== false) {
                    $headerFound = true;
                    if ($versalityIdx !== false && $versalityIdx > ($startIndex + 2)) {
                        $extraCols = $versalityIdx - ($startIndex + 3);
                    }
                }
                continue;
            }

            if ($startIndex > 0) {
                $row = array_slice($row, $startIndex);
            }
            if ($extraCols > 0 && count($row) > 3) {
                array_splice($row, 3, $extraCols);
            }
            if (trim((string) ($row[0] ?? '')) === '') {
                continue;
            }

            $record = ['created_at' => now(), 'updated_at' => now()];
            foreach (self::VERSALITY_COLUMNS as $i => $col) {
                $record[$col] = trim((string) ($row[$i] ?? ''));
            }
            $records[] = $record;
            $inserted++;
        }

        if ($inserted === 0) {
            return 'Tidak ada data yang diproses. Pastikan format kolom benar.';
        }

        DB::transaction(function () use ($records) {
            DB::table('odp_mg_versality_v5')->delete();
            foreach (array_chunk($records, 200) as $chunk) {
                DB::table('odp_mg_versality_v5')->insert($chunk);
            }
        });

        if ($newUrl) {
            $this->storeUrl('versality_url', $newUrl);
        }

        return null;
    }

    /** @return array{0: ?string, 1: ?string} [url, error] */
    private function resolveUrl(string $settingKey, ?string $newUrl): array
    {
        $url = $newUrl ?: $this->getStoredUrl($settingKey);
        if (! $url) {
            return [null, 'URL tidak valid.'];
        }

        return [$url, null];
    }

    /** @return array{0: array<int, array<int, string>>, 1: ?string} [csv rows, error] */
    public function fetchCsvRows(string $url): array
    {
        if (! preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return [[], 'URL tidak valid.'];
        }

        $spreadsheetId = $matches[1];
        $gid = 0;
        if (preg_match('/gid=([0-9]+)/', $url, $gidMatches)) {
            $gid = $gidMatches[1];
        }
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";

        try {
            $response = Http::timeout(30)->withoutVerifying()->get($csvUrl);
        } catch (\Throwable $e) {
            return [[], 'Gagal menghubungi Google Sheets: '.$e->getMessage()];
        }

        if (! $response->successful() || $response->body() === '') {
            return [[], 'Gagal mengambil data dari Google Sheets (Kosong).'];
        }

        $csvData = $response->body();
        if (str_contains($csvData, '<html') && str_contains($csvData, 'Sign in - Google Accounts')) {
            return [[], 'Spreadsheet belum di-share ke Public (Anyone with the link).'];
        }

        // fgetcsv atas file (bukan parser baris manual) supaya field yang mengandung
        // koma/baris baru terkutip tetap terbaca benar (mis. kolom remarks/sertifikasi).
        $tmpFile = tempnam(sys_get_temp_dir(), 'mg_csv_');
        file_put_contents($tmpFile, $csvData);
        $rows = [];
        $handle = fopen($tmpFile, 'r');
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        unlink($tmpFile);

        return [$rows, null];
    }
}
