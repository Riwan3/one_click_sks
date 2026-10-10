<?php

namespace Tests\Unit;

use App\Services\Mor\MorEngine;
use Tests\TestCase;

/**
 * Perilaku inti mesin MOR yang harus tetap sama dengan V1/Excel.
 * Kesamaan penuh dengan V1 sudah diuji diferensial saat port; tes ini menjaga
 * kasus-kasus kuncinya.
 */
class MorEngineTest extends TestCase
{
    private function op(string $nrp, string $jabatan, array $inputs, bool $hasSp = false): array
    {
        return [
            'nrp' => $nrp,
            'nama' => $nrp,
            'jabatan' => $jabatan,
            'has_sp' => $hasSp,
            'inputs' => $inputs + [
                'ATR' => 0.99, 'HM' => 1.0, 'PDTY (%)' => null, 'TIMESHEET' => 1.0,
                'FUEL' => 0.0, 'TOTAL PEMBINAAN' => 0.0, 'MASA KERJA (BULAN)' => 24,
            ],
        ];
    }

    public function test_lookup_point_mengikuti_match_excel(): void
    {
        $table = [[0.8, 5], [0.9, 10], [0.99, 25]];

        $this->assertSame(0.0, MorEngine::lookupPoint($table, 0.79));   // di bawah kunci terkecil -> 0
        $this->assertSame(5.0, MorEngine::lookupPoint($table, 0.8));    // tepat di kunci
        $this->assertSame(10.0, MorEngine::lookupPoint($table, 0.98));  // kunci terbesar yang <= nilai
        $this->assertSame(25.0, MorEngine::lookupPoint($table, 1.2));
        $this->assertSame(0.0, MorEngine::lookupPoint($table, null));
    }

    public function test_presisi_15_digit_seperti_excel(): void
    {
        // 0.1 + 0.2 = 0.30000000000000004 di PHP, tapi 0.3 di Excel.
        $this->assertSame(10.0, MorEngine::lookupPoint([[0.3, 10]], 0.1 + 0.2));
        $this->assertSame(MorEngine::xl(71.6), MorEngine::xl(71.599999999999994));
    }

    public function test_type_dari_teks_unit(): void
    {
        $this->assertSame('OPERATOR PC 1250/6015', MorEngine::typeFromText('MASTER EX 1250'));
        $this->assertSame('OPERATOR HAULER', MorEngine::typeFromText('master hd 785'));
        $this->assertSame('OPERATOR PC 200', MorEngine::typeFromText('EXCAVATOR 210'));
        $this->assertSame('OPERATOR WATER TRUCK', MorEngine::typeFromText('MASTER WT'));
        $this->assertNull(MorEngine::typeFromText('MEKANIK'));
    }

    public function test_tanggal_masuk_format_odp(): void
    {
        $this->assertSame('2018-10-01', MorEngine::parseTanggal('1-Okt-18'));
        $this->assertSame('2013-06-03', MorEngine::parseTanggal('3-Jun-13'));
        $this->assertSame('2020-08-15', MorEngine::parseTanggal('15-Agu-2020'));
        $this->assertNull(MorEngine::parseTanggal('31-Feb-20'));
        $this->assertNull(MorEngine::parseTanggal(''));
    }

    public function test_skor_ranking_dan_prasyarat_satu_sheet(): void
    {
        $engine = new MorEngine;
        $ops = $engine->scoreSheet('DT', [
            $this->op('A', 'OPERATOR DT 31 - 50T', ['ATR' => 0.99]),
            $this->op('B', 'OPERATOR DT 31 - 50T', ['ATR' => 0.85]),
            $this->op('C', 'OPERATOR DT 31 - 50T', ['ATR' => 0.99], hasSp: true),
            $this->op('D', 'OPERATOR DT 31 - 50T', ['ATR' => 0.99, 'MASA KERJA (BULAN)' => 1]),
        ]);
        $byNrp = array_column($ops, null, 'nrp');

        // A, C, D punya poin sama -> seri di peringkat 1; B lebih rendah.
        $this->assertSame(1, $byNrp['A']['rank']);
        $this->assertSame(1, $byNrp['C']['rank']);
        $this->assertSame(4, $byNrp['B']['rank']);
        $this->assertGreaterThan($byNrp['B']['total'], $byNrp['A']['total']);

        $this->assertSame('OK', $byNrp['A']['prasyarat']);
        $this->assertSame('FALSE', $byNrp['B']['prasyarat']); // ATR < 0.98
        $this->assertSame('SP', $byNrp['C']['prasyarat_gagal'][0]['input']);
        $this->assertSame('MASA KERJA (BULAN)', $byNrp['D']['prasyarat_gagal'][0]['input']);
    }

    public function test_pembinaan_dibatasi_cap(): void
    {
        $engine = new MorEngine;
        [$plus, $minus] = $engine->scoreSheet('DT', [
            $this->op('A', 'OPERATOR DT 31 - 50T', ['TOTAL PEMBINAAN' => 2.0]),
            $this->op('B', 'OPERATOR DT 31 - 50T', ['TOTAL PEMBINAAN' => -8.0]),
        ]);

        $this->assertSame(20.0, (float) $plus['values']['POIN PEMBINAAN']);  // 20 + 2 -> cap 20
        $this->assertSame(12.0, (float) $minus['values']['POIN PEMBINAAN']); // 20 - 8
    }
}
