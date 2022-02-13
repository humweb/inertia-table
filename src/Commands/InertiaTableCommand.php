<?php

namespace Humweb\InertiaTable\Commands;

use Illuminate\Console\Command;

class InertiaTableCommand extends Command
{
    public $signature = 'inertia-table';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
