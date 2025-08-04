<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_message
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @return void
 */
function totara_message_upgrade_message_type_for_program(): void {
    global $DB;

    $sql = "SELECT mm.* FROM {message_metadata} AS mm 
        INNER JOIN {notifications} AS n ON mm.notificationid = n.id
        WHERE n.component = 'totara_message' AND mm.icon = 'default' AND msgtype = 0";

    $transaction = $DB->start_delegated_transaction();
    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $record) {
        if (isset($record->onaccept) && isset($record->onreject)) {
            $onaccept = unserialize($record->onaccept);
            $onreject = unserialize($record->onreject);
            if ((isset($onaccept->action) && $onaccept->action === 'prog_extension') &&
                (isset($onreject->action) && $onreject->action === 'prog_extension')
            ) {
                $update_recored = new stdClass();
                $update_recored->id = $record->id;
                $update_recored->msgtype = 11;
                $update_recored->icon = 'program-regular';
                $DB->update_record('message_metadata', $update_recored);
            }
        }
    }

    $rs->close();
    $transaction->allow_commit();
}
