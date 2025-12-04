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
        $this->info('Publishing Dishari configuration and views...');

        $this->call('vendor:publish', [
            '--tag' => 'dishari-config',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'dishari-views',
            '--force' => true,
        ]);

        $this->info('Dishari Menu Package installed successfully.');

        return Command::SUCCESS;
    }
}
