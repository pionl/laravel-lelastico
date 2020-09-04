<?php

namespace Lelastico\Console;

use Elasticsearch\Client;
use Exception;
use Illuminate\Console\Command;
use Lelastico\Contracts\IndicesServiceContract;
use Lelastico\Indices\AbstractElasticIndex;

class UpdateIndicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:indices {--only=} {--f} {--d} {--skip-settings-update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the elastic indices
        --only="only", handle only given index
        --f, will delete the index and data. Will new index with mappings
        --d, will delete the index and data
        --skip-settings-update, when upadting, the index is closed / opened due the settings update. You can skip it
        by provided this option.';

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function handle(Client $client, IndicesServiceContract $indicesServiceContract)
    {
        // Get arguments
        $reCreatedIndex = $this->option('f');
        $deleteIndex = $this->option('d');
        $indexOnly = $this->option('only');

        $indices = $indicesServiceContract->getAvailableIndices();

        if (empty($indices)) {
            $this->warn('No elastic search indices');

            return;
        }

        foreach ($indices as $IndexClass) {
            /** @var AbstractElasticIndex $index */
            $index = new $IndexClass($client);

            // Run index mapping if the only option is not set or if the index name matches the
            // index
            if (null !== $indexOnly && $index->cleanName !== $indexOnly) {
                continue;
            }

            $this->info("$index->name");

            // Should we delete the index to force mappings or check if the index exists
            $exists = $index->exists();
            if ($exists && ($reCreatedIndex || $deleteIndex)) {
                $this->warn('   Deleting index');
                $index->delete();
                $exists = false;
            }

            // Only deleting?
            if ($deleteIndex) {
                continue;
            }

            // Create index or update mappings
            if ($exists) {
                $this->line('   Updating mappings');
                $index->update(true === $this->option('skip-settings-update'));
            } else {
                $this->line('   Creating index with mappings');

                $index->create();
            }

            // Stop the foreach if only limit has been matched.
            if (null !== $indexOnly) {
                break;
            }
        }
    }
}
