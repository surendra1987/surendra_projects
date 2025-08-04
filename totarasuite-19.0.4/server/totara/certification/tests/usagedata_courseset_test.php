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
 * @package totara_certification
 */

use core_phpunit\testcase;
use totara_program\content\program_content;
use totara_program\content\course_set;

class totara_certification_usagedata_courseset_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');
        $cert1 = $generator_program->create_certification();
        $cert2 = $generator_program->create_certification();
        $cert3 = $generator_program->create_certification();
        $cert4 = $generator_program->create_certification();

        $course = $generator->create_course();
        $course1 = $generator->create_course();

        $p1 = $generator_program->create_program();
        $p2 = $generator_program->create_program();

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

        $generator_program->legacy_add_coursesets_to_program($cert2, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course,
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_OPTIONAL,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course,
                    $course1
                ]
            ],
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($cert3, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($cert4, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => []
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($p1, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        $generator_program->legacy_add_coursesets_to_program($p2, [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_SOME,
                'courses' => [
                    $course,
                ]
            ],
        ]);

        $results = (new \totara_certification\usagedata\courseset())->export();
        $this->assertEquals(8, $results['count_coursesets']);
        $this->assertEquals(4, $results['with_one_course']);
        $this->assertEquals(3, $results['with_multiple_courses']);
        $this->assertEquals(2, $results['paths_with_one_courseset']);
    }
}