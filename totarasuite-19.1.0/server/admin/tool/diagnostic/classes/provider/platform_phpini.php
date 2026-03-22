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
* @package tool_diagnostic
*/

namespace tool_diagnostic\provider;

use coding_exception;
use tool_diagnostic\content\content;

/**
 * Note: As this is most probably run from the CLI, it will get ini values from the CLI PHP.
 * The webserver's PHP values may differ.
 */
class platform_phpini extends base {

    /** @var int */
    protected static $order = 30;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'platform_phpini';
    }

    protected function validate_config(): void {
        parent::validate_config();

        if (!array_key_exists('whitelist', $this->config)) {
            throw new coding_exception("Invalid config: missing key 'whitelist' for provider " . static::get_id());
        }
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->json_encode($this->get_ini_values()));
        $content->set_data_type(content::TYPE_JSON);

        return $content;
    }

    /**
     * @return array
     */
    private function get_ini_values(): array {
        $result = [];
        foreach ($this->config['whitelist'] as $ini_option) {
            $value = ini_get($ini_option);
            // Only add it if the option exists.
            if ($value !== false) {
                $result[$ini_option] = ini_get($ini_option);
            }
        }
        return $result;
    }

}
