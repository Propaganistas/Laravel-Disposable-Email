<?php

namespace Propaganistas\LaravelDisposableEmail\Contracts;

interface Fetcher
{
    public function handle($url): array;
}
