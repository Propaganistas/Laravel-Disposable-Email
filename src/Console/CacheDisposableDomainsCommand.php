<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Console\Command;

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
     * The JSON source URL.
     *
     * @var string
     */
    public static $sourceUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $data = $this->fetchFromSource()) {
            $this->error('Couldn\'t reach the list source. Aborting.');

            return;
        }

        if (! $this->isUsefulJson($data)) {
            $this->error('The fetched list appears to be invalid. Aborting.');

            return;
        }

        if (! $this->storeData($data)) {
            $this->error('Cannot write the fetched list to [storage/framework/disposable_domains.json]. Aborting.');

            return;
        }

        $this->info('Disposable domains list updated successfully.');
    }

    /**
     * Fetch new data from the source URL.
     *
     * @return bool
     */
    protected function fetchFromSource()
    {
        return file_get_contents(static::$sourceUrl);
    }

    /**
     * Writes data to the disk.
     *
     * @param string $data
     * @return bool
     */
    protected function storeData($data)
    {
        return file_put_contents(base_path('storage/framework/disposable_domains.json'), $data);
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