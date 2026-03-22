<?php
/*
 * This file is part of Totara Learn
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
 * @author Aaron Machin <aaron.machin@totaralms.com>
 * @package totara_core
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/componentlib.class.php');

use core_phpunit\testcase;

class totara_core_lang_installer_test extends testcase {

    /**
     * Testcase to check lang_pack_url() and, consequently, get_language_version()
     */
    public function test_lang_pack_url() {
        global $CFG;

        // Discover version maturity.
        $maturity = null;
        $version_file = $CFG->dirroot . '/version.php';
        include($version_file);

        $version = totara_major_version();

        if ($version != 'evergreen' && $maturity === MATURITY_ALPHA) {
            $version = (int) $version;
            $version--;
        }

        if (is_numeric($version)) {
            $version = 'T'.$version;
        }

        $installer = new lang_installer();
        $this->assertSame(DOWNLOAD_BASE . '/' . $version . '/', $installer->lang_pack_url());

        $this->assertSame(DOWNLOAD_BASE . '/' . $version . '/en.tgz', $installer->lang_pack_url('en'));
    }
}


