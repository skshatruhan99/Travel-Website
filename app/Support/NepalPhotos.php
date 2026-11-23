<?php

namespace App\Support;

use Illuminate\Support\Str;

class NepalPhotos
{
    public static function destination(string $slug): ?string
    {
        return self::url(config('nepal_photos.destinations.'.$slug));
    }

    public static function tour(string $slug, ?string $destinationSlug = null): ?string
    {
        return self::url(config('nepal_photos.tours.'.$slug))
            ?? ($destinationSlug ? self::destination($destinationSlug) : null);
    }

    public static function hotel(string $slug): ?string
    {
        return self::url(config('nepal_photos.hotels.'.$slug));
    }

    public static function activity(string $slug): ?string
    {
        return self::url(config('nepal_photos.activities.'.$slug));
    }

    public static function place(string $name): ?string
    {
        $key = Str::slug($name);

        return self::url(config('nepal_photos.places.'.$key));
    }

    private static function url(?string $key): ?string
    {
        $path = $key ? config('nepal_photos.photos.'.$key.'.path') : null;

        return $path ? asset($path) : null;
    }
}
