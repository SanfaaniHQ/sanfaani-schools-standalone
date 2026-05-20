<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test
                            {--filter= : Filter tests by name}
                            {--testsuite= : Run a named PHPUnit test suite}
                            {--stop-on-failure : Stop after the first failing test}';

    protected $description = 'Run the PHPUnit test suite through the local Composer installation';

    public function handle(): int
    {
        $phpunit = base_path('vendor/bin/phpunit');

        if (! file_exists($phpunit)) {
            $this->error('PHPUnit is not installed. Run composer install before executing php artisan test.');

            return self::FAILURE;
        }

        $arguments = [PHP_BINARY, $phpunit];

        if ($this->option('filter')) {
            $arguments[] = '--filter';
            $arguments[] = (string) $this->option('filter');
        }

        if ($this->option('testsuite')) {
            $arguments[] = '--testsuite';
            $arguments[] = (string) $this->option('testsuite');
        }

        if ($this->option('stop-on-failure')) {
            $arguments[] = '--stop-on-failure';
        }

        $testingEnvironment = [
            'APP_ENV' => 'testing',
            'APP_KEY' => 'base64:6B51G2yW9oVncbGTPK/T5yJ4U4K7KBlpS7Mxfq/0slE=',
            'APP_CONFIG_CACHE' => base_path('bootstrap/cache/config.testing.php'),
            'APP_MAINTENANCE_DRIVER' => 'file',
            'BCRYPT_ROUNDS' => '4',
            'BROADCAST_CONNECTION' => 'null',
            'CACHE_STORE' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'PULSE_ENABLED' => 'false',
            'TELESCOPE_ENABLED' => 'false',
            'NIGHTWATCH_ENABLED' => 'false',
        ];
        $previousEnvironment = [];

        foreach ($testingEnvironment as $key => $value) {
            $previousEnvironment[$key] = getenv($key);
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $command = implode(' ', array_map('escapeshellarg', $arguments));
        $exitCode = self::FAILURE;

        passthru($command, $exitCode);

        foreach ($previousEnvironment as $key => $value) {
            if ($value === false) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);

                continue;
            }

            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return (int) $exitCode;
    }
}
