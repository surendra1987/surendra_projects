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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

global $CFG, $PAGE, $OUTPUT;

use tool_usagedata\config;
use tool_usagedata\exporter;

require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';

admin_externalpage_setup('usagedata');

$exporter = new exporter();
$data = [
    'opt_out_enabled' => config::is_opt_out_enabled(),
    'areas' => $exporter->purpose()
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tool_usagedata/page', $data);
echo $OUTPUT->footer();