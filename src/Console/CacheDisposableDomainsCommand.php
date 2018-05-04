<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Console\Command;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;

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
        if (! $data = Cache::fetchSource()) {
            $this->error('Couldn\'t reach the list source. Aborting.');

            return;
        }

        if (! $this->isUsefulJson($data)) {
            $this->error('The fetched list appears to be invalid. Aborting.');

            return;
        }

        try {
            Cache::store($data);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            $this->error('Cannot write the fetched list to the cache. Aborting.');
        }

        $this->info('Disposable domains list updated successfully.');
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