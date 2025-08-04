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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\task;

use auth_ssosaml\data_provider\idp as idp_provider;

defined('MOODLE_INTERNAL') || die();

/**
 * Session cleanup task.
 */
class remote_metadata_refresh extends \core\task\scheduled_task {
    /**
     * @inheritDoc
     *
     * @return string
     */
    public function get_name() {
        return get_string('metadata_refresh', 'auth_ssosaml');
    }

    /**
     * Do the job.
     */
    public function execute() {
        if (!is_enabled_auth('ssosaml')) {
            return;
        }

        $enabled_idps = idp_provider::get_enabled();
        foreach ($enabled_idps as $idp) {
            try {
                $idp->refresh_metadata();
            } catch (\Exception $e) {
                mtrace('Failed to refresh metadata for IdP "' . $idp->label . '": ' . $e->getMessage());
                continue;
            }
        }
    }
}
