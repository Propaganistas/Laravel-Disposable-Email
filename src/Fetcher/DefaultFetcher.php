<?php

namespace Propaganistas\LaravelDisposableEmail\Fetcher;

use InvalidArgumentException;
use UnexpectedValueException;

class DefaultFetcher
{
    public function handle($url)
    {
        if (! $url) {
            throw new InvalidArgumentException('Source URL is null');
        }

        $content = file_get_contents($url);

        if ($content === false) {
            throw new UnexpectedValueException('Failed to interpret the source URL ('.$url.')');
        }

        return $content;
    }
}