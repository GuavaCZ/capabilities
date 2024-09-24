<?php

namespace Guava\Capabilities\Commands;

use Illuminate\Console\Command;

class SyncCapabilitiesCommand extends Command
{
    public $signature = 'capabilities:sync';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
