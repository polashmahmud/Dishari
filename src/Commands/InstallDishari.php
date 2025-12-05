<?php

namespace Polashmahmud\Dishari\Commands;

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
    protected $description = 'Install the Dishari package (Publish config, views, libs, migrations, dependencies and components)';

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

        // 3. Publish Library Files (iconMap)
        $this->publishLibraryFiles();

        // 4. Manage Migrations (Publish & Run) - (New Step)
        $this->manageMigrations();

        // 5. Ask to install 'vue-sonner' dependency
        if ($this->confirm('This package requires "vue-sonner". Do you want to install it now?', true)) {
            $this->installPackage('vue-sonner');
        } else {
            $this->warn('Please manually install "vue-sonner" later.');
        }

        // 6. Ask to install 'vue-draggable-plus' dependency
        if ($this->confirm('This package requires "vue-draggable-plus" for drag & drop. Do you want to install it now?', true)) {
            $this->installPackage('vue-draggable-plus');
        } else {
            $this->warn('Please manually install "vue-draggable-plus" later.');
        }

        // 7. Ask to install shadcn-vue components
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
     * Manually copy views and components to the correct directory.
     */
    protected function publishViews($directoryName)
    {
        $this->info("Publishing views and components...");

        $packageRoot = __DIR__ . '/../../';

        // 1. Publish Pages
        $sourcePages = $packageRoot . 'resources/js/pages';
        $destinationPages = resource_path("js/pages/{$directoryName}");

        if (File::exists($sourcePages)) {
            File::ensureDirectoryExists($destinationPages);
            File::copyDirectory($sourcePages, $destinationPages);
            $this->info("Views published to: resources/js/pages/{$directoryName}");
        } else {
            $this->error('Source Pages directory not found in the package.');
        }

        // 2. Publish Components
        $sourceComponents = $packageRoot . 'resources/js/components';
        $destinationComponents = resource_path("js/components/{$directoryName}");

        if (File::exists($sourceComponents)) {
            File::ensureDirectoryExists($destinationComponents);
            File::copyDirectory($sourceComponents, $destinationComponents);
            $this->info("Components published to: resources/js/components/{$directoryName}");
        } else {
            $this->warn('Source Components directory not found in the package.');
        }
    }

    /**
     * Publish the library files (e.g., iconMap.ts).
     */
    protected function publishLibraryFiles()
    {
        $this->info("Publishing library files (iconMap) to: resources/js/lib...");

        $packageRoot = __DIR__ . '/../../';
        $sourceLib = $packageRoot . 'resources/js/lib';
        $destinationLib = resource_path('js/lib');

        if (File::exists($sourceLib)) {
            File::ensureDirectoryExists($destinationLib);
            File::copyDirectory($sourceLib, $destinationLib);
            $this->info('Library files published successfully!');
        } else {
            $this->error('Source Lib directory not found in the package.');
        }
    }

    /**
     * Manage Migrations: Publish and/or Run.
     */
    protected function manageMigrations()
    {
        // Ask to publish migration files
        if ($this->confirm('Do you want to publish the migration files?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'dishari-migrations'
            ]);
            $this->info('Migration files published successfully!');
        } else {
            $this->comment('Skipping migration publishing (Package migrations will be loaded automatically).');
        }

        // Ask to run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->info('Running migrations...');
            $this->call('migrate');
            $this->info('Migrations executed successfully!');
        } else {
            $this->comment('Skipping migration execution.');
        }
    }

    /**
     * Install an NPM package using the detected package manager.
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

        $components = ['button', 'card', 'dialog', 'input', 'label', 'select', 'switch', 'radio-group'];
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
