<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();

/**
 * PDFlib testcase.
 */
class core_pdflib_test extends testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->libdir . '/pdflib.php');
    }

    /**
     * Assert we have the ability to generate a QR code.
     *
     * @return void
     * @covers pdf::qr()
     */
    public function test_qr_code(): void {
        $qr = \pdf::qr('Example Code', 5);

        // We can only check if it looks like a base64'd image, but can't
        // check the actual code as it has an element of random.
        $this->assertNotEmpty($qr);
        $this->assertStringStartsWith('iVBORw0KGgo', $qr);
    }

    /**
     * @return void
     * @covers pdf::qr()
     */
    public function test_invalid_qr_code(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('payload not provided');
        \pdf::qr('', 1);
    }
}
