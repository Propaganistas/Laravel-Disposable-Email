<?php

namespace Propaganistas\LaravelDisposableEmail\Traits;

trait ParsesJson
{
    /**
     * Parses the given JSON into a native array. Returns false on errors.
     *
     * @param string $data
     * @return array|bool
     */
    protected function parseJson($data)
    {
        $data = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            return false;
        }

        return $data;
    }
}