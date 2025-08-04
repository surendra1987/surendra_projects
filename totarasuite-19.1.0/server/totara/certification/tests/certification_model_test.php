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
use totara_certification\model\certification;
use totara_program\testing\generator;
use totara_certification\entity\certification as entity;

class totara_certification_certification_model_test extends testcase {

    /**
     * @return void
     */
    public function test_clone_certification(): void {
        /** @var generator $program_generator */
        $program_generator = $this->getDataGenerator()->get_plugin_generator('totara_program');
        $origin_certif = $program_generator->create_certification(['cert_windowperiod' => '6 month']);
        $program = $program_generator->create_program();

        self::assertFalse($program->is_certif());
        self::assertEquals(1, entity::repository()->count());
        $origin_certif = certification::load_by_id($origin_certif->certifid);
        $cloned_certif = $origin_certif->clone($program);
        self::assertEquals(2, entity::repository()->count());
        $program = new totara_program\program($program->id);
        self::assertTrue($program->is_certif());
        self::assertEquals($cloned_certif->windowperiod, $origin_certif->windowperiod);
        self::assertEquals($cloned_certif->learningcomptype, $origin_certif->learningcomptype);
        self::assertEquals($cloned_certif->activeperiod, $origin_certif->activeperiod);
        self::assertEquals($cloned_certif->minimumactiveperiod, $origin_certif->minimumactiveperiod);
        self::assertEquals($cloned_certif->recertifydatetype, $origin_certif->recertifydatetype);
    }
}