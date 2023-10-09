<?php

namespace BrayanCaro\ApiRule\Utils;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Adds a prefix for each key in the array.
 *
 * This function has the same behavior as {@see https://laravel.com/docs/9.x/helpers#method-array-prependkeyswith} prependkeyswith
 * but that function was introducted in laravel 9
 *
 * @template T
 *
 * @param  array<string, T>  $array
 * @return array<string, T>
 */
function prependKeysWith(array $array, string $prefix): array
{
    return Collection::wrap($array)->mapWithKeys(function ($value, $key) use ($prefix) {
        return [
            Str::start($key, $prefix) => $value,
        ];
    })->toArray();
}
