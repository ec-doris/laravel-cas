<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cas:install 
                            {--routes : Publish and setup CAS routes}
                            {--config : Publish CAS configuration files}
                            {--all : Publish routes, config, and setup everything}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and setup Laravel CAS authentication';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Laravel CAS...');

        if ($this->option('all')) {
            $this->publishRoutes();
            $this->publishConfig();
            $this->updateWebRoutes();
            $this->showCompletionMessage();
            return 0;
        }

        if ($this->option('routes')) {
            $this->publishRoutes();
            $this->updateWebRoutes();
        }

        if ($this->option('config')) {
            $this->publishConfig();
        }

        if (!$this->option('routes') && !$this->option('config')) {
            // Default behavior - ask what to install
            $this->askWhatToInstall();
        }

        $this->showCompletionMessage();
        return 0;
    }

    /**
     * Ask user what to install
     */
    protected function askWhatToInstall(): void
    {
        $choices = $this->choice(
            'What would you like to install?',
            [
                'routes' => 'Publish CAS routes to routes/laravel-cas.php',
                'config' => 'Publish CAS configuration files',
                'all' => 'Install everything',
            ],
            'all',
            null,
            true
        );

        if (in_array('routes', $choices) || in_array('all', $choices)) {
            $this->publishRoutes();
            $this->updateWebRoutes();
        }

        if (in_array('config', $choices) || in_array('all', $choices)) {
            $this->publishConfig();
        }
    }

    /**
     * Publish CAS routes
     */
    protected function publishRoutes(): void
    {
        $routesFile = base_path('routes/laravel-cas.php');

        if ($this->files->exists($routesFile) && !$this->option('force')) {
            $this->warn('Routes file already exists. Use --force to overwrite.');
            return;
        }

        $this->info('Publishing CAS routes...');
        
        $this->call('vendor:publish', [
            '--tag' => 'laravel-cas-routes',
            '--force' => $this->option('force'),
        ]);

        $this->line('<info>✓</info> CAS routes published to routes/laravel-cas.php');
    }

    /**
     * Publish CAS configuration
     */
    protected function publishConfig(): void
    {
        $configFile = config_path('laravel-cas.php');

        if ($this->files->exists($configFile) && !$this->option('force')) {
            $this->warn('Configuration file already exists. Use --force to overwrite.');
            return;
        }

        $this->info('Publishing CAS configuration...');
        
        $this->call('vendor:publish', [
            '--tag' => 'laravel-cas-config',
            '--force' => $this->option('force'),
        ]);

        $this->line('<info>✓</info> CAS configuration published to config/laravel-cas.php');
    }

    /**
     * Update web.php to include CAS routes
     */
    protected function updateWebRoutes(): void
    {
        $webRoutesPath = base_path('routes/web.php');
        $casRoutesInclude = "require __DIR__ . '/laravel-cas.php';";

        if (!$this->files->exists($webRoutesPath)) {
            $this->warn('routes/web.php not found. Please manually include CAS routes.');
            return;
        }

        $webRoutesContent = $this->files->get($webRoutesPath);

        // Check if already included
        if (str_contains($webRoutesContent, $casRoutesInclude) || 
            str_contains($webRoutesContent, 'laravel-cas.php')) {
            $this->line('<info>✓</info> CAS routes already included in web.php');
            return;
        }

        // Add the include at the top after opening PHP tag
        $updatedContent = preg_replace(
            '/(<\?php\s*\n)/m',
            "$1\n// Include Laravel CAS routes\n{$casRoutesInclude}\n",
            $webRoutesContent
        );

        if ($updatedContent !== $webRoutesContent) {
            $this->files->put($webRoutesPath, $updatedContent);
            $this->line('<info>✓</info> CAS routes included in routes/web.php');
        } else {
            $this->warn('Could not automatically update web.php. Please manually add:');
            $this->warn("require __DIR__ . '/laravel-cas.php';");
        }
    }

    /**
     * Show completion message
     */
    protected function showCompletionMessage(): void
    {
        $this->info('');
        $this->info('Laravel CAS installation completed!');
        $this->info('');
        $this->line('Next steps:');
        $this->line('1. Configure your .env file with CAS settings:');
        $this->line('   CAS_URL=https://webgate.ec.europa.eu/cas');
        $this->line('   CAS_REDIRECT_LOGIN_ROUTE=dashboard  # The name of your post-login route');
        $this->line('   CAS_REDIRECT_LOGOUT_URL=https://your-app.com/');
        $this->line('');
        $this->line('2. Add the auth guard to config/auth.php (if not already present).');
        $this->line('3. Use the `cas.auth` middleware to protect your routes.');
        $this->line('4. Ensure your CAS server whitelists the callback URL: https://your-app.com/cas/callback');
    }
}