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

            $oldDomains = Indisposable::remoteDomains();

            Indisposable::flushCache();

            $domains = Indisposable::remoteDomains();

            $domainCount = count($domains);

            $this->info('Successfully cached '. $domainCount . ' disposable email '. str_plural('domains', $domainCount) .'.');

        } catch (\Exception $exception) {

            $this->error($exception->getMessage());

            if ($oldDomains) {
                Indisposable::setRemoteDomainsCache($oldDomains);
            }

        }
    }

}