<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\suggest;

defined('MOODLE_INTERNAL') || die();


/**
 * Mock suggester, only used for PHUnit
 */
class mock_suggest extends suggest_base {

    public static string $search = 'bad';
    public static string $replace = 'good';

    public function is_ready(): bool {
        return static::is_available();
    }

    public function suggest_word(string $word): ?string {
        return str_replace(static::$search, static::$replace, $word);
    }

    public static function is_available(): bool {
        // The mock suggester is only available for PHPUnit and Behat
        return !empty(PHPUNIT_TEST) || (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING);
    }

    public static function get_name(): string {
        return 'mock_suggest';
    }


    public static function for_language(string $language, ?string $spelling = null): suggest_base {
        return new self();
    }
}
