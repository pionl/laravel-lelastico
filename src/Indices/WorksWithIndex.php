<?php

declare(strict_types=1);

namespace Lelastico\Indices;

use Elasticsearch\Client;

trait WorksWithIndex
{
    public Client $client;

    /**
     * Creates an index.
     */
    public function create(): void
    {
        $body = [];

        // Add mappings
        $properties = $this->propertyMappings();
        if (empty($properties) === false) {
            $body['mappings'] = [
                'properties' => $properties,
            ];
        }

        // Add settings
        $settings = $this->settings();
        if (empty($settings) === false) {
            $body['settings'] = $settings;
        }

        // Create index
        $this->client->indices()
            ->create([
                'index' => $this->name,
                'body' => $body,
            ]);
    }

    /**
     * Update mappings (if contains) and settings.
     */
    public function update(bool $skipSettingsUpdate = false): void
    {
        // Update mapping
        $properties = $this->propertyMappings();
        if (empty($properties) === false) {
            $this->client->indices()
                ->putMapping([
                    'index' => $this->name,
                    'body' => [
                        'properties' => $properties,
                    ],
                ]);
        }

        // For quick updates we do not need to update settings.
        if ($skipSettingsUpdate === false) {
            // Update settings
            $settings = $this->settings();
            if (empty($settings) === false) {
                // We need to close the index before we can update settings
                $this->client->indices()
                    ->close([
                        'index' => $this->name,
                    ]);

                // Update the settings
                $this->client->indices()
                    ->putSettings([
                        'index' => $this->name,
                        'body' => $settings,
                    ]);

                // Open the index
                $this->client->indices()
                    ->open([
                        'index' => $this->name,
                    ]);
            }
        }
    }

    /**
     * Deletes the index.
     */
    public function delete(): array
    {
        return $this->client->indices()
            ->delete([
                'index' => $this->name,
            ]);
    }

    /**
     * Checks if the index exists.
     */
    public function exists(): bool
    {
        return $this->client->indices()
            ->exists([
                'index' => $this->name,
            ]);
    }

    /**
     * Returns the properties mapping.
     */
    protected function propertyMappings(): array
    {
        return [];
    }

    /**
     * Returns settings for the index.
     */
    protected function settings(): array
    {
        return [];
    }
}
