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

class platform_database extends base {

    /** @var int */
    protected static $order = 20;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'platform_database';
    }

    protected function validate_config(): void {
        parent::validate_config();

        if (!array_key_exists('dbconfig_whitelist', $this->config)) {
            throw new coding_exception("Invalid config: missing key 'dbconfig_whitelist' for provider " . static::get_id());
        }
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->get_info());
        $content->set_data_type(content::TYPE_TEXT);

        return $content;
    }

    /**
     * @return string
     */
    private function get_info(): string {
        global $DB;

        $db_family = 'DB family:' . PHP_EOL . $DB->get_dbfamily();
        $db_server_info = 'DB server info:' . PHP_EOL . json_encode($DB->get_server_info());

        $db_config = $DB->export_dbconfig();
        $db_type = 'DB type: ' . PHP_EOL . $db_config->dbtype;
        $db_library = 'DB library: ' . PHP_EOL . $db_config->dblibrary;

        if ($DB->get_dbfamily() === 'mysql') {
            $db_collation = 'DB collation: ' . PHP_EOL . $DB->get_dbcollation();
        } else {
            $db_collation = 'DB collation: ' . PHP_EOL . 'na';
        }

        $db_options = [];

        foreach ($this->config['dbconfig_whitelist'] as $option) {
            $value = $db_config->dboptions[$option] ?? null;
            // Only add it if the option exists.
            if ($value !== null) {
                $db_options[$option] = $value;
            }
        }

        $db_options = 'DB Options: ' . PHP_EOL . json_encode($db_options);


        return $db_family . PHP_EOL . PHP_EOL
            . $db_server_info . PHP_EOL . PHP_EOL
            . $db_type . PHP_EOL . PHP_EOL
            . $db_library . PHP_EOL . PHP_EOL
            . $db_collation . PHP_EOL . PHP_EOL
            . $db_options . PHP_EOL . PHP_EOL;
    }

}
