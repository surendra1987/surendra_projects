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
 * @package mfa_button
 */

namespace mfa_button;

use core_mfa\factor as base_factor;

/**
 * Simple "press a button" factor for unit testing.
 *
 * @package mfa_button
 */
class factor extends base_factor {
    /** @inheritDoc */
    protected string $name = 'button';

    /** @inheritDoc */
    public function has_verify_ui(): bool {
        return true;
    }

    /** @inheritDoc */
    public function verify(int $user_id, array $data, array $instances): bool {
        if ((!defined('BEHAT_SITE_RUNNING') || (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING == false))
            && (!defined('PHPUNIT_TEST') || (defined('PHPUNIT_TEST') && PHPUNIT_TEST == false))
        ) {
            return false;
        }
        if (!empty($data['invalid'])) {
            return false;
        }
        return true;
    }
}
