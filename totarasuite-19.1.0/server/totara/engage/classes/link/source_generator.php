<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package totara_engage
 */

namespace totara_engage\link;

use moodle_url;

/**
 * Link source generator. Any page that should be returned to in a "back" style button
 * should implement this class.
 *
 * @package totara_engage\link
 */
abstract class source_generator {
    /**
     * List of attributes that must be provided in the source else this source is not valid
     *
     * @var array
     */
    protected static $required_attributes = [];

    /**
     * Attributes used to build the source string
     *
     * @var array
     */
    protected $attributes;

    /**
     * Prevent direct construction
     *
     * @param array $attributes
     */
    private function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Create a simple instance of this class.
     *
     * @param array $attributes
     * @return source_generator
     */
    public static function make(array $attributes): source_generator {
        return new static($attributes);
    }

    /**
     * Validate that the provided source was correct
     *
     * @param array $attributes
     * @return bool
     */
    public static function validate(array $attributes): bool {
        foreach (static::$required_attributes as $required_attribute) {
            if (empty($attributes[$required_attribute])) {
                debugging("Required attribute '$required_attribute' was not provided");
                return false;
            }
        }

        return true;
    }

    /**
     * A short code used to uniquely identify this particular source
     *
     * @return string
     */
    abstract public static function get_source_key(): string;

    /**
     * Convert the source params provided by the string eg ek.2.test.5 into an array.
     * So pl.55.l would convert to ['id' => 55, 'library' => true] on the totara_playlist source generator.
     *
     * Used when we have a source string and what to find out what URL it's valid for.
     *
     * Internal method - used by the base builder.
     *
     * @param array $source_params
     * @return array
     */
    abstract public static function convert_source_to_attributes(array $source_params): array;

    /**
     * Called by the link builder to generate the ?source= parameter.
     *
     * @return string
     */
    public function build_source(): string {
        $params = array_merge([static::get_source_key()], $this->convert_attributes_to_source($this->attributes));
        $source = implode(builder::FIELD_SPLIT, $params);

        return self::prepend_previous_sources($source);
    }

    /**
     * Prepend sources from previous navigation history, so we can navigate back more than one level.
     *
     * @param string $source
     * @return string
     */
    public static function prepend_previous_sources(string $source): string {
        $complete_sources_string = self::get_previous_sources() . builder::SOURCE_SPLIT . $source;
        
        return self::clean_sources_string($complete_sources_string);
    }

    /**
     * Find the source parameter for the current page. It records the previous sources we navigated from.
     *
     * @return string
     */
    private static function get_previous_sources(): ?string {
        // First check POST/GET.
        $prev_sources = optional_param('source', null, PARAM_TEXT);

        if (!$prev_sources) {
            // For Ajax requests, we won't find it in POST/GET, but in the referer.
            $query = parse_url(get_local_referer(false), PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $query_parts);
                $prev_sources = $query_parts['source'] ?? null;
            }
        }

        return $prev_sources;
    }

    /**
     * Remove invalid parts from a concatenated string of any number of sources.
     *
     * @param string $sources_string
     * @return string
     */
    private static function clean_sources_string(string $sources_string): string {
        $sources = explode(builder::SOURCE_SPLIT, $sources_string);
        $sources_validated = [];
        $last_valid_source = '';
        foreach ($sources as $source) {
            // We don't want unknown sources
            $check = builder::from_source($source);
            if ($check instanceof empty_destination) {
                continue;
            }

            // We never want the same source twice in a row.
            if ($source === $last_valid_source) {
                continue;
            }

            $last_valid_source = $source;
            $sources_validated[] = $source;
        }
        return implode(builder::SOURCE_SPLIT, $sources_validated);
    }

    /**
     * Customise the URL. Used to attach special params outside the scope of this generator.
     *
     * @param array $destination_attributes
     * @param moodle_url $url
     * @return void
     */
    public function add_custom_url_params(array $destination_attributes, moodle_url $url): void {
    }

    /**
     * Return the values that should be squashed together into a source string.
     * ['abc', 123, 555] for a playlist will turn into pl.abc.123.555.
     * Position of the elements is important, keys are not kept.
     *
     * @param array $attributes
     * @return array
     */
    abstract protected function convert_attributes_to_source(array $attributes): array;
}