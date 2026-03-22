<?php
/**
 * This file is part of Totara Core
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package core
 */

use core\check\table;

/**
 * Example unit tests for check API
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_check_table_test extends \core_phpunit\testcase {

    /**
     * A simple example showing how a check and result object works
     *
     * Conceptually a check is analgous to a unit test except at runtime
     * instead of build time so many checks in real life such as testing
     * an API is connecting aren't viable to unit test.
     */
    public function test_table() {
        global $OUTPUT;

        $security_checks = [
            \core\check\security\displayerrors::class,
            \core\check\security\unsecuredataroot::class,
            \core\check\security\publicpaths::class,
            \core\check\security\configrw::class,
            \core\check\security\preventexecpath::class,
            \core\check\security\nodemodules::class,
            \core\check\security\embed::class,
            \core\check\security\openprofiles::class,
            \core\check\security\crawlers::class,
            \core\check\security\passwordpolicy::class,
            \core\check\security\emailchangeconfirmation::class,
            \core\check\security\webcron::class,
            \core\check\security\usernameenumeration::class,
            \core\check\security\persistentlogin::class,
            \mod_scorm\check\security\sessionkeepalive::class,
            \core\check\security\logincsrf::class,
            \repository_url\check\security\enabled::class,
            \core\check\security\externalentityloading::class,
            \core\check\security\disableconsistentcleaning::class,
            \core\check\security\devgraphql::class,
            \core\check\security\oauth2verify::class,
            \core\check\security\https::class,
            \core\check\security\cookiehttponly::class,
            \core\check\security\cookiesecure::class,
            \core\check\security\riskadmin::class,
            \core\check\security\riskbackup::class,
            \core\check\security\defaultuserrole::class,
            \core\check\security\guestrole::class,
            \core\check\security\frontpagerole::class,
            \core\check\security\noauth::class,
            \core\check\security\riskallowxss::class,
            \core\check\security\resourceallowxss::class,
        ];
        $performance_checks = [
            \core\check\performance\designermode::class,
            \core\check\performance\cachejs::class,
            \core\check\performance\debugging::class,
            \core\check\performance\backups::class,
            \core\check\performance\stats::class,
            // \core\check\performance\dbschema::class,
        ];

        $status_checks = [
            \core\check\status\environment::class,
            \core\check\status\upgradecheck::class,
            \core\check\status\antivirus::class,
        ];

        $table = new table('security', '/');
        foreach ($security_checks as $check_class) {
            foreach ($table->checks as $check) {
                if ($check instanceof $check_class) {
                    continue 2;
                }
            }
            self::fail('Missing security core check class, expected ' . $check_class);
        }

        $table = new table('performance', '/');
        foreach ($performance_checks as $check_class) {
            foreach ($table->checks as $check) {
                if ($check instanceof $check_class) {
                    continue 2;
                }
            }
            self::fail('Missing performance core check class, expected ' . $check_class);
        }

        $table = new table('status', '/');
        foreach ($status_checks as $check_class) {
            foreach ($table->checks as $check) {
                if ($check instanceof $check_class) {
                    continue 2;
                }
            }
            self::fail('Missing status core check class, expected ' . $check_class);
        }
        $html = $table->render($OUTPUT);

        self::assertStringContainsString('Environment', $html);
        self::assertStringContainsString('Upgrade', $html);
        self::assertStringContainsString('Antivirus', $html);
    }
}

