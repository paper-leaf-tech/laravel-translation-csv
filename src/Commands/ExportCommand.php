<?php

namespace PaperleafTech\LaravelTranslation\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PaperleafTech\LaravelTranslation\Services\GoogleSheetsService;

class ExportCommand extends Command
{
    protected $signature = 'translations:export {lang=en} {--clear : Clear existing sheet data before export}';

    protected $description = 'Export Laravel translations to a connected Google Sheet.';

    public function __construct(protected GoogleSheetsService $sheetsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $lang = $this->argument('lang');
        $langPath = lang_path($lang);

        if (! File::isDirectory($langPath)) {
            $this->error("Translation directory not found: {$langPath}");

            return self::FAILURE;
        }

        try {
            $this->info("Exporting translations for language: {$lang}");

            // Collect all translations
            $translations = $this->collectTranslations($langPath);

            if (empty($translations)) {
                $this->warn('No translations found to export.');

                return self::SUCCESS;
            }

            $this->info('Found '.count($translations).' translation keys.');

            // Prepare data for Google Sheets
            $sheetData = $this->prepareSheetData($translations);

            // Clear existing data if requested
            if ($this->option('clear')) {
                $this->info('Clearing existing sheet data...');
                $keyColumn = config('laravel-translation.key_column', 'A');
                $updatedValueColumn = config('laravel-translation.updated_value_column', 'C');
                $this->sheetsService->clearSheetData("{$keyColumn}:{$updatedValueColumn}");
            }

            // Write to Google Sheets
            $this->info('Writing to Google Sheets...');
            $keyColumn = config('laravel-translation.key_column', 'A');
            $updatedValueColumn = config('laravel-translation.updated_value_column', 'C');
            $headerRow = config('laravel-translation.header_row', 1);

            $range = "{$keyColumn}{$headerRow}:{$updatedValueColumn}";
            $this->sheetsService->updateSheetData($range, $sheetData);

            $this->info('✓ Translations exported successfully!');
            $this->line('');
            $this->line("Spreadsheet ID: {$this->sheetsService->getClient()->getConfig('spreadsheet_id')}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Export failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Collect all translations from PHP files in the language directory
     */
    protected function collectTranslations(string $langPath, string $prefix = ''): array
    {
        $translations = [];

        $files = File::files($langPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filename = $file->getFilenameWithoutExtension();
            $fileTranslations = require $file->getPathname();

            if (! is_array($fileTranslations)) {
                continue;
            }

            foreach ($fileTranslations as $key => $value) {
                $fullKey = $prefix ? "{$prefix}.{$filename}.{$key}" : "{$filename}.{$key}";

                if (is_array($value)) {
                    // Flatten nested arrays
                    $translations = array_merge(
                        $translations,
                        $this->flattenTranslations($value, $fullKey)
                    );
                } else {
                    $translations[$fullKey] = $value;
                }
            }
        }

        // Handle subdirectories
        $directories = File::directories($langPath);
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $nestedPrefix = $prefix ? "{$prefix}.{$dirName}" : $dirName;
            $translations = array_merge(
                $translations,
                $this->collectTranslations($directory, $nestedPrefix)
            );
        }

        return $translations;
    }

    /**
     * Flatten nested translation arrays
     */
    protected function flattenTranslations(array $translations, string $prefix): array
    {
        $flattened = [];

        foreach ($translations as $key => $value) {
            $fullKey = "{$prefix}.{$key}";

            if (is_array($value)) {
                $flattened = array_merge(
                    $flattened,
                    $this->flattenTranslations($value, $fullKey)
                );
            } else {
                $flattened[$fullKey] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Prepare data in the format expected by Google Sheets
     */
    protected function prepareSheetData(array $translations): array
    {
        $data = [];

        // Add header row if configured
        if (config('laravel-translation.header_row')) {
            $data[] = ['Key', 'Original Value', 'Updated Value'];
        }

        // Add translation rows
        // Initially, both Original Value and Updated Value are the same
        foreach ($translations as $key => $value) {
            $data[] = [$key, $value, $value];
        }

        return $data;
    }
}