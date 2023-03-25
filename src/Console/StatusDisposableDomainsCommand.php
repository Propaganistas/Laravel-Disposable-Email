<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Propaganistas\LaravelDisposableEmail\Contracts\Fetcher;
use Propaganistas\LaravelDisposableEmail\DisposableDomains;

class StatusDisposableDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disposable:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows the status of disposable domains';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Propaganistas\LaravelDisposableEmail\DisposableDomains  $disposable
     * @return  void
     */
    public function handle(Config $config, DisposableDomains $disposable)
    {
        $this->line('Getting informations for disposable domains...');
        
        if (!file_exists($disposable->getStoragePath())) {
            $this->error('Could not find disposable domains at ' . $disposable->getStoragePath());
            return 1;
        }

        $lastUpdate = date('Y-m-d H:i:s', filemtime($disposable->getStoragePath()));
        $linesCount = count($disposable->getDomains());

        $this->info('Last updated disposable domains list at: ' . $lastUpdate);
        $this->info('Number of disposable domains founded: ' . $linesCount . ' domains');
        return 0;
    }
}

