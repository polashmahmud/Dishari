<?php

namespace Polashmahmud\Menu\Commands;

use Illuminate\Console\Command;

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

    protected $description = 'Install Dishari Menu Package';
    /**
     * Execute the console command.
     */

    public function handle(): int
    {
        $this->info('Starting Dishari installation...');

        // 1. Ask to publish the configuration file
        if ($this->confirm('Do you want to publish the configuration file?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'dishari-config'
            ]);
            $this->info('Configuration published successfully!');
        } else {
            $this->comment('Skipping configuration publishing.');
        }

        // 2. Ask to publish VueJS Views (Pages & Components)
        if ($this->confirm('Do you want to publish VueJS Views (Pages & Components)?', true)) {
            // Note: If the user wants to change the path by editing the config,
            // they should publish the config first and edit it.
            // Here, it will be published according to the default config.

            $this->call('vendor:publish', [
                '--tag' => 'dishari-views'
            ]);
            $this->info('Views published successfully!');
        } else {
            $this->comment('Skipping views publishing.');
        }

        $this->info('Dishari installation completed successfully! 🚀');
        return Command::SUCCESS;
    }
}
