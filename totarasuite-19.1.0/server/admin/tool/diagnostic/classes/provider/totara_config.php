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

class totara_config extends base {

    /** @var int */
    protected static $order = 60;

    protected function validate_config(): void {
        parent::validate_config();

        if (!array_key_exists('whitelist', $this->config)) {
            throw new coding_exception("Invalid config: missing key 'whitelist' for provider " . static::get_id());
        }
    }

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'totara_config';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->json_encode($this->get_filtered_cfg()));
        $content->set_data_type(content::TYPE_JSON);

        return $content;
    }

    /**
     * Filter output based on configuration settings.
     *
     * @return array
     */
    private function get_filtered_cfg(): array {
        global $CFG;
        $all_cfg = get_object_vars($CFG);
        $result = array_intersect_key($all_cfg, array_flip($this->config['whitelist']));
        ksort($result);
        return $result;
    }
}
