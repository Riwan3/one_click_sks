<?php

namespace App\Support;

/**
 * Parser teks alokasi PIC dari chat WA / sheet lama / copy-paste Excel (Setting GL v1).
 * Setiap hasil berupa ['area' => ..., 'unit' => ..., 'pic' => ...].
 */
class OdrGlAllocationParser
{
    /**
     * Box "Penanggung Jawab Front": judul Area lalu baris "E 1234  Nama",
     * atau kolom Tab (Unit+PIC / Area+Unit+PIC).
     */
    public function front(string $raw): array
    {
        $rows = [];
        $currentArea = '';

        foreach ($this->lines($raw) as $line) {
            $tab = $this->tabLine($line, 'front');
            if ($tab !== null) {
                if ($tab) $rows[] = $tab;
                continue;
            }

            $text = $this->stripWaFormat($line);
            if ($text === '') continue;

            if (preg_match('/^[Ee]\s*(\d{3,6})\s+(.+)$/u', $text, $m)) {
                $rows[] = ['area' => $currentArea, 'unit' => 'E'.$m[1], 'pic' => trim($m[2])];
                continue;
            }

            $currentArea = $text;
        }

        return $rows;
    }

    /**
     * Box "Penanggung Jawab Disposal": judul Area, "Pic : Nama", lalu "- E1234" per unit
     * (baris "Fleet :" diabaikan). Area yang hanya punya Pic tanpa unit tetap disimpan dengan unit kosong.
     * Atau kolom Tab (Area+PIC / Area+Unit+PIC).
     */
    public function disposal(string $raw): array
    {
        $rows = [];
        $area = '';
        $pic = '';
        $picHasUnit = false;

        $flushAreaPic = function () use (&$rows, &$area, &$pic, &$picHasUnit) {
            if ($area !== '' && $pic !== '' && ! $picHasUnit) $rows[] = ['area' => $area, 'unit' => '', 'pic' => $pic];
        };

        foreach ($this->lines($raw) as $line) {
            $tab = $this->tabLine($line, 'disposal');
            if ($tab !== null) {
                if ($tab) $rows[] = $tab;
                continue;
            }

            $text = $this->stripWaFormat($line);
            if ($text === '' || preg_match('/^Fleet\s*[:;]?\s*$/ui', $text)) continue;

            if (preg_match('/^Pic\s*:\s*(.+)$/ui', $text, $m)) {
                $flushAreaPic();
                $pic = trim($m[1], "_* \t");
                $picHasUnit = false;
                continue;
            }

            if (preg_match('/^[-–]\s*[Ee]\s*(\d{3,6})/u', $text, $m)) {
                if ($pic === '') continue;
                $rows[] = ['area' => $area, 'unit' => 'E'.$m[1], 'pic' => $pic];
                $picHasUnit = true;
                continue;
            }

            $flushAreaPic();
            $area = $text;
            $pic = '';
            $picHasUnit = false;
        }
        $flushAreaPic();

        return $rows;
    }

    private function lines(string $raw): array
    {
        return array_filter(preg_split('/\r?\n/', $raw), fn ($line) => trim($line) !== '');
    }

    /** Baris kolom Tab (copy dari Excel). null = bukan baris Tab; [] = baris Tab tak valid. */
    private function tabLine(string $line, string $type): ?array
    {
        if (! str_contains($line, "\t")) return null;
        $cols = array_map('trim', explode("\t", $line));

        $parsed = match (true) {
            count($cols) >= 3 => ['area' => $cols[0], 'unit' => $cols[1], 'pic' => $cols[2]],
            count($cols) === 2 && $type === 'front' => ['area' => '', 'unit' => $cols[0], 'pic' => $cols[1]],
            count($cols) === 2 && $type === 'disposal' => ['area' => $cols[0], 'unit' => '', 'pic' => $cols[1]],
            default => null,
        };
        if ($parsed === null) return null;

        return $parsed['pic'] === '' || ($parsed['area'] === '' && $parsed['unit'] === '') ? [] : $parsed;
    }

    /** Buang format WhatsApp (_italic_ / *bold*) di tepi baris. */
    private function stripWaFormat(string $line): string
    {
        return trim(trim(trim($line), '_*'));
    }
}
