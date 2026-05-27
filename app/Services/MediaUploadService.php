<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessMediaImage;
use App\Models\MediaItem;
use App\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadService
{
    /**
     * Persist the raw upload to private storage, extract EXIF data,
     * create the MediaItem record, and dispatch the optimisation job.
     */
    public function store(UploadedFile $file, array $meta = []): MediaItem
    {
        $uuid     = (string) Str::uuid();
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $destPath = 'originals/' . $uuid . '.' . $ext;

        // Store original on the private local disk (never publicly accessible)
        $file->storeAs('originals', $uuid . '.' . $ext, ['disk' => 'local']);

        $exif = $this->extractExif($file->getRealPath());

        $item = MediaItem::create([
            'uuid'            => $uuid,
            'title'           => $meta['title'] ?? null,
            'description'     => $meta['description'] ?? null,
            'original_path'   => $destPath,
            'category_id'     => $meta['category_id'] ?? null,
            'is_featured'     => (bool) ($meta['is_featured'] ?? false),
            ...$exif,
        ]);

        // Attach tags (create if they don't exist)
        if (!empty($meta['tags']) && is_array($meta['tags'])) {
            $tagIds = collect($meta['tags'])->map(fn (string $name) =>
                Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                )->id
            );
            $item->tags()->sync($tagIds);
        }

        ProcessMediaImage::dispatch($item->id);

        return $item;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /** Extract EXIF metadata safely — returns empty array on failure. */
    private function extractExif(string $path): array
    {
        if (!function_exists('exif_read_data')) {
            return [];
        }

        try {
            $raw = @exif_read_data($path, sections: 'EXIF,IFD0', arrays: false);
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($raw)) {
            return [];
        }

        return [
            'camera_make'    => $this->safe($raw, 'Make'),
            'camera_model'   => $this->safe($raw, 'Model'),
            'lens'           => $this->safe($raw, 'LensModel') ?? $this->safe($raw, 'UndefinedTag:0xA434'),
            'iso'            => isset($raw['ISOSpeedRatings']) ? (string) $raw['ISOSpeedRatings'] : null,
            'aperture'       => $this->formatApFNumber($raw),
            'shutter_speed'  => $this->formatShutter($raw),
            'focal_length'   => $this->formatFocalLength($raw),
            'taken_at'       => $this->parseExifDate($this->safe($raw, 'DateTimeOriginal')),
        ];
    }

    private function safe(array $raw, string $key): ?string
    {
        $val = $raw[$key] ?? null;
        return is_string($val) ? trim($val) : null;
    }

    private function formatApFNumber(array $raw): ?string
    {
        if (!isset($raw['ApertureFNumber'])) {
            if (!isset($raw['FNumber'])) {
                return null;
            }
            $fn = $raw['FNumber'];
        } else {
            $fn = $raw['ApertureFNumber'];
        }

        if (is_string($fn) && str_contains($fn, '/')) {
            [$n, $d] = explode('/', $fn);
            $val = (int) $d > 0 ? round((int) $n / (int) $d, 1) : null;
            return $val ? 'f/' . $val : null;
        }

        return 'f/' . $fn;
    }

    private function formatShutter(array $raw): ?string
    {
        if (!isset($raw['ExposureTime'])) {
            return null;
        }
        $et = (string) $raw['ExposureTime'];
        if (str_contains($et, '/')) {
            return $et . 's';
        }
        return $et . 's';
    }

    private function formatFocalLength(array $raw): ?string
    {
        if (!isset($raw['FocalLength'])) {
            return null;
        }
        $fl = (string) $raw['FocalLength'];
        if (str_contains($fl, '/')) {
            [$n, $d] = explode('/', $fl);
            $val = (int) $d > 0 ? round((int) $n / (int) $d) : null;
            return $val ? $val . 'mm' : null;
        }
        return $fl . 'mm';
    }

    private function parseExifDate(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        try {
            return \DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $raw)
                ?->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
