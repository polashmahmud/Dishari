<?php

namespace Polashmahmud\Menu\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallDishari extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dishari:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Dishari package (Publish config and views)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Dishari installation...');

        // Default directory name if config is not published
        $directoryName = 'dishari';

        // 1. Ask to publish the configuration file
        if ($this->confirm('Do you want to publish the configuration file?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'dishari-config'
            ]);

            $this->info('Configuration published successfully!');

            // Since config is published, ask for custom directory preference
            $inputName = $this->ask('What directory name would you like to use for Vue files?', 'dishari');

            // If user provided a name, update the variable and the config file
            if ($inputName) {
                $directoryName = $inputName;
                $this->updateConfigFile($directoryName);
            }
        } else {
            $this->comment("Skipping configuration publishing. Using default directory: '{$directoryName}'.");
        }

        // 2. Publish Views (Mandatory - No question asked)
        // Since the package relies on these views, we publish them automatically
        // based on the $directoryName determined above.

        $this->publishViews($directoryName);

        // 3. Optional: Run migrations prompt
        // if ($this->confirm('Do you want to run migrations?', false)) {
        //     $this->call('migrate');
        // }

        $this->info('Dishari installation completed successfully! 🚀');
    }

    /**
     * Update the configuration file with the user's input.
     */
    protected function updateConfigFile($directoryName)
    {
        $configPath = config_path('dishari.php');

        if (File::exists($configPath)) {
            $content = File::get($configPath);

            // Regex to find and replace the directory_name value
            $newContent = preg_replace(
                "/('directory_name'\s*=>\s*)(['\"])(.*?)(['\"])/",
                "$1'$directoryName'",
                $content
            );

            File::put($configPath, $newContent);

            $this->info("Configuration updated: Directory set to '{$directoryName}'.");
        } else {
            // Edge case: If for some reason publish failed or file shouldn't be touched
            $this->warn('Config file not found. Skipping config update.');
        }
    }

    /**
     * Manually copy views to the correct directory.
     */
    protected function publishViews($directoryName)
    {
        $this->info("Publishing views to: resources/js/Pages/{$directoryName}...");

        // Define source paths (Package directories)
        // Assuming this Command file is in src/Commands
        $packageRoot = __DIR__ . '/../../';
        $sourcePages = $packageRoot . 'resources/js/Pages';

        // Define destination paths (User's application directories)
        $destinationPages = resource_path("js/Pages/{$directoryName}");

        // Ensure the source exists before copying
        if (File::exists($sourcePages)) {
            // Create destination directory if it doesn't exist
            File::ensureDirectoryExists($destinationPages);

            // Copy the directory contents
            File::copyDirectory($sourcePages, $destinationPages);

            $this->info('Views published successfully!');
        } else {
            $this->error('Source Pages directory not found in the package.');
        }
    }
}
