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

class totara_certification_usagedata_recertification_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');

        $generator_program->create_certification(['recertifydatetype' => CERTIFRECERT_COMPLETION]);
        $generator_program->create_certification(['recertifydatetype' => CERTIFRECERT_COMPLETION]);
        $generator_program->create_certification(['recertifydatetype' => CERTIFRECERT_EXPIRY]);
        $generator_program->create_certification(['recertifydatetype' => CERTIFRECERT_EXPIRY]);
        $generator_program->create_certification(['recertifydatetype' => CERTIFRECERT_EXPIRY]);
        $generator_program->create_program();
        $generator_program->create_program();

        $data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '50 day',
            'cert_minimumactiveperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_FIXED,
        );
        $cert1 = $generator_program->legacy_create_certification($data);

        $data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_minimumactiveperiod' => '365 day',
            'cert_recertifydatetype' => CERTIFRECERT_FIXED,
        );
        $cert2 = $generator_program->legacy_create_certification($data);
        $cert3 = $generator_program->legacy_create_certification($data);

        $data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_minimumactiveperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_FIXED,
        );

        $cert4 = $generator_program->legacy_create_certification($data);
        $cert5 = $generator_program->legacy_create_certification($data);
        $cert6 = $generator_program->legacy_create_certification($data);

        $results = (new \totara_certification\usagedata\recertification())->export();
        $this->assertEquals(3, $results['certification_expiry_date']);
        $this->assertEquals(2, $results['certification_completion_date']);
        $this->assertEquals(6, $results['fixed_expiry_date']);

        $minimum_active_period_ratio = $results['minimum_active_period_ratio'];
        $this->assertEquals(3, $minimum_active_period_ratio['0.0']);
        $this->assertEquals(2, $minimum_active_period_ratio['1.0']);
        $this->assertEquals(1, $minimum_active_period_ratio['0.0 - 0.2']);
        $this->assertEquals(0, $minimum_active_period_ratio['0.2 - 0.4']);
        $this->assertEquals(0, $minimum_active_period_ratio['0.4 - 0.6']);
        $this->assertEquals(0, $minimum_active_period_ratio['0.6 - 0.8']);
        $this->assertEquals(0, $minimum_active_period_ratio['0.8 - 1.0']);

        $minimum_recertify_ratio = $results['minimum_recertify_ratio'];
        $this->assertEquals(11, $minimum_recertify_ratio['0.0 - 0.2']);
        $this->assertEquals(0, $minimum_recertify_ratio['0.2 - 0.4']);
        $this->assertEquals(0, $minimum_recertify_ratio['0.4 - 0.6']);
        $this->assertEquals(0, $minimum_recertify_ratio['0.6 - 0.8']);
        $this->assertEquals(0, $minimum_recertify_ratio['0.8 - 1.0']);
    }
}