<?php

namespace Guava\Capabilities\Commands;

use Illuminate\Console\Command;

class SyncRolesCommand extends Command
{
    public $signature = 'roles:sync';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
