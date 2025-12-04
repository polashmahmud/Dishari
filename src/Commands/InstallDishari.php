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
    protected $description = 'Install the Dishari package (Publish config, views, dependencies and components)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Dishari installation...');

        $directoryName = 'dishari'; // Default name

        // 1. Ask to publish the configuration file
        if ($this->confirm('Do you want to publish the configuration file?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'dishari-config'
            ]);

            $this->info('Configuration published successfully!');

            // Ask for custom directory preference
            $inputName = $this->ask('What directory name would you like to use for Vue files?', 'dishari');

            if ($inputName) {
                $directoryName = $inputName;
                $this->updateConfigFile($directoryName);
            }
        } else {
            $this->comment("Skipping configuration publishing. Using default directory: '{$directoryName}'.");
        }

        // 2. Publish Views (Mandatory)
        $this->publishViews($directoryName);

        // 3. Ask to install 'vue-sonner' dependency
        if ($this->confirm('This package requires "vue-sonner". Do you want to install it now?', true)) {
            $this->installPackage('vue-sonner');
        } else {
            $this->warn('Please manually install "vue-sonner" later.');
        }

        // 4. Ask to install 'vue-draggable-plus' dependency
        if ($this->confirm('This package requires "vue-draggable-plus" for drag & drop. Do you want to install it now?', true)) {
            $this->installPackage('vue-draggable-plus');
        } else {
            $this->warn('Please manually install "vue-draggable-plus" later.');
        }

        // 5. Ask to install shadcn-vue components
        $this->info('This package relies on the following shadcn-vue components: button, card, dialog, input, label, select, switch.');

        if ($this->confirm('Do you want to install these components now?', true)) {
            $this->installShadcnComponents();
        } else {
            $this->warn('Please manually install the required components later.');
        }

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

            $newContent = preg_replace(
                "/('directory_name'\s*=>\s*)(['\"])(.*?)(['\"])/",
                "$1'$directoryName'",
                $content
            );

            File::put($configPath, $newContent);
            $this->info("Configuration updated: Directory set to '{$directoryName}'.");
        } else {
            $this->warn('Config file not found. Skipping config update.');
        }
    }

    /**
     * Manually copy views to the correct directory.
     */
    protected function publishViews($directoryName)
    {
        $this->info("Publishing views to: resources/js/Pages/{$directoryName}...");

        $packageRoot = __DIR__ . '/../../';
        $sourcePages = $packageRoot . 'resources/js/Pages';
        $destinationPages = resource_path("js/Pages/{$directoryName}");

        if (File::exists($sourcePages)) {
            File::ensureDirectoryExists($destinationPages);
            File::copyDirectory($sourcePages, $destinationPages);
            $this->info('Views published successfully!');
        } else {
            $this->error('Source Pages directory not found in the package.');
        }
    }

    /**
     * Install an NPM package using the detected package manager.
     * Reusable for both vue-sonner and vue-draggable-plus.
     */
    protected function installPackage($packageName)
    {
        $this->info("Installing {$packageName}...");

        $command = "npm install {$packageName}"; // Default

        if (File::exists(base_path('yarn.lock'))) {
            $command = "yarn add {$packageName}";
        } elseif (File::exists(base_path('pnpm-lock.yaml'))) {
            $command = "pnpm add {$packageName}";
        } elseif (File::exists(base_path('bun.lockb'))) {
            $command = "bun add {$packageName}";
        }

        $this->comment("Running: $command");
        passthru($command);
    }

    /**
     * Install required shadcn-vue components.
     */
    protected function installShadcnComponents()
    {
        $this->info('Installing shadcn-vue components...');

        $components = ['button', 'card', 'dialog', 'input', 'label', 'select', 'switch'];
        $componentString = implode(' ', $components);

        $prefix = 'npx shadcn-vue@latest add';

        if (File::exists(base_path('pnpm-lock.yaml'))) {
            $prefix = 'pnpm dlx shadcn-vue@latest add';
        } elseif (File::exists(base_path('bun.lockb'))) {
            $prefix = 'bunx shadcn-vue@latest add';
        }

        $command = "$prefix $componentString";

        $this->comment("Running: $command");
        passthru($command);
    }
}
