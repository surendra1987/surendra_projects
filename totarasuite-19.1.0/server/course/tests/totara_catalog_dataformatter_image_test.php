<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package totara_catalog
 */


use core_course\totara_catalog\course\dataformatter\image;
use totara_catalog\dataformatter\dataformatter_test_base;
use totara_catalog\dataformatter\formatter;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot . "/totara/catalog/tests/dataformatter_test_base.php");

/**
 * @group totara_catalog
 */
class core_course_totara_catalog_dataformatter_image_test extends \totara_catalog\dataformatter\dataformatter_test_base {

    public function test_image() {
        global $CFG;

        $context = context_system::instance();

        $df = new image('courseidfield', 'altfield', 'cacherevfield');
        $this->assertCount(3, $df->get_required_fields());
        $this->assertSame('courseidfield', $df->get_required_fields()['courseid']);
        $this->assertSame('altfield', $df->get_required_fields()['alt']);
        $this->assertSame('cacherevfield', $df->get_required_fields()['cacherev']);

        $this->assertSame([formatter::TYPE_PLACEHOLDER_IMAGE], $df->get_suitable_types());

        $course = $this->getDataGenerator()->create_course();
        $test_params = [
            'courseid' => $course->id,
            'alt' => 'test_alt_text',
        ];
        $result = $df->get_formatted_value($test_params, $context);
        $this->assertInstanceOf(stdClass::class, $result);
        // Check that we get a url back that includes default icon in its path.
        $this->assertStringContainsString($CFG->wwwroot, $result->url);
        $this->assertStringContainsString('/course_defaultimage', $result->url);
        $this->assertSame('test_alt_text', $result->alt);

        $this->assert_exceptions($df, $test_params);
    }
}
