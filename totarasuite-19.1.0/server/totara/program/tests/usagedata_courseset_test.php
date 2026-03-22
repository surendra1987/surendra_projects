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

use core_phpunit\testcase;
use totara_program\content\program_content;
use totara_program\content\course_set;

class totara_program_usagedata_courseset_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');
        $program1 = $generator_program->create_program();
        $program2 = $generator_program->create_program();
        $program3 = $generator_program->create_program();
        $program4 = $generator_program->create_program();
        $program5 = $generator_program->create_program();

        $course = $generator->create_course();
        $course1 = $generator->create_course();

        $cert1 = $generator_program->create_certification();
        $generator_program->legacy_add_coursesets_to_program($cert1, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_ALL,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'certifpath' => CERTIFPATH_RECERT,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($program1, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_ALL,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'courses' => [
                    $course,
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($program2, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'courses' => [
                    $course
                ]
            ]
        ]);

        $generator_program->legacy_add_coursesets_to_program($program3, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        /** @var \totara_hierarchy\testing\generator $hierarchygenerator */
        $hierarchygenerator = $generator->get_plugin_generator('totara_hierarchy');
        $competency_framework = $hierarchygenerator->create_comp_frame([]);
        $competency = $hierarchygenerator->create_comp(['frameworkid' => $competency_framework->id]);
        $hierarchygenerator->assign_linked_course_to_competency($competency, $course);

        $generator_program->legacy_add_coursesets_to_program($program4, [
            [
                'type' => program_content::CONTENTTYPE_COMPETENCY,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_ALL,
                'competency' => $competency
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'courses' => [
                    $course1,
                    $course
                ]
            ],
        ]);

        $prog_content = new program_content($program5->id);
        $prog_content->add_set(program_content::CONTENTTYPE_RECURRING);
        $coursesets = $prog_content->get_course_sets();
        $coursesets[0]->course = $course1;
        $prog_content->save_content();

        $results = (new \totara_program\usagedata\courseset())->export();
        $this->assertEquals(10, $results['count_coursesets']);
        $this->assertEquals(5, $results['with_one_course']);
        $this->assertEquals(4, $results['with_multiple_courses']);
        $this->assertEquals(2, $results['progs_with_one_courseset']);
        $this->assertEquals(1, $results['legacy_recurring']);
        $this->assertEquals(1, $results['legacy_competency']);
    }
}