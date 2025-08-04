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

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\result;
use core\check\check;

/**
 * Checks whether a DOM object will load contents of an external file by default when it loads XML.
 */
class externalentityloading extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_xxe_risk_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $CFG;
        require_once($CFG->dirroot . '/totara/core/environmentlib.php');

        $dom = new \DOMDocument();
        $dom->load($CFG->dirroot . "/totara/core/tests/fixtures/extentities.xml");
        if (totara_core_xml_external_entities_check_searchdom($dom, 'filetext')) {
            // This environment is vulnerable.
            $status = result::CRITICAL;
            $summary = get_string('check_xxe_risk_critical', 'admin');
        } else if (LIBXML_VERSION < 20900) {
            // Ancient libxml library, they should really have at least 2.9.0 now that we require PHP 7.3
            $status = result::CRITICAL;
            $summary = get_string('check_xxe_risk_critical', 'admin');
        } else {
            $status = result::OK;
            $summary = get_string('check_xxe_risk_ok', 'admin');
        }
        $details = get_string('check_xxe_risk_details', 'admin');

        return new result($status, $summary, $details);
    }
}

