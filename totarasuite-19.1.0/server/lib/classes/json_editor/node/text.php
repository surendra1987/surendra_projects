<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package core
 */
namespace core\json_editor\node;

use core\json_editor\helper\node_helper;
use core\json_editor\node\abstraction\inline_node;
use html_writer;
use core\json_editor\formatter\formatter;

final class text extends node implements inline_node {
    /**
     * @var array
     */
    private $marks;

    /**
     * @var string
     */
    private $value;

    /**
     * @param array $node
     * @return node
     */
    public static function from_node(array $node): node {
        /** @var text $text */
        $text = parent::from_node($node);

        $text->marks = [];
        $text->value = '';

        if (array_key_exists('marks', $node) && is_array($node['marks'])) {
            $text->marks = $node['marks'];
        }

        if (array_key_exists('text', $node)) {
            $text->value = (string) $node['text'];
        }

        return $text;
    }

    /**
     * @param array $raw_node
     * @return bool
     */
    public static function validate_schema(array $raw_node): bool {
        if (!array_key_exists('text', $raw_node)) {
            return false;
        }

        if (isset($raw_node['marks']) && (!is_array($raw_node['marks']) || !self::validate_schema_marks($raw_node['marks']))) {
            return false;
        }

        return node_helper::check_keys_match_against_data($raw_node, ['type', 'text'], ['marks']);
    }

    /**
     * @param array $raw_node
     * @return array|null
     */
    public static function clean_raw_node(array $raw_node): ?array {
        $cleaned_raw_node = parent::clean_raw_node($raw_node);

        if (null === $cleaned_raw_node) {
            return null;
        }

        if (array_key_exists('marks', $cleaned_raw_node)) {
            $cleaned_raw_node['marks'] = self::clean_raw_node_marks($cleaned_raw_node['marks']);
        }

        return $cleaned_raw_node;
    }

    /**
     * @param formatter $formatter
     * @return string
     */
    public function to_html(formatter $formatter): string {
        return s($this->value);
    }

    /**
     * @param formatter $formatter
     * @return string
     */
    public function to_text(formatter $formatter): string {
        return $this->value;
    }

    /**
     * @return array|null
     */
    public function get_raw_marks(): ?array {
        return $this->marks;
    }

    /**
     * @return string
     */
    protected static function do_get_type(): string {
        return 'text';
    }

    /**
     * @param string $text
     * @return array
     */
    public static function create_json_node_from_text(string $text): array {
        return [
            'type' => static::get_type(),
            'text' => $text,
            'marks' => []
        ];
    }

    /**
     * @param string $text
     * @param string|\moodle_url $url
     * @return array
     */
    public static function create_json_node_from_link(string $text, $url): array {
        if ($url instanceof \moodle_url) {
            $url = $url->out(false);
        }
        return [
            'type' => static::get_type(),
            'text' => $text,
            'marks' => [
                [
                    'type' => 'link',
                    'attrs' => [
                        'href' => (string)$url
                    ]
                ]
            ]
        ];
    }
}