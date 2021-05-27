<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Console\Command;
use Propaganistas\LaravelDisposableEmail\DisposableDomains;

class UpdateDisposableDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disposable:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates to the latest disposable email domains list';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Propaganistas\LaravelDisposableEmail\DisposableDomains  $disposable
     * @return  void
     */
    public function handle(Config $config, DisposableDomains $disposable)
    {
        $this->line('Fetching from source...');

        $fetcher = $this->laravel->make(
            $config->get('disposable-email.fetcher')
        );

        $data = $this->laravel->call([$fetcher, 'handle'], [
            'url' => $config->get('disposable-email.source'),
        ]);

        $this->line('Saving response to storage...');

        $disposable->saveToStorage($data)
            ? $this->info('Disposable domains list updated successfully.')
            : $this->error('Could not write to storage ('.$disposable->getStoragePath().')!');
    }
}
