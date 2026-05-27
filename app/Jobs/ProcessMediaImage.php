<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MediaItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

/**
 * Converts an uploaded original image to WebP + AVIF, creates responsive
 * srcset sizes, generates a LQIP placeholder, and strips large ICC profiles
 * from the public copies (EXIF already saved to DB before dispatch).
 */
class ProcessMediaImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    // Responsive breakpoints (width in px)
    private const SRCSET_WIDTHS = [480, 768, 1280, 1920];

    public function __construct(
        private readonly int $mediaItemId,
    ) {}

    public function handle(): void
    {
        $item = MediaItem::findOrFail($this->mediaItemId);

        $manager  = new ImageManager(new GdDriver());
        $original = Storage::disk('local')->path($item->original_path);
        $image    = $manager->read($original);

        $baseName = pathinfo($item->original_path, PATHINFO_FILENAME);
        $dir      = 'media/' . $item->uuid;

        // All processed files go to Cloudflare R2
        $r2 = Storage::disk('r2');

        // ----------------------------------------------------------------
        // WebP full-size
        // ----------------------------------------------------------------
        $webpPath = $dir . '/' . $baseName . '.webp';
        $r2->put($webpPath, $image->toWebp(quality: 82)->toString(), 'public');

        // ----------------------------------------------------------------
        // AVIF full-size (GD + avif extension required)
        // ----------------------------------------------------------------
        $avifPath = null;
        if (function_exists('imageavif')) {
            $avifPath = $dir . '/' . $baseName . '.avif';
            $r2->put($avifPath, $image->toAvif(quality: 65)->toString(), 'public');
        }

        // ----------------------------------------------------------------
        // Responsive srcset sizes (WebP)
        // ----------------------------------------------------------------
        $srcsetPaths = [];
        foreach (self::SRCSET_WIDTHS as $width) {
            if ($width >= $image->width()) {
                continue; // don't upscale
            }
            $resized  = $manager->read($original);
            $resized->scaleDown(width: $width);
            $sizePath = $dir . '/' . $baseName . '-' . $width . '.webp';
            $r2->put($sizePath, $resized->toWebp(quality: 80)->toString(), 'public');
            $srcsetPaths[$width] = $sizePath;
        }

        // ----------------------------------------------------------------
        // LQIP — 20 px wide, base64 data URI stored on R2 as a text file
        // ----------------------------------------------------------------
        $lqip = $manager->read($original);
        $lqip->scaleDown(width: 20);
        $lqipData = 'data:image/webp;base64,' . base64_encode(
            $lqip->toWebp(quality: 30)->toString()
        );
        $lqipPath = $dir . '/' . $baseName . '-lqip.txt';
        $r2->put($lqipPath, $lqipData, 'public');

        // ----------------------------------------------------------------
        // Persist results
        // ----------------------------------------------------------------
        $item->update([
            'webp_path'    => $webpPath,
            'avif_path'    => $avifPath,
            'srcset_paths' => $srcsetPaths ?: null,
            'lqip_path'    => $lqipPath,
            'width'        => $image->width(),
            'height'       => $image->height(),
        ]);
    }
}
