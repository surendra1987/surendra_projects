<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic;

use core\collection;
use core\orm\query\builder;
use core_component;
use moodle_exception;
use moodle_url;
use stdClass;
use stored_file;
use Throwable;
use tool_diagnostic\provider\provider;

class manager {

    public const EXPORT_FILE_NAME = 'diagnostic.zip';

    /** @var provider[] */
    private $providers;

    /** @var array */
    private $config;

    /**
     * @param config_provider $config
     * @param bool $include_disabled
     * @param array|string[] $exclude_providers optionally exclude specific providers
     */
    public function __construct(
        config_provider $config,
        bool $include_disabled = false,
        array $exclude_providers = []
    ) {
        $this->config = $config->get_config();
        $this->providers = $this->load_providers($include_disabled, $exclude_providers);
    }

    /**
     * Run diagnostics.
     *
     * @return array The download URL and the filesize
     * @throws moodle_exception
     */
    public function run_diagnostics(): array {
        // We need an integer for the files.itemid field, so let's just take the current time.
        $file_storage_item_id = time();

        $temp_dir = make_request_directory();

        $this->execute_providers($temp_dir);

        $stored_file = $this->finalize($file_storage_item_id, $temp_dir);

        return [
            'download_url' => $this->get_plugin_file_url($file_storage_item_id),
            'download_filesize' => $stored_file->get_filesize()
        ];
    }

    /**
     * @param int $file_storage_item_id
     * @return string
     */
    private function get_plugin_file_url(int $file_storage_item_id): string {
        return moodle_url::make_pluginfile_url(
            SYSCONTEXTID,
            'tool_diagnostic',
            'export',
            $file_storage_item_id,
            '/',
            static::EXPORT_FILE_NAME
        );
    }

    /**
     * Load all providers.
     *
     * @param bool $include_disabled
     * @param array $exclude_providers
     * @return array|provider[]
     */
    private function load_providers(bool $include_disabled = false, array $exclude_providers = []) {
        $providers = [];
        foreach ($this->get_provider_classes() as $class) {
            $provider_id = $class::get_id();
            if (isset($this->config[$provider_id])) {
                /** @var provider $provider */
                $provider = new $class($this->config[$provider_id]);
                if (($provider->is_enabled() || $include_disabled) && !in_array($provider_id, $exclude_providers)) {
                    $providers[] = $provider;
                }
            }
        }

        return $providers;
    }

    /**
     * Get providers.
     *
     * @return array|provider[]
     */
    public function get_providers(): array {
        return $this->providers;
    }

    /**
     * Get all providers as objects
     *
     * @return stdClass[]
     */
    public function get_all_provider_instances(): array {
        $providers = [];
        foreach ($this->providers as $provider) {
            $instance = new stdClass();
            $instance->id = $provider::get_id();
            $instance->name = $provider->get_name();
            $instance->enabled = $provider->is_enabled();
            $instance->whitelist = $provider->get_whitelist_html();
            $instance->description = $provider::get_description();
            $providers[] = $instance;
        }

        return $providers;
    }

    /**
     * Get all existing provider classes.
     *
     * @return array
     */
    private function get_provider_classes(): array {
        $classes = core_component::get_namespace_classes(
            'provider',
            provider::class
        );

        // We want the providers in a certain sort order
        return collection::new($classes)->sort(function ($a, $b) {
            return $a::get_order() <=> $b::get_order();
        })->to_array();
    }

    /**
     * Execute providers.
     *
     * @param string $temp_dir
     * @return void
     */
    private function execute_providers(string $temp_dir): void {
        foreach ($this->providers as $provider) {
            if (CLI_SCRIPT && !PHPUNIT_TEST) {
                echo get_string('provider_executing', 'tool_diagnostic', $provider->get_name());
            }

            try {
                $provider->execute($temp_dir);
            } catch (Throwable $e) {
                if (CLI_SCRIPT) {
                    echo get_string('provider_execution_failed', 'tool_diagnostic', $e->getMessage()) . PHP_EOL;
                }
            }

            if (CLI_SCRIPT && !PHPUNIT_TEST) {
                echo get_string('provider_done', 'tool_diagnostic') . PHP_EOL;
            }
        }
    }

    /**
     * Finalize the export process.
     *  - Create a ZIP from the individual files created by the providers.
     *  - Add it to file storage.
     *  - Remove the individual files.
     *
     * @param int $file_storage_item_id
     * @param string $temp_dir
     * @return ?stored_file
     */
    private function finalize(int $file_storage_item_id, string $temp_dir): ?stored_file {
        if (CLI_SCRIPT && !PHPUNIT_TEST) {
            echo get_string('cli_zipping', 'tool_diagnostic') . PHP_EOL;
        }

        // Providers will create files in a directory - zip them and clean up.
        $files_temp = get_directory_list($temp_dir, '', false, true, true);

        // If there are no files to zip then return.
        if (empty($files_temp)) {
            return null;
        }

        $files = [];
        foreach ($files_temp as $file) {
            $files[$file] = $temp_dir . '/' . $file;
        }

        // Zip files
        $zip_packer = get_file_packer();
        /** @var stored_file $stored_file */
        $stored_file = $zip_packer->archive_to_storage($files, SYSCONTEXTID, 'tool_diagnostic', 'export', $file_storage_item_id, '/', static::EXPORT_FILE_NAME);
        if (!$stored_file) {
            throw new moodle_exception('error_zip_storage_failed', 'tool_diagnostic');
        }

        // Cleanup providers.
        if (CLI_SCRIPT && !PHPUNIT_TEST) {
            echo get_string('cli_cleaning_up', 'tool_diagnostic') . PHP_EOL;
        }
        foreach ($this->providers as $provider) {
            $provider->cleanup($temp_dir);
        }

        return $stored_file;
    }

    /**
     * Returns the export file record for given item id.
     *
     * @param int $item_id
     * @return stdClass|null
     */
    public static function get_result_file_record(int $item_id): ?stdClass {
        return builder::table('files')
            ->where('contextid', SYSCONTEXTID)
            ->where('component', 'tool_diagnostic')
            ->where('filearea', 'export')
            ->where('filepath', '/')
            ->where('filename', static::EXPORT_FILE_NAME)
            ->where('itemid', $item_id)
            ->one(false);
    }
}
