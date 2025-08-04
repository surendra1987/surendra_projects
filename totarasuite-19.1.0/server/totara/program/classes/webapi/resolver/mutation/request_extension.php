<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\mutation;

use core\entity\user;
use core\orm\query\builder;
use core\webapi\execution_context;
use core\webapi\middleware;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use stdClass;
use totara_program\entity\program_extension;
use totara_program\program;
use totara_program\ProgramException;
use totara_certification\totara_notification\resolver\extension_requested as certification_extension_request_resolver;
use totara_program\totara_notification\resolver\extension_requested as extension_request_resolver;
use totara_notification\external_helper;


/**
 * Graphql resolver to request an extension.
 */
class request_extension extends mutation_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->dirroot . '/totara/program/lib.php');

        $input = $args['input'];
        $program = new program($input['id']);

        $program->check_advanced_feature();

        $current_user = user::logged_in();
        if (!$program->can_request_extension($current_user->id)) {
            throw new ProgramException(get_string('error:processingextrequest', 'totara_program'));
        }

        $extdatetime = $input['extdatetime'];
        $prog_completion = $program->get_completion_data($current_user->id);
        $duedate = empty($prog_completion->timedue) ? 0 : $prog_completion->timedue;

        if ($extdatetime < $duedate) {
            return [
                'success' => false,
                'message' => get_string('extensionearlierthanduedate', 'totara_program')
            ];
        }

        if ($extdatetime < time()) {
            return [
                'success' => false,
                'message' => get_string('extensionbeforenow', 'totara_program')
            ];
        }

        if (program_extension::repository()
            ->where('userid', $current_user->id)
            ->where('programid', $program->id)
            ->where('status', 0)
            ->exists()) {
            throw new ProgramException('Can not create the unapproved request extension repeatedly.');
        }

        if ($extension_id = self::create_extension($program->id, $current_user->id, $extdatetime, $input['extreason'])) {
            self::fire_extension_notification($program, $extension_id, $current_user->id);

            return [
                'success' => true,
                'message' => get_string('pendingextension', 'totara_program')
            ];
        }

        return [
            'success' => false,
            'message' => get_string('extensionrequestfailed', 'totara_program')
        ];
    }

    /**
     * @param int $program_id
     * @param int $user_id
     * @param int $extdate
     * @param string $extreason
     * @return bool|int
     */
    protected static function create_extension(int $program_id, int $user_id, int $extdate, string $extreason) {
        $extension = new stdClass;
        $extension->programid = $program_id;
        $extension->userid = $user_id;
        $extension->extensiondate = $extdate;
        $extension->extensionreason = $extreason;
        $extension->status = 0;

        return builder::get_db()->insert_record('prog_extension', $extension);
    }

    /**
     * @param program $program
     * @param int $extension_id
     * @param int $user_id
     * @return void
     */
    protected static function fire_extension_notification(program $program, int $extension_id, int $user_id): void {
        $resolver = $program->is_certif() ? new certification_extension_request_resolver(
            [
                'program_id' => $program->id,
                'subject_user_id' => $user_id,
                'extension_request_id' => $extension_id
            ]
        ) : new extension_request_resolver(
            [
                'program_id' => $program->id,
                'subject_user_id' => $user_id,
                'extension_request_id' => $extension_id
            ]
        );
        external_helper::create_notifiable_event_queue($resolver);
    }

    /**
     * @return array|middleware[]
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
        ];
    }
}