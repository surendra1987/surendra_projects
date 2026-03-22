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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core
 */

namespace core\webapi\resolver\mutation;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core_mfa\escalation\base;

/**
 * Verify MFA factor
 */
class mfa_verify_factor extends mutation_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG, $USER;

        /** @var base $action_class */
        $action_class = $USER->mfa_action;
        if (!class_exists($action_class)) {
            throw new coding_exception("Invalid escalation class");
        }

        $escalation = new $action_class($USER->pseudo_id);
        if (!$escalation instanceof base) {
            throw new coding_exception("Class provided is not an instance of escalation");
        }

        $verified = $escalation->verify(
            $args['input']['factor'],
            json_decode($args['input']['data'], true)
        );

        if (!$verified) {
            return ['success' => false, 'next_url' => null];
        }

        return [
            'success' => true,
            'next_url' => $CFG->wwwroot . '/mfa/complete.php',
        ];
    }
}
