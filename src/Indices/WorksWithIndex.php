<?php

namespace Lelastico\Indices;

use Elasticsearch\Client;

/**
 * @property Client client
 */
trait WorksWithIndex
{
    /**
     * Returns the properties mapping.
     *
     * @return array
     */
    protected function propertyMappings(): array
    {
        return [];
    }

    /**
     * Returns settings for the index.
     *
     * @return array
     */
    protected function settings(): array
    {
        return [];
    }

    /**
     * Creates an index.
     */
    public function create()
    {
        $body = [];

        // Add mappings
        $properties = $this->propertyMappings();
        if (!empty($properties)) {
            $body['mappings'] = [
                'properties' => $properties,
            ];
        }

        // Add settings
        $settings = $this->settings();
        if (!empty($settings)) {
            $body['settings'] = $settings;
        }

        // Create index
        $this->client->indices()->create([
            'index' => $this->name,
            'body' => $body,
        ]);
    }

    /**
     * Update mappings (if contains) and settings.
     */
    public function update(bool $skipSettingsUpdate = false)
    {
        // Update mapping
        $properties = $this->propertyMappings();
        if (!empty($properties)) {
            $this->client->indices()->putMapping([
                'index' => $this->name,
                'body' => [
                    'properties' => $properties,
                ],
            ]);
        }

        // For quick updates we do not need to update settings.
        if (false === $skipSettingsUpdate) {
            // Update settings
            $settings = $this->settings();
            if (!empty($settings)) {
                // We need to close the index before we can update settings
                $this->client->indices()->close([
                    'index' => $this->name,
                ]);

                // Update the settings
                $this->client->indices()->putSettings([
                    'index' => $this->name,
                    'body' => $settings,
                ]);

                // Open the index
                $this->client->indices()->open([
                    'index' => $this->name,
                ]);
            }
        }
    }

    /**
     * Deletes the index.
     *
     * @return array
     */
    public function delete(): array
    {
        return $this->client->indices()->delete([
            'index' => $this->name,
        ]);
    }

    /**
     * Checks if the index exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->client->indices()->exists([
            'index' => $this->name,
        ]);
    }
}
