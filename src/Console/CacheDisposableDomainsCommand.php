<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Console\Command;
use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;

class CacheDisposableDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disposable:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches the latest disposable email domains list';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {

            Indisposable::flushCache();

            $domains = Indisposable::remoteDomains();

            $this->info('Successfully cached '. count($domains) . ' disposable email domains.');

        } catch (\Exception $exception) {

            $this->error($exception->getMessage());

        }
    }

    /**
     * Check whether the given JSON data is useful.
     *
     * @param string $data
     * @return bool
     */
    private function isUsefulJson($data)
    {
        $data = json_decode($data, true);

        return json_last_error() === JSON_ERROR_NONE && ! empty($data);
    }
}