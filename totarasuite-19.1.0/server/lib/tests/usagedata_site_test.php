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

use core\usagedata\site;
use core_phpunit\testcase;
use tool_usagedata\config;

class core_usagedata_site_test extends testcase {

  public function test_site_export():void {
    global $CFG, $DB;

    $CFG->registrationcode = 'registrationcode';
    $CFG->forceflavour = 'forceflavour';
    $CFG->sitetype = 'sitetype';
    $CFG->lang = 'language';

    $site = new site();
    $export_values = $site->export();


    $this->assertNotEmpty($export_values);
    $this->assertTrue(is_int($export_values['time']));
    $this->assertEquals(date_default_timezone_get(), $export_values['timezone']);
    $this->assertEquals($CFG->siteidentifier, $export_values['siteidentifier']);
    $this->assertEquals(config::get_site_identifier(), $export_values['usagedata_siteid']);
    $this->assertEquals('registrationcode', $export_values['registrationcode']);
    $this->assertEquals($CFG->wwwroot, $export_values['wwwroot']);
    $this->assertEquals(get_config('totara_flavour', 'currentflavour'), $export_values['currentflavour']);
    $this->assertEquals('forceflavour', $export_values['forceflavour']);
    $this->assertEquals('sitetype', $export_values['sitetype']);
    $this->assertEquals($DB->get_field_sql('SELECT MAX(lastruntime) FROM "ttr_task_scheduled"'), $export_values['lastcron']);
    $this->assertEquals($CFG->debug, $export_values['debug']);
    $this->assertEquals($CFG->debugdisplay, $export_values['debugdisplay']);
    $this->assertEquals('language', $export_values['language']);
    $this->assertEquals($CFG->maintenance_enabled, $export_values['maintenance_enabled']);
  }
}

