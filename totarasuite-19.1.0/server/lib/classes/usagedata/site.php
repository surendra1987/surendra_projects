<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core
 */

namespace core\usagedata;

use tool_usagedata\config;
use tool_usagedata\export;

class site implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('site_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $CFG, $DB;

        return [
            'time' => time(),
            'timezone' => date_default_timezone_get() ?? self::NOTSET,
            'siteidentifier' => $CFG->siteidentifier ?? self::NOTSET,
            'usagedata_siteid' => config::get_site_identifier() ?? self::NOTSET,
            'registrationcode' => !empty($CFG->registrationcode) ? $CFG->registrationcode : self::NOTSET,
            'wwwroot' => $CFG->wwwroot ?? self::NOTSET,
            'currentflavour' => get_config('totara_flavour', 'currentflavour') ?? self::NOTSET,
            'forceflavour' => !empty($CFG->forceflavour) ? $CFG->forceflavour : self::NOTSET,
            'sitetype' => !empty($CFG->sitetype) ? $CFG->sitetype : self::NOTSET,
            'lastcron' => $DB->get_field_sql('SELECT MAX(lastruntime) FROM "ttr_task_scheduled"') ?? self::NOTSET,
            'debug' => $CFG->debug ?? self::NOTSET,
            'debugdisplay' => $CFG->debugdisplay ?? self::NOTSET,
            'language' => $CFG->lang ?? 'en',
            'maintenance_enabled' => $CFG->maintenance_enabled ?? self::NOTSET,
        ];
    }
}

