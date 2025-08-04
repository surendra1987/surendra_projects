<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 * @category test
 */

use core_my\models\perform_overview\state;

require_once(__DIR__.'/perform_overview_repository_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_perform_overview_repository_not_progressed_test
extends totara_competency_perform_overview_repository_testcase {
    /**
     * {@inheritDoc}
     */
    protected static function overview_state_under_test(): state {
        return state::not_progressed();
    }
}
