<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Console\Command;
use Propaganistas\LaravelDisposableEmail\Contracts\Fetcher;
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
            $fetcherClass = $config->get('disposable-email.fetcher')
        );

        if (! $fetcher instanceof Fetcher) {
            $this->error($fetcherClass . ' should implement ' . Fetcher::class);
            return 1;
        }

        $data = $this->laravel->call([$fetcher, 'handle'], [
            'url' => $config->get('disposable-email.source'),
        ]);

        $this->line('Saving response to storage...');

        if ($disposable->saveToStorage($data)) {
            $this->info('Disposable domains list updated successfully.');
            $disposable->bootstrap();
            return 0;
        } else {
            $this->error('Could not write to storage ('.$disposable->getStoragePath().')!');
            return 1;
        }
    }
}
