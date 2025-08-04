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

namespace tool_diagnostic\provider;

use coding_exception;
use html_writer;
use tool_diagnostic\content\content;

abstract class base implements provider {

    /** @var string */
    protected $file;

    /** @var array  */
    protected $config;

    /** @var int */
    protected static $order = 0;

    public function __construct(array $config) {
        $this->config = $config;
        $this->validate_config();
    }

    /**
     * Validate configuration.
     *
     * All providers must have at least the 'enabled' key.
     *
     * @return void
     * @throws coding_exception
     */
    protected function validate_config(): void {
        if (!array_key_exists('enabled', $this->config)) {
            throw new coding_exception("Invalid config: missing key 'enabled' for provider " . static::get_id());
        }
        // If there's a whitelist make sure it only contains alphanumeric strings
        if (array_key_exists('whitelist', $this->config)) {
            $this->validate_whitelist($this->config['whitelist']);
        }
    }

    /**
     * Validate that only whitelist entries only contain allowed characters
     */
    private function validate_whitelist(array $whitelist): void {
        foreach ($whitelist as $whitelist_entry) {
            if (is_array($whitelist_entry)) {
                $this->validate_whitelist($whitelist_entry);
            } else {
                // Our whitelists currently only support alphanumeric characters, underscore and dots
                $is_valid = preg_match('/^[a-z0-9_.]+$/i', $whitelist_entry);
                if (!$is_valid) {
                    throw new coding_exception('Invalid whitelist entry detected');
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function is_enabled(): bool {
        return array_key_exists('enabled', $this->config)
            && $this->config['enabled'] === true;
    }

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string(static::get_id() . '_name', 'tool_diagnostic');
    }

    /**
     * @inheritDoc
     */
    public static function get_description(): string {
        return get_string(static::get_id() . '_description', 'tool_diagnostic');
    }

    /**
     * Get order of the provider for sorting the list
     *
     * @return int
     */
    public static function get_order(): int {
        return static::$order;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $path): void {
        $content = $this->get_content();
        $this->file = $path . DIRECTORY_SEPARATOR . $this->create_file_name($content);
        file_put_contents($this->file, $content->get_data());
    }

    /**
     * @inheritDoc
     */
    public function cleanup(string $path): void {
        if ($this->file && is_file($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * Create file name for output item.
     *
     * @param content $content
     *
     * @return string
     */
    private function create_file_name(content $content): string {
        return $content->get_id() . $this->get_file_extension($content->get_data_type());
    }

    /**
     * Get file extension for data type.
     *
     * @param string $type
     *
     * @return string
     */
    private function get_file_extension(string $type): string {
        switch ($type) {
            case content::TYPE_TEXT:
                return '.txt';
            case content::TYPE_HTML:
                return '.html';
            case content::TYPE_JSON:
                return '.json';
            default:
                throw new \coding_exception("Invalid output data type provided");
        }
    }

    /**
     * Wrapper for json_encode() so we can control options in one place.
     *
     * @param $value
     * @return string
     */
    protected function json_encode($value): string {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function get_whitelist_html(): string {
        $whitelists_html = '';
        $whitelists_html_1 = '';
        if (isset($this->config['whitelist'])) {
            $whitelists_html .= html_writer::start_tag('ul');
            foreach ($this->config['whitelist'] as $key => $whitelist) {
                if (is_string($whitelist)) {
                    $whitelists_html .= html_writer::tag('li', $whitelist);
                }

                if (is_array($whitelist)) {
                    $whitelists_html_1 .= html_writer::span($key);
                    $whitelists_html_1 .= html_writer::start_tag('ul');
                    foreach ($whitelist as $value) {
                        $whitelists_html_1 .= html_writer::tag('li', $value);
                    }
                    $whitelists_html_1  .= html_writer::end_tag('ul');
                }
            }
            $whitelists_html .= html_writer::end_tag('ul');
        } elseif (isset($this->config['dbconfig_whitelist'])) {
            $whitelists_html = html_writer::start_tag('ul');
            foreach ($this->config['dbconfig_whitelist'] as $whitelist) {
                $whitelists_html .= html_writer::tag('li', $whitelist);
            }
            $whitelists_html .= html_writer::end_tag('ul');
        }

        if (!empty($whitelists_html_1)) {
            return $whitelists_html_1;
        }

        return $whitelists_html;
    }
}
