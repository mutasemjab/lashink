<?php

/**
 * Get a site setting value, locale-aware.
 * For URLs / numbers / paths, locale is ignored (returns value_ar).
 */
function sett(string $key, ?string $locale = null): string
{
    return \App\Models\SiteSetting::val($key, $locale);
}

/**
 * Get raw (non-localized) site setting value — for URLs, paths, numbers.
 */
function sett_raw(string $key): string
{
    return \App\Models\SiteSetting::raw($key);
}

function uploadImage($folder, $image)
{
    $extension = strtolower($image->getClientOriginalExtension());

    // generate unique name with timestamp + random string
    $filename = uniqid() . '_' . time() . '.' . $extension;

    $image->move(base_path($folder), $filename);

    return $filename;
}



function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}




