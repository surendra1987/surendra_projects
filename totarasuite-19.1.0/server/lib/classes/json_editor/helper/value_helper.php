<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */
namespace core\json_editor\helper;

/**
 * Helper class for processing values, e.g. attrs.
 */
final class value_helper {
    /**
     * Clean url, also allow mailto scheme.
     *
     * @param string|null $url
     * @return string|null
     */
    public static function clean_url(?string $url): ?string {
        if ($url === null || $url === '') {
            return $url;
        }

        if (parse_url($url, PHP_URL_SCHEME) === 'mailto') {
            $url = fix_utf8($url);
        } else {
            $url = clean_param($url, PARAM_URL);
        }

        return $url;
    }
}
