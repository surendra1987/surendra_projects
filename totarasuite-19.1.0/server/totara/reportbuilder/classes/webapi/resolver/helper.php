<?php
/*
 * This file is part of Totara LMS
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
 * @author Carl Anderson <carl.anderson@totaralearning.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\webapi\resolver;

use context_system;
use core\record\tenant;

/**
 * Helper trait containing functions useful to the reportbuilder resolvers
 */
trait helper {

    /**
     * Returns whether the current user can edit reports
     * @param int|null $tenantid
     * @return bool Whether the current user can edit reports
     */
    private static function user_can_edit_report(?int $tenantid = null) {
        $context = context_system::instance();
        if (!empty($tenantid)) {
            $context = tenant::fetch($tenantid)->category_context;
        }
        return has_capability('totara/reportbuilder:managereports', $context);
    }

    /**
     * Returns whether the current user can create reports
     * @param int|null $tenantid
     * @return bool Whether the current user can create reports
     */
    private static function user_can_create_reports(?int $tenantid = null) {
        $context = context_system::instance();
        if (!empty($tenantid)) {
            $context = tenant::fetch($tenantid)->category_context;
        }
        return has_capability('totara/reportbuilder:managereports', $context);
    }
}