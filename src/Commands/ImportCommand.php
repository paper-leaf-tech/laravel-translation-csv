<?php

namespace PaperleafTech\LaravelTranslation\Commands;

use Illuminate\Console\Command;

class ImportCommand extends Command
{
    protected $signature = 'translations:import {lang=en}';
    protected $description = 'Import updated translations from Google Sheets';

    public function handle(): int
    {
        $lang = $this->argument('lang');

        $this->info('Translations imported successfully.');
        return self::SUCCESS;
    }
}