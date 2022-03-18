<?php

declare(strict_types=1);

namespace Lelastico\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Lelastico\Contracts\IndicesServiceContract;
use Lelastico\Indices\AbstractElasticIndex;

class UpdateIndicesCommand extends Command
{
    protected $signature = 'elastic:indices 
    {index? : change only given index} 
    {--f|force : will delete the index and data and creates a new index.} 
    {--d|delete : will delete the index and data} 
    {--s|skip-settings-update : Do not not update settings to prevent index close / open.}';

    protected $description = 'Change elastic indices';

    /**
     * Execute the console command.
     */
    public function handle(IndicesServiceContract $indicesServiceContract, Container $container): void
    {
        $forceCreateNewIndex = $this->option('force');
        $deleteIndex = $this->option('delete');
        $preferredIndex = $this->argument('index');

        $indices = $indicesServiceContract->getAvailableIndices();

        if (empty($indices)) {
            $this->warn('No available indices');

            return;
        }

        $processed = false;

        foreach ($indices as $IndexClass) {
            /** @var AbstractElasticIndex $index */
            $index = $container->make($IndexClass);

            // Run index mapping if the only option is not set or if the index name matches the
            // index
            if ($preferredIndex !== null && $index->cleanName !== $preferredIndex) {
                continue;
            }

            $processed = true;

            $this->info($index->name);

            // Should we delete the index to force mappings or check if the index exists
            $exists = $index->exists();
            if ($exists && ($forceCreateNewIndex || $deleteIndex)) {
                $this->warn('☠️  Deleting index');
                $index->delete();
                $exists = false;
            }

            // Only deleting?
            if ($deleteIndex) {
                continue;
            }

            // Create index or update mappings
            if ($exists) {
                $this->line('⛑  Updating mappings');
                $index->update($this->option('skip-settings-update') === true);
            } else {
                $this->line('🚀  Creating index with mappings');

                $index->create();
            }

            // Stop the foreach if only limit has been matched.
            if ($preferredIndex !== null) {
                break;
            }
        }

        if ($processed === false && $preferredIndex !== null) {
            $this->warn('🆘 Given index does not exists: ' . $preferredIndex);
        }
    }
}
