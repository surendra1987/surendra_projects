<?php
/*
 * This file is part of Totara TXP
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package core
 */

namespace core\webapi\resolver\type;

use coding_exception;
use context_system;
use core\model\enrol as enrol_model;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use core\formatter\enrol as enrol_formatter;
use moodle_exception;

/**
 * Enrol type resolver
 */
class enrol extends type_resolver {

    /**
     * @param string $field
     * @param $instance
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     * @throws \dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function resolve(string $field, $instance, array $args, execution_context $ec) {
        /** @var enrol_model $instance */
        if (!$instance instanceof enrol_model) {
            throw new coding_exception('Invalid data handed to enrol type resolver');
        }

        if (empty($instance->courseid)) {
            throw new moodle_exception('Course id is empty.');
        }

        $course_interactor = \container_course\interactor\course_interactor::from_course_id($instance->courseid);
        if (!$course_interactor->can_view_enrol()) {
            return null;
        }

        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : context_system::instance();
        $formatter = new enrol_formatter($instance, $context);
        return $formatter->format($field, $args['format'] ?? null);
    }
}
