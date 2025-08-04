<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <gary.liu@totara.com>
 * @package hierarchy_goal
 */

use core_phpunit\testcase;
use hierarchy_goal\entity\company_goal;
use hierarchy_goal\entity\personal_goal;
use hierarchy_goal\formatter\company_goal as company_goal_formatter;
use hierarchy_goal\formatter\personal_goal as personal_goal_formatter;
use hierarchy_goal\personal_goal_assignment_type;

class hierarchy_goal_goal_formatter_test extends testcase {
    /**
     * @return void
     */
    public function test_company_goal_formatter_for_type_name(): void {
        /** @var \totara_hierarchy\testing\generator $hierarchy_generator */
        $hierarchy_generator = self::getDataGenerator()->get_plugin_generator('totara_hierarchy');

        $goal_type_data = [
            'idnumber' => 'test1',
            'fullname' => '<span lang="es" class="multilang">Spanish paragraph 1</span>',
        ];

        $type_id = $hierarchy_generator->create_goal_type($goal_type_data);

        $goal = new company_goal();
        $goal->id = 123;
        $goal->shortname = 'some short name';
        $goal->description = 'some desc';
        $goal->idnumber = 'some idnumber';
        $goal->fullname = 'some full name';
        $goal->typeid = $type_id;

        $formatter = new company_goal_formatter($goal, context_system::instance());
        $value = $formatter->format('type_name',  \core\format::FORMAT_HTML);
        self::assertEquals('Spanish paragraph 1', $value);
    }

    public function test_personal_goal_formatter_for_type_name(): void {
        /** @var \totara_hierarchy\testing\generator $hierarchy_generator */
        $hierarchy_generator = self::getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $personal_goal_type = $hierarchy_generator->create_personal_goal_type([
            'fullname' => '<span lang="es" class="multilang">Spanish paragraph 1</span>'
        ]);
        $goal_id = $hierarchy_generator->create_personal_goal(
            $this->getDataGenerator()->create_user()->id,
            [
                'assigntype'  => (personal_goal_assignment_type::self())->get_value(),
                'typeid'      => $personal_goal_type->id,
            ]
        )->id;

        $personal_goal = new personal_goal($goal_id);
        $formatter = new personal_goal_formatter($personal_goal, context_system::instance());
        $value = $formatter->format('type_name',  \core\format::FORMAT_HTML);
        self::assertEquals('Spanish paragraph 1', $value);
    }
}