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
use core\orm\query\builder;
use DateTime;
use DateTimeZone;
use tool_diagnostic\content\content;

class totara_upgrade_history extends base {

    /** @var int */
    protected static $order = 50;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'totara_upgrade_history';
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


        $all_records = builder::table('upgrade_log')
            ->where_in('plugin', $this->config['whitelist'])
            ->order_by('id')
            ->get()
            ->all();

        // Display a readable time.
        foreach ($all_records as $record) {
            $record->timemodified =  (new DateTime())
                ->setTimestamp($record->timemodified)
                ->setTimezone(new DateTimeZone('UTC'))
                ->format("Y-m-d H:i:s e");
        }

        $content->set_data($this->json_encode($all_records));

        $content->set_data_type(content::TYPE_JSON);

        return $content;
    }
}
