<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core
 * @category usagedata
 */

use core\usagedata\version;
use core_phpunit\testcase;
use tool_usagedata\export;

class core_usagedata_version_test extends testcase {

    public function test_version_export():void {
        $version = new version();

        $version_reflection_object = new reflectionObject($version);

        // override the version data
        $version_prop = $version_reflection_object->getProperty('version');
        $version_prop->setAccessible(true);
        $version_prop->setValue($version, 'testVersion');

        $maturity_prop = $version_reflection_object->getProperty('maturity');
        $maturity_prop->setAccessible(true);
        $maturity_prop->setValue($version, 100);

        $branch_prop = $version_reflection_object->getProperty('branch');
        $branch_prop->setAccessible(true);
        $branch_prop->setValue($version, 'testBranch');

        $release_prop = $version_reflection_object->getProperty('release');
        $release_prop->setAccessible(true);
        $release_prop->setValue($version, '3');

        $totara_version_prop = $version_reflection_object->getProperty('totara_version');
        $totara_version_prop->setAccessible(true);
        $totara_version_prop->setValue($version, '13');

        $totara_build_prop = $version_reflection_object->getProperty('totara_build');
        $totara_build_prop->setAccessible(true);
        $totara_build_prop->setValue($version, '1');

        $totara_release_prop = $version_reflection_object->getProperty('totara_release');
        $totara_release_prop->setAccessible(true);
        $totara_release_prop->setValue($version, '46');

        $export_values = $version->export();
        // The result should not be empty
        $this->assertNotEmpty($export_values);

        $this->assertSame('testVersion', $export_values['version']);
        $this->assertSame('testBranch', $export_values['moodlebranch']);
        $this->assertSame('3', $export_values['moodlerelease']);
        $this->assertSame(100, $export_values['maturity']);
        $this->assertSame('13', $export_values['totaraversion']);
        $this->assertSame('1', $export_values['totarabuild']);
        $this->assertSame('46', $export_values['totararelease']);
    }
}
