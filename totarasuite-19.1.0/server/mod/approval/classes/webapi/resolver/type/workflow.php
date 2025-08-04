<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\webapi\resolver\type;

use core\entity\user;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_approval\entity\application\application;
use mod_approval\entity\assignment\assignment;
use mod_approval\exception\helper\validation;
use mod_approval\formatter\workflow\workflow as workflow_formatter;
use mod_approval\model\workflow\workflow as workflow_model;

/**
 * Workflow type resolver
 */
class workflow extends type_resolver {
    /**
     * @param string $field
     * @param workflow_model|object $workflow
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $workflow, array $args, execution_context $ec) {
        validation::instance_of($workflow, workflow_model::class, 'Expected workflow model');

        if ($field === 'interactor') {
            return $workflow->get_interactor(user::logged_in()->id);
        }

        if ($field === 'applications_count') {
            return application::repository()
                ->as('application')
                ->join(['approval_workflow_version', 'workflow_version'], 'workflow_version.id', '=', 'application.workflow_version_id')
                ->where('workflow_version.workflow_id', '=', $workflow->id)
                ->count();
        }

        if ($field === 'assignments_count') {
            return assignment::repository()
                ->as('assignment')
                ->join(['approval_workflow', 'workflow'], 'workflow.course_id', '=', 'assignment.course')
                ->where('workflow.id', '=', $workflow->id)
                ->count();
        }

        $format = $args['format'] ?? format::FORMAT_PLAIN;
        $formatter = new workflow_formatter($workflow, $ec->get_relevant_context());

        return $formatter->format($field, $format);
    }
}