# Laravel Translation Manager

Manage Laravel translation strings using Google Sheets. This package allows you to sync your Laravel translation files with a Google Spreadsheet, making it easy for non-technical team members to manage translations.

## Features

- 🔄 **Bi-directional sync** - Export Laravel translations to Google Sheets and import updates back
- 🔐 **Service Account authentication** - Simple, secure authentication using Google service accounts
- 📝 **Nested translations** - Automatically handles nested translation arrays using dot notation
- 🌍 **Multi-language support** - Manage translations for any language
- 🔍 **Dry-run mode** - Preview changes before applying them
- 📋 **Three-column workflow** - Preserves original values while allowing content editors to manage updates
- ✨ **Easy setup** - Simple configuration and installation process

## Installation

Install the package via Composer:

```bash
composer require paper-leaf-tech/laravel-translation
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-translation-config
```

## Google Cloud Setup

### 1. Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google Sheets API**:
   - Navigate to "APIs & Services" > "Library"
   - Search for "Google Sheets API"
   - Click "Enable"

### 2. Create a Service Account

1. Navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "Service Account"
3. Fill in the service account details:
   - **Name**: Laravel Translation Manager (or any name you prefer)
   - **Description**: Service account for managing Laravel translations
4. Click "Create and Continue"
5. Skip the optional steps and click "Done"

### 3. Download Service Account Credentials

1. In the "Credentials" page, find your newly created service account
2. Click on the service account email
3. Go to the "Keys" tab
4. Click "Add Key" > "Create new key"
5. Select "JSON" format
6. Click "Create" - a JSON file will be downloaded

### 4. Store the Credentials

Move the downloaded JSON file to your Laravel application:

```bash
# Recommended location
mv ~/Downloads/your-service-account-file.json storage/app/google-service-account.json
```

> **Important**: Add this file to your `.gitignore` to prevent committing credentials to version control!

```
# .gitignore
storage/app/google-service-account.json
```

### 5. Create and Share Your Google Sheet

1. Create a new Google Spreadsheet or use an existing one
2. Open the service account JSON file and find the `client_email` field
3. Share your Google Sheet with this email address:
   - Click "Share" in Google Sheets
   - Paste the service account email (e.g., `laravel-translation@project-id.iam.gserviceaccount.com`)
   - Grant "Editor" permissions
   - Click "Send"

### 6. Get Your Spreadsheet ID

The spreadsheet ID is in the URL:
```
https://docs.google.com/spreadsheets/d/SPREADSHEET_ID_HERE/edit
```

Copy the `SPREADSHEET_ID_HERE` portion.

## Configuration

Add the following to your `.env` file:

```env
# Path to your service account JSON file
GOOGLE_SHEETS_CREDENTIALS_PATH="${STORAGE_PATH}/app/google-service-account.json"

# Your Google Spreadsheet ID (from the URL)
GOOGLE_SHEETS_SPREADSHEET_ID="your-spreadsheet-id-here"

# Optional: Specify a sheet name (defaults to first sheet)
GOOGLE_SHEETS_SHEET_NAME="Translations"
```

### Advanced Configuration

You can customize additional settings in `config/laravel-translation.php`:

```php
return [
    // Credentials path
    'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
    
    // Spreadsheet ID
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
    
    // Sheet name (null = first sheet)
    'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', null),
    
    // Column for translation keys
    'key_column' => 'A',
    
    // Column for original translation values (preserved for reference)
    'original_value_column' => 'B',
    
    // Column for updated translation values (managed by content editors)
    'updated_value_column' => 'C',
    
    // Header row number (null = no headers)
    'header_row' => 1,
];
```

## Usage

### Export Translations to Google Sheets

Export your Laravel translation files to Google Sheets:

```bash
# Export English translations
php artisan translations:export en

# Export other languages
php artisan translations:export es

# Clear existing sheet data before export
php artisan translations:export en --clear
```

This command will:
- Read all translation files from `lang/en/` (or your specified language)
- Flatten nested arrays using dot notation (e.g., `auth.failed` → `auth.failed`)
- Write the data to your Google Sheet with three columns:
  - **Column A (Key)**: Translation key in dot notation
  - **Column B (Original Value)**: The original translation value (preserved for reference)
  - **Column C (Updated Value)**: Initially same as original, this is where content editors make changes

### Import Translations from Google Sheets

Import updated translations from Google Sheets back to Laravel:

```bash
# Import English translations
php artisan translations:import en

# Preview changes without writing files
php artisan translations:import en --dry-run
```

This command will:
- Read data from your Google Sheet (columns A, B, and C)
- Prioritize values from **Column C (Updated Value)**, falling back to **Column B (Original Value)** if Column C is empty
- Parse dot notation back into nested arrays
- Create/update translation files in `lang/en/`

### Translation File Structure

The package handles nested translations automatically. For example:

**Google Sheet:**
```
Key                    | Original Value                              | Updated Value
-----------------------|---------------------------------------------|---------------------------------------------
auth.failed           | These credentials do not match our records. | These credentials do not match our records.
auth.throttle         | Too many login attempts.                    | Too many login attempts. Please wait.
validation.required   | The :attribute field is required.           | The :attribute field is required.
```

> **Note**: Content editors work in the "Updated Value" column (C). The "Original Value" column (B) is preserved for reference.

**Generated Laravel file** (`lang/en/auth.php`):
```php
<?php

return [
    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please wait.',  // Uses updated value from column C
];
```

**Generated Laravel file** (`lang/en/validation.php`):
```php
<?php

return [
    'required' => 'The :attribute field is required.',
];
```

## Workflow Example

### Initial Setup
1. Export your existing Laravel translations to Google Sheets:
   ```bash
   php artisan translations:export en --clear
   ```
   This creates three columns:
   - **Column A**: Translation keys (e.g., `auth.failed`)
   - **Column B**: Original values (preserved for reference)
   - **Column C**: Updated values (initially same as Column B)

2. Share the Google Sheet with your team members

### Making Updates
1. Content editors modify translations in **Column C (Updated Value)**
   - Column B remains unchanged for reference
   - If Column C is empty, the import will use Column B's value

2. Import the updates:
   ```bash
   # Preview changes first
   php artisan translations:import en --dry-run
   
   # Apply changes
   php artisan translations:import en
   ```

3. Commit the updated translation files to your repository

### Best Practices
- **Never edit Column B (Original Value)** - This preserves the baseline translations
- **Make all edits in Column C (Updated Value)** - This is where content editors work
- **Leave Column C empty** to use the original value from Column B
- **Re-export periodically** to sync new translation keys added in code

## Troubleshooting

### Permission Denied Error

**Error:** `Permission denied accessing Google Sheet`

**Solution:** Ensure you've shared the Google Sheet with your service account email address. You can find this email in your credentials JSON file under the `client_email` field.

### Credentials File Not Found

**Error:** `Google Sheets credentials file not found`

**Solution:** 
- Verify the path in your `.env` file is correct
- Ensure the JSON file exists at the specified location
- Check file permissions

### Invalid Credentials

**Error:** `Google Sheets credentials file contains invalid JSON`

**Solution:** 
- Re-download the service account JSON file from Google Cloud Console
- Ensure you selected "JSON" format when creating the key
- Verify the file is a valid service account credential (should have `"type": "service_account"`)

### Sheet Not Found

**Error:** `Google Sheet not found`

**Solution:**
- Verify the spreadsheet ID in your configuration
- Ensure the spreadsheet hasn't been deleted
- Check that the service account has access to the sheet

### Getting Service Account Email

If you need to find your service account email to share the sheet:

```bash
# View the credentials file
cat storage/app/google-service-account.json | grep client_email
```

Or use the helper method in your code:
```php
use PaperleafTech\LaravelTranslation\Services\GoogleSheetsService;

$service = app(GoogleSheetsService::class);
$email = $service->getServiceAccountEmail();
```

## Security Considerations

- **Never commit your service account JSON file** to version control
- Store credentials in `storage/app/` which is typically excluded from version control
- Use environment variables for configuration
- Limit service account permissions to only Google Sheets API
- Regularly rotate service account keys if needed

## Requirements

- PHP 8.2 or higher
- Laravel 10.x or higher
- Google Cloud project with Sheets API enabled
- Service account with access to your Google Sheet

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- [Brendan Angerman](https://github.com/paper-leaf-tech)
- Built with [Spatie Laravel Package Tools](https://github.com/spatie/laravel-package-tools)
- Uses [Google API PHP Client](https://github.com/googleapis/google-api-php-client)

## Support

If you discover any issues or have questions, please [open an issue on GitHub](https://github.com/paper-leaf-tech/laravel-translation/issues).
