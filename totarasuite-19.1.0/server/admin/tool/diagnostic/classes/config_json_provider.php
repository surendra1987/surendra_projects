<?php

namespace tool_diagnostic;

use coding_exception;

class config_json_provider implements config_provider {

    private $custom_config_filename = '';

    public function __construct() {
        global $CFG;

        // Let's set the default
        $this->custom_config_filename = $CFG->dirroot.'/admin/tool/diagnostic/config/config.json';
    }

    /**
     * Overwrite the custom config filenames
     *
     * @param string $custom_config_filename the full path to the custom config file to use
     */
    public function set_custom_config_filename(string $custom_config_filename): void {
        $this->custom_config_filename = $custom_config_filename;
    }

    /**
     * @return array
     */
    public function get_config(): array {
        return $this->make_config_from_file();
    }

    /**
     * Read the config file and convert it to a config array
     *
     * @return array
     * @throws coding_exception
     */
    private function make_config_from_file(): array {
        // We provide a way to easily overwrite the default configurations
        // without the need to touch checked in files.
        $config_file = $this->custom_config_filename;
        if (!file_exists($config_file)) {
            $config_file = dirname(__FILE__, 2) . '/config/config.json.dist';
        }
        $config_json = file_get_contents($config_file);
        $config = json_decode($config_json, true);

        if (!is_array($config)) {
            throw new coding_exception("JSON could not be decoded. Please check the config file: {$config_file}");
        }
        return $config;
    }
}
