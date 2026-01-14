<?php

namespace PaperleafTech\LaravelTranslationCsv\Commands;

use Illuminate\Console\Command;

class ExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example for running on local:
     *
     * @var string
     */
    protected $signature = 'translation-csv:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the current translations to a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Success');

        return Command::SUCCESS;
    }
}
