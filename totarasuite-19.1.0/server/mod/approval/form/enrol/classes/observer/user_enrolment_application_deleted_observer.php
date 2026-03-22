<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package approvalform_enrol
 */

namespace approvalform_enrol\observer;

use approvalform_enrol\enrol;
use core\entity\user_enrolment;
use core\orm\query\builder;
use mod_approval\event\application_deleted;
use mod_approval\model\application\application;
use mod_approval\model\json_trait;

class user_enrolment_application_deleted_observer {

    use json_trait;

    public static function delete_enrolment(application_deleted $event): void {
        $data = $event->get_data();

        try {
            $application = application::load_by_id($data['objectid']);
        } catch (\Exception $e) {
            return;
        }

        // Make sure this application belongs to an approvalform_enrol form.
        if ($application->get_approvalform_plugin()::class != enrol::class) {
            return;
        }

        $enrol_application = builder::table('user_enrolments_application')
            ->select('user_enrolments_id')
            ->where('approval_application_id', '=', $application->id)
            ->get();

        // If there was no enrolment created
        if (!$enrol_application || $enrol_application->count() != 1) {
            return;
        }

        $enrolment_id = $enrol_application->first()->user_enrolments_id;

        $enrolment_type = builder::table('enrol')
            ->select('enrol')
            ->join('user_enrolments', 'id', '=', 'enrolid')
            ->where('enrol', '=', 'self')
            ->where('user_enrolments.id', '=', $enrolment_id)
            ->get();

        // Delete the enrolment if it was made via self-enrolment because it is an exceptional case
        // Other methods of enrolment are unlikely to need to delete enrolment in this way.
        if ($enrolment_type->first()->enrol != 'self') {
            return;
        }

        user_enrolment::repository()
            ->where('id', '=', $enrolment_id)
            ->delete();
    }
}