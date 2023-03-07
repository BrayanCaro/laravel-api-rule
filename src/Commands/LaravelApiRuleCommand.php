<?php

namespace BrayanCaro\LaravelApiRule\Commands;

use Illuminate\Console\Command;

class LaravelApiRuleCommand extends Command
{
    public $signature = 'laravel-api-rule';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
