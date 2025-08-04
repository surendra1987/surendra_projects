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

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/pdflib.php');

// This is a simple test page to render the barcodes we support via the pdf library.

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

global $PAGE, $OUTPUT;

$PAGE->set_url('/lib/tests/other/barcodetestpage.php');
$PAGE->set_context($context);
$PAGE->set_title('PDF library barcodes test');
$PAGE->set_heading('PDF library barcodes test');

echo $OUTPUT->header();

$payloads = [
    'https://example.com' => 10,
    'This is my data' => 15,
    'Another' => 3,
    'A' => 1,
    $CFG->wwwroot => 7
];

foreach ($payloads as $data => $size) {
    echo $OUTPUT->heading($data, 2);
    $qr = pdf::qr($data, $size);
    echo sprintf('<div><img src="data:image/png;base64, %s" alt="%s"></div>', $qr, $data);
}

echo $OUTPUT->footer();
