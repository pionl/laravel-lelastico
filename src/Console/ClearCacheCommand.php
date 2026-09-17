<?php

declare(strict_types=1);

namespace Lelastico\Console;

use Elasticsearch\Client;
use Illuminate\Console\Command;

class ClearCacheCommand extends Command
{
    protected $signature = 'elastic:clear 
    {index? : change only given index} ';

    protected $description = 'Clears elastic search cache';

    public function handle(Client $client): void
    {
        $client->indices()
            ->clearCache([
                'index' => $this->argument('index'),
            ]);
    }
}
