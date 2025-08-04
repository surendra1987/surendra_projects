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
 * @package engage_course
 */
namespace engage_course\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use engage_course\totara_engage\share\course_provider;
use totara_engage\share\recipient\manager as recipient_manager;
use totara_engage\share\manager as share_manager;
use totara_engage\webapi\middleware\require_valid_recipients;

/**
 * Share courses with a recipient.
 */
class share_with_recipient_by_ids extends mutation_resolver {
    /** @inheritDoc */
    public static function resolve(array $args, execution_context $ec): bool {
        global $DB, $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(\context_user::instance($USER->id));
        }

        $transaction = $DB->start_delegated_transaction();

        $recipients = recipient_manager::create_from_array([$args['recipient']]);

        $provider = new course_provider();

        foreach ($args['ids'] as $id) {
            $instance = $provider->get_item_instance_from_instance_id($id);
            share_manager::share($instance, 'engage_course', $recipients);
        }

        $transaction->allow_commit();

        return true;
    }

    /** @inheritDoc */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('engage_resources'),
            new require_valid_recipients('recipient'),
        ];
    }
}
