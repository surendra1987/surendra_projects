<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @package totara_notification
 */
namespace totara_notification\placeholder\abstraction;

/**
 * Empty-able placeholder which can default the value as representation string
 * for not able to resolve the value of the placeholder key.
 */
abstract class single_emptiable_placeholder implements single_placeholder {
    /**
     * Checks whether the placeholder is available with the given data for key
     * {$key} or not.
     *
     * @param string $key
     * @return bool
     */
    abstract protected function is_available(string $key): bool;

    /**
     * This function is called as part of {@see placeholder::get()}.
     *
     * Child classes should extend this function in order to be able to
     * provide the value for the given placeholder key.
     *
     * __Please make sure__ all placeholders requiring it are passed through either format_string or format_text.
     * There's no centralised way making sure the correct format is applied. Without it multilang or other
     * filters might not be correctly applied, or text editor formatting will not be correctly applied.
     *
     * If the value returned contains valid and safe html (e.g. format_text() applied) then
     * is_safe_html should correspondingly return true otherwise HTML won't be rendered correctly.
     *
     * @param string $key
     * @return string|null When the result expects an empty string, this should return "",
     * while null should return when the data is not available and will result in "<no data available for $key>".
     */
    abstract protected function do_get(string $key): ?string;

    /**
     * Give the representation string for not resolveable placeholder key, due to data not existing.
     * Extend this function to change the representation string for empty value.
     *
     * @param string $key
     * @return string
     * @deprecated since Totara 14.1
     */
    protected function get_representation_string_for_empty(string $key): string {
        debugging(
            'single_emptiable_placeholder::get_representation_string_for_empty has been deprecated. Empty values ' .
            'from single_emptiable_placeholder::get will automatically be replaced by the no_available_data_for_key string.',
            DEBUG_DEVELOPER
        );
        return get_string('no_available_data_for_key', 'totara_notification', $key);
    }

    /**
     * Get the placeholder value for the given key.
     *
     * __Please make sure__ all placeholders requiring it are passed through either format_string or format_text.
     * There's no centralised way making sure the correct format is applied. Without it multilang or other
     * filters might not be correctly applied, or text editor formatting will not be correctly applied.
     *
     * If the value returned contains valid and safe html (e.g. format_text() applied) then
     * is_safe_html should correspondingly return true otherwise HTML won't be rendered correctly.
     *
     * @inheritDoc
     * @param string $key
     * @return string|null When the result expects an empty string, this should return "",
     * while null should return when the data is not available and will result in "<no data available for $key>".
     */
    public function get(string $key): ?string {
        if (!$this->is_available($key)) {
            return NULL;
        }

        return $this->do_get($key);
    }

    /**
     * Checks if we are expecting html content from the value that associated with
     * the $key or not.
     *
     * If the corresponding placeholder value comes from user input then make sure to clean it
     * in your "get" function, using format_text, format_string or something similar.
     *
     * @param string $key
     * @return bool
     */
    public static function is_safe_html(string $key): bool {
        return false;
    }
}