<?php

namespace Propaganistas\LaravelDisposableEmail\Fetcher;

use InvalidArgumentException;
use Propaganistas\LaravelDisposableEmail\Contracts\Fetcher;
use UnexpectedValueException;

class DefaultFetcher implements Fetcher
{
    public function handle($url): array
    {
        if (! $url) {
            throw new InvalidArgumentException('Source URL is null');
        }

        $content = $this->file_get_contents_curl($url);

        if ($content === false) {
            throw new UnexpectedValueException('Failed to interpret the source URL ('.$url.')');
        }

        if (! $this->isValidJson($content)) {
            throw new UnexpectedValueException('Provided data could not be parsed as JSON');
        }

        return json_decode($content);
    }

    protected function isValidJson($data): bool
    {
        $data = json_decode($data, true);

        return json_last_error() === JSON_ERROR_NONE && ! empty($data);
    }

    public function file_get_contents_curl( $url ) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}