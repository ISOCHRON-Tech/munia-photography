<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Static dummy data used across all public pages when the database is empty.
 * Replace with real data once uploads are in place.
 *
 * aspect_ratio values used in photo-card component:
 *   padding-top: {{ $ar }}%  — so this must be a plain float (no % suffix)
 *
 * aspect_ratio values used on the home page card:
 *   --ar: {{ $item->aspect_ratio ?? '66.67%' }}  — this expects a % string
 *   We store the float and handle both in each builder method.
 */
final class HomeDummyData
{
    // ─── Raw photo data (shared across home + gallery) ───────────────────────
    // [id, title, category_name, category_slug, url_seed, w, h, ar_float]

    private const PHOTO_DATA = [
        [1,  'Into the Mist',      'Landscape',    'landscape',    'mist-valley-01',    1200,  800, 66.67],
        [2,  'Golden Hour',        'Portrait',     'portrait',     'golden-dusk-01',     800, 1200, 150.0],
        [3,  'Still Waters',       'Nature',       'nature',       'still-lake-01',     1200,  900, 75.0],
        [4,  'Urban Geometry',     'Architecture', 'architecture', 'city-angles-01',     800, 1000, 125.0],
        [5,  'First Light',        'Landscape',    'landscape',    'dawn-ridge-01',     1200,  800, 66.67],
        [6,  'Shadow Play',        'Abstract',     'abstract',     'shadow-form-01',     900,  900, 100.0],
        [7,  'Wandering',          'Street',       'street',       'street-wander-01',  1600,  900, 56.25],
        [8,  'Quiet Morning',      'Landscape',    'landscape',    'quiet-morning-01',  1200,  900, 75.0],
        [9,  'Borrowed Light',     'Portrait',     'portrait',     'borrow-light-01',    800, 1067, 133.38],
        [10, 'The Long Road',      'Landscape',    'landscape',    'long-road-01',      1600,  900, 56.25],
        [11, 'Concrete & Glass',   'Architecture', 'architecture', 'concrete-glass-01',  900,  900, 100.0],
        [12, 'Deep Forest',        'Nature',       'nature',       'deep-forest-01',    1200,  800, 66.67],
        [13, 'Rain on Glass',      'Abstract',     'abstract',     'rain-glass-01',      800, 1067, 133.38],
        [14, 'Last Train',         'Street',       'street',       'last-train-01',     1600,  900, 56.25],
        [15, 'Fog & Fir',          'Nature',       'nature',       'fog-fir-01',         800,  960, 120.0],
        [16, 'Open Horizon',       'Landscape',    'landscape',    'open-horizon-01',   1600,  900, 56.25],
        [17, 'The Watcher',        'Portrait',     'portrait',     'watcher-portrait',   800, 1200, 150.0],
        [18, 'Stone & Sky',        'Architecture', 'architecture', 'stone-sky-01',      1200,  800, 66.67],
    ];

    // ─── Raw story data (shared across home + stories index) ────────────────

    private const STORY_DATA = [
        [
            'slug'    => 'chasing-light-golden-hour',
            'title'   => 'On Chasing Light: Notes from the Golden Hour',
            'excerpt' => 'There is a window of roughly twenty minutes each evening when the world briefly forgets to be ordinary. The light turns amber, shadows grow long, and every surface holds a kind of warmth that no artificial source can replicate.',
            'date'    => '2026-05-12',
            'mins'    => 5,
            'seed'    => 'golden-hour-story',
        ],
        [
            'slug'    => 'language-of-silence',
            'title'   => 'The Language of Silence: Minimalist Photography',
            'excerpt' => 'Minimalism in photography is not about emptiness — it is about discipline. Knowing what to exclude is the hardest skill to learn, and perhaps the most rewarding.',
            'date'    => '2026-04-03',
            'mins'    => 4,
            'seed'    => 'minimal-silence',
        ],
        [
            'slug'    => 'shooting-in-the-rain',
            'title'   => 'Shooting in the Rain: A Love Letter to Grey Days',
            'excerpt' => 'Rain transforms the familiar. Reflections appear on pavements, colours deepen, and the light takes on a diffuse, almost studio-like quality. Grey days are, quietly, the best days to shoot.',
            'date'    => '2026-03-18',
            'mins'    => 3,
            'seed'    => 'rain-reflections',
        ],
        [
            'slug'    => 'ethics-of-travel-photography',
            'title'   => 'The Ethics of Travel Photography',
            'excerpt' => 'When we point a lens at a stranger in a foreign city, what do we owe them? Consent, dignity, and honesty — these are not optional extras. They are the foundations of any photograph worth keeping.',
            'date'    => '2026-02-28',
            'mins'    => 6,
            'seed'    => 'ethics-travel-story',
        ],
        [
            'slug'    => 'film-vs-digital',
            'title'   => 'Film vs Digital: A Personal Reckoning',
            'excerpt' => 'I shot film for three years before switching to digital, then went back to film for six months. What I learned is that the debate entirely misses the point — it is always about intention, never equipment.',
            'date'    => '2026-01-15',
            'mins'    => 7,
            'seed'    => 'film-digital-story',
        ],
        [
            'slug'    => 'finding-home-in-foreign-light',
            'title'   => 'Finding Home in Foreign Light',
            'excerpt' => 'Every city has a quality of light unique to its latitude, its architecture, its air. Learning to read that light — to feel when it is right — is the closest thing to belonging somewhere new.',
            'date'    => '2025-12-02',
            'mins'    => 4,
            'seed'    => 'foreign-light-story',
        ],
    ];

    // ─── Home page ───────────────────────────────────────────────────────────

    /**
     * 6 featured photo objects for the home page "Selected Work" grid.
     * aspect_ratio is a CSS % string because home.blade uses --ar: {{ $item->aspect_ratio ?? '66.67%' }}
     */
    public static function featured(): Collection
    {
        return collect(array_slice(self::PHOTO_DATA, 0, 6))
            ->map(static function (array $row): object {
                [$id, $title, $cat, $catSlug, $seed, $w, $h, $ar] = $row;
                $item = (object) [
                    'id'           => $id,
                    'title'        => $title,
                    'public_url'   => "https://picsum.photos/seed/{$seed}/{$w}/{$h}",
                    'aspect_ratio' => $ar . '%',   // CSS % string for --ar variable
                    'avif_path'    => null,
                    'avif_url'     => null,
                    'webp_path'    => null,
                    'webp_url'     => null,
                    'lqip_path'    => null,
                ];
                $item->category = (object) ['name' => $cat, 'slug' => $catSlug];
                return $item;
            });
    }

    /** 3 story objects for the home page "From the Journal" section. */
    public static function stories(): Collection
    {
        return collect(array_slice(self::STORY_DATA, 0, 3))
            ->map(static fn (array $s) => self::buildStory($s));
    }

    public static function totalPhotos(): int
    {
        return 47;
    }

    // ─── Gallery index page ──────────────────────────────────────────────────

    /**
     * 18 photo objects for the gallery grid.
     * aspect_ratio is a plain float because photo-card uses: padding-top: {{ $ar }}%
     */
    public static function galleryItems(): Collection
    {
        return collect(self::PHOTO_DATA)
            ->map(static function (array $row): object {
                [$id, $title, $cat, $catSlug, $seed, $w, $h, $ar] = $row;
                $item = (object) [
                    'id'             => $id,
                    'title'          => $title,
                    'public_url'     => "https://picsum.photos/seed/{$seed}/{$w}/{$h}",
                    'aspect_ratio'   => $ar,         // plain float for padding-top: X%
                    'avif_path'      => null,
                    'avif_url'       => null,
                    'webp_path'      => null,
                    'webp_url'       => null,
                    'lqip_path'      => null,
                    'srcset'         => null,
                    'width'          => $w,
                    'height'         => $h,
                    'camera_display' => null,
                    'iso'            => null,
                    'aperture'       => null,
                    'shutter_speed'  => null,
                ];
                $item->category = (object) ['name' => $cat, 'slug' => $catSlug];
                return $item;
            });
    }

    /** Category filter pills for the gallery header. */
    public static function galleryCategories(): Collection
    {
        $counts = collect(self::PHOTO_DATA)
            ->groupBy(fn ($r) => $r[2])
            ->map->count();

        return collect([
            ['Landscape',    'landscape',    $counts->get('Landscape',    0)],
            ['Portrait',     'portrait',     $counts->get('Portrait',     0)],
            ['Architecture', 'architecture', $counts->get('Architecture', 0)],
            ['Nature',       'nature',       $counts->get('Nature',       0)],
            ['Abstract',     'abstract',     $counts->get('Abstract',     0)],
            ['Street',       'street',       $counts->get('Street',       0)],
        ])->map(static fn (array $r) => (object) [
            'name'               => $r[0],
            'slug'               => $r[1],
            'media_items_count'  => $r[2],
        ]);
    }

    // ─── Stories index page ──────────────────────────────────────────────────

    /** 6 story objects for the stories index grid. */
    public static function storiesIndex(): Collection
    {
        return collect(self::STORY_DATA)
            ->map(static fn (array $s) => self::buildStory($s));
    }

    // ─── Shared builder ──────────────────────────────────────────────────────

    private static function buildStory(array $s): object
    {
        return (object) [
            'slug'                 => $s['slug'],
            'title'                => $s['title'],
            'meta_description'     => $s['excerpt'],
            'published_at'         => Carbon::parse($s['date']),
            'reading_time_minutes' => $s['mins'],
            'banner_url'           => "https://picsum.photos/seed/{$s['seed']}/1600/900",
            'banner_path'          => null,
            'banner_webp_path'     => null,
        ];
    }
}
