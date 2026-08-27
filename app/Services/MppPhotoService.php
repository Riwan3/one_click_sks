<?php

namespace App\Services;

use App\Models\MppUnitPhoto;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MppPhotoService
{
    private const STORAGE_PATH = 'mpp-photos';

    public function store(array $data): MppUnitPhoto
    {
        $data['created_by'] = auth()->id();

        if (isset($data['file'])) {
            $file = $data['file'];
            $watermarkedPath = $this->uploadWithWatermark($file, $data);
            $data['url'] = $watermarkedPath;
            unset($data['file']);
        }

        return MppUnitPhoto::create($data);
    }

    public function uploadWithWatermark($file, array $metadata): string
    {
        // Store original file
        $filename = $this->generateFilename($metadata);
        $directory = $this->getStorageDirectory($metadata);

        $image = Image::read($file->path());

        // Rotate to EXIF angle if available
        $image = $this->rotateToExifAngle($image);

        // Add watermark
        $this->applyWatermark($image, $metadata);

        // Save watermarked image
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, (string) $image->encode());

        return 'storage/' . $path;
    }

    private function generateFilename(array $metadata): string
    {
        $date = $metadata['tanggal'] ?? now()->format('Y-m-d');
        $shift = $metadata['shift'] ?? 'D';
        $jenis = $metadata['jenis'] ?? 'Unknown';
        $timestamp = now()->format('YmdHis');

        return "{$date}_{$shift}_{$jenis}_{$timestamp}.jpg";
    }

    private function getStorageDirectory(array $metadata): string
    {
        $jenis = $metadata['jenis'] ?? 'Unknown';
        $year = $metadata['tanggal'] ? date('Y', strtotime($metadata['tanggal'])) : date('Y');
        $month = $metadata['tanggal'] ? date('m', strtotime($metadata['tanggal'])) : date('m');

        return self::STORAGE_PATH . '/' . $jenis . '/' . $year . '/' . $month;
    }

    private function rotateToExifAngle($image): object
    {
        // Intervention\Image v3 handles EXIF rotation automatically
        // This is a placeholder for manual EXIF handling if needed
        return $image;
    }

    private function applyWatermark($image, array $metadata): void
    {
        $width = $image->width();
        $height = $image->height();
        $fontSize = max(20, (int) ($width / 40));

        // Prepare watermark text
        $nama = $metadata['nama'] ?? 'Unknown';
        $tanggal = $metadata['tanggal'] ?? now()->format('d-m-Y');
        $shift = $metadata['shift'] ?? 'D';
        $watermarkText = "{$nama} | {$tanggal} | Shift {$shift}";

        // Add semi-transparent background for text readability
        $image->drawRectangle(
            50,
            $height - 100,
            $width - 50,
            $height - 50,
            function ($draw) {
                $draw->background('rgba(0, 0, 0, 0.5)');
            }
        );

        // Add watermark text
        $image->text(
            $watermarkText,
            (int) ($width / 2),
            $height - 75,
            function ($font) use ($fontSize) {
                $font->size($fontSize);
                $font->color('ffffff');
                $font->align('center');
                $font->valign('middle');
            }
        );
    }

    public function delete(MppUnitPhoto $photo): bool
    {
        if ($photo->url && str_starts_with($photo->url, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $photo->url));
        }

        return $photo->delete();
    }

    public function getPhotosByJenis(string $jenis, int $limit = 20)
    {
        return MppUnitPhoto::byJenis($jenis)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getPhotosByEmployee(string $nrp, ?string $jenis = null)
    {
        $query = MppUnitPhoto::where('nrp', $nrp);

        if ($jenis) {
            $query->byJenis($jenis);
        }

        return $query->latest()->get();
    }

    public function deletePhoto(MppUnitPhoto $photo): bool
    {
        return $this->delete($photo);
    }

    public function zipPhotosForEmployee(string $nrp): string
    {
        $photos = $this->getPhotosByEmployee($nrp);

        if ($photos->isEmpty()) {
            throw new \Exception("No photos found for employee {$nrp}");
        }

        $zipPath = storage_path('app/temp/employee_photos_' . $nrp . '_' . now()->timestamp . '.zip');
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Failed to create ZIP file");
        }

        foreach ($photos as $photo) {
            $filePath = public_path($photo->url);
            if (file_exists($filePath)) {
                $filename = basename($photo->url);
                $zip->addFile($filePath, $filename);
            }
        }

        $zip->close();

        return $zipPath;
    }
}
