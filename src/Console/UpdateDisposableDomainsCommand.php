<?php

namespace Propaganistas\LaravelDisposableEmail\Console;

use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Propaganistas\LaravelDisposableEmail\DisposableDomains;
use Propaganistas\LaravelDisposableEmail\Traits\ParsesJson;

class UpdateDisposableDomainsCommand extends Command
{
    use ParsesJson;

    /**
     * The console command name.
     * For Laravel 5.0
     *
     * @var string
     */
    protected $name = 'disposable:update';

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
     * The disposable domains service.
     *
     * @var \Propaganistas\LaravelDisposableEmail\DisposableDomains
     */
    protected $disposableDomains;

    /**
     * The config service.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * UpdateDisposableDomainsCommand constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @param \Propaganistas\LaravelDisposableEmail\DisposableDomains $disposable
     * @return void
     */
    public function handle(DisposableDomains $disposable)
    {
        $this->disposableDomains = $disposable;

        $this->line('Fetching from source...');

        $data = $this->fetchFromSource();

        if ($data === false) {
            $this->error('Aborting.');

            return;
        }

        if (! $this->isValidData($data)) {
            $this->error('Source returned invalid JSON. Aborting.');

            return;
        }

        $this->line('Saving response to storage...');

        if (! $this->save($data)) {
            $this->error('Couldn\'t write to storage ('.$this->disposableDomains->getStoragePath().'). Aborting.');

            return;
        }

        $this->info('Disposable domains list updated successfully.');
    }

    /**
     * Fetches from the source URL.
     *
     * @return string|bool
     */
    protected function fetchFromSource()
    {
        $sourceUrl = $this->config->get('disposable-email.source');

        try {
            $content = file_get_contents($sourceUrl);

            if ($content !== false) {
                return $content;
            }

            $this->error('PHP failed to interpret the source URL ('.$sourceUrl.')');
        } catch (Exception $e) {
            $this->error('Couldn\'t reach the source ('.$sourceUrl.').');
        }

        return false;
    }

    /**
     * Determines whether the data is valid JSON.
     *
     * @param string $data
     * @return bool
     */
    protected function isValidData($data)
    {
        if ($this->parseJson($data)) {
            return true;
        }

        return false;
    }

    /**
     * Saves received data to storage.
     *
     * @param string $data
     * @return bool
     */
    protected function save($data)
    {
        if (file_put_contents($this->disposableDomains->getStoragePath(), $data) === false) {
            return false;
        }

        // Flushing the cache will force it to refill itself in the next request.
        $this->disposableDomains->flushCache();

        return true;
    }
}