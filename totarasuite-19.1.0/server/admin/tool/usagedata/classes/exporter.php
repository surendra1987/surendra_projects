<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata;

use coding_exception;
use core\plugininfo\base;
use core_component;
use core_plugin_manager;
use tool_usagedata\local\data;
use tool_usagedata\local\datacollection;
use tool_usagedata\api\signing_service_api;
use tool_usagedata\api\data_collector_api;

final class exporter {

    /** @var datacollection|null */
    private ?datacollection $data = null;

    /**
     * @param core_plugin_manager|null $plugin_manager
     * @return datacollection
     * @throws coding_exception
     */
    private function initialise_data(?core_plugin_manager $plugin_manager = null): datacollection {
        if ($this->data === null) {
            $this->data = new datacollection($this->get_export_classes($plugin_manager));
        }
        
        return $this->data;
    }

    /**
     * Retrieves the export classes
     * @param core_plugin_manager|null $plugin_manager
     * @return data[]
     * @throws coding_exception
     */
    private function get_export_classes(?core_plugin_manager $plugin_manager = null): array {
        return array_map(
            function ($class) {
                return new data($class);
            },
            $this->get_export_class_namespaces($plugin_manager)
        );
    }

    /**
     * Gets the export class namespaces
     * Filters out non-standard or core plugins
     * @param core_plugin_manager|null $plugin_manager
     * @return string[]
     * @throws coding_exception
     */
    private function get_export_class_namespaces(?core_plugin_manager $plugin_manager = null): array {
        if ($plugin_manager === null) {
            $plugin_manager = core_plugin_manager::instance();
        }

        $export_classes = core_component::get_namespace_classes(data::LOOKUP_NAMESPACE, export::class);

        // filter out any classes which are not part of the standard plugins
        $standard_plugins = $this->generate_standard_plugins($plugin_manager);
        return array_filter($export_classes, function ($class) use ($standard_plugins) {
            $data = new data($class);
            $plugin_type = $data->component_type;
            // core plugins can be bypassed
            if ($plugin_type === 'core') {
                return true;
            }

            $plugin_name = $data->component_name;
            if (!isset($standard_plugins[$plugin_type.'_'.$plugin_name])) {
                return false;
            }

            return true;
        });
    }

    /**
     * Generate a list of plugins which are considered standard plugins
     * This can be used to weed out plugins exporting data which are not
     * part of the standard list.
     *
     * @param core_plugin_manager|null $plugin_manager
     * @return array
     */
    private function generate_standard_plugins(?core_plugin_manager $plugin_manager = null): array {
        if ($plugin_manager === null) {
            $plugin_manager = core_plugin_manager::instance();
        }
        $installed_plugins = $plugin_manager->get_plugins();
        $standard_plugins = [];
        foreach ($installed_plugins as $types) {
            /**
             * @var base $plugin
             */
            foreach ($types as $plugin) {
                if ($plugin->is_standard()) {
                    $standard_plugins[$plugin->type.'_'.$plugin->name] = 1;
                }
            }
        }
        return $standard_plugins;
    }

    public function export(): array {
        return $this->initialise_data()->to_hierarchical_array();
    }

    public function purpose(): array {
        return $this->initialise_data()->to_informative_list_items();
    }

    /**
     * Collates data and sends to retrieved signed URL
     * @return bool
     * @throws \Exception
     */
    public function export_to_data_collector(): bool {
        // Get the export data before we retrieve the signed url as the signed url is time-limited
        $export_data = $this->export();

        $data_collection_url = (new signing_service_api())->retrieve_signed_url();
        if (!$data_collection_url) {
            return false;
        }

        return (new data_collector_api())->send_export_data(
            $data_collection_url,
            $export_data
        );
    }
}

