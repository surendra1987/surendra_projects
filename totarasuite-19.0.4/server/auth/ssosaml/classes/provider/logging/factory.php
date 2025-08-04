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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\logging;

use auth_ssosaml\model\idp;

/**
 * Factory class to create a logger.
 */
class factory {
    /**
     * Get the configured logger
     *
     * @param idp $idp
     * @return contract
     */
    public static function get_logger(idp $idp): contract {
        if ($idp->debug) {
            return new db_logger($idp->id, $idp->sp_config->test_mode);
        } else {
            return new null_logger();
        }
    }
}
