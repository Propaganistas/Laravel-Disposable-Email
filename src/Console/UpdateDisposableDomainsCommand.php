<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
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
     * @return int
     */
    public function handle(Config $config, DisposableDomains $disposable)
    {
        $this->line('Fetching from source...');

        $fetcher = $this->laravel->make(
            $fetcherClass = $config->get('disposable-email.fetcher')
        );

        if (! $fetcher instanceof Fetcher) {
            $this->error($fetcherClass.' should implement '.Fetcher::class);

            return Command::FAILURE;
        }

        $sources = $config->get('disposable-email.sources');

        if (! $sources && $config->get('disposable-email.source')) {
            $sources = [$config->get('disposable-email.source')];
        }

        if (! is_array($sources)) {
            $this->error('Source URLs should be defined in an array');

            return Command::FAILURE;
        }

        $data = [];
        foreach ($sources as $source) {
            $data = array_merge($data, $this->laravel->call([$fetcher, 'handle'], [
                'url' => $source,
            ]));
        }

        $this->line('Saving response to storage...');

        if (! $disposable->saveToStorage($data)) {
            $this->error('Could not write to storage ('.$disposable->getStoragePath().')!');

            return Command::FAILURE;
        }

        $this->info('Disposable domains list updated successfully.');

        $disposable->bootstrap();

        return Command::SUCCESS;
    }
}
