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
 * @package core
 */

namespace core\usagedata;

use tool_usagedata\export;

class version implements export {

    private string $version;
    private int $maturity;
    private string $release;
    private string $branch;

    private string $totara_version;
    private string $totara_build;
    private string $totara_release;

    public function __construct() {
        global $CFG;

        // set defaults in case version.php is not available
        $version  = '';
        $maturity = MATURITY_ALPHA;
        $branch = '';
        $release = '';
        $TOTARA = new \stdClass();

        // if file exists - version, maturity, branch, release, TOTARA will be overridden
        include $CFG->dirroot . '/version.php';

        $this->version = $version;
        $this->maturity = $maturity;
        $this->release = $release;
        $this->branch = $branch;

        $this->totara_version = !empty($TOTARA->version) ? $TOTARA->version : '';
        $this->totara_build = !empty($TOTARA->build) ? $TOTARA->build : '';
        $this->totara_release = !empty($TOTARA->release) ? $TOTARA->release : '';
    }

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('version_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    public function export(): array {
        return [
            'version' => $this->version ?? self::NOTSET,
            'maturity' => $this->maturity ?? MATURITY_ALPHA,
            'totaraversion' => $this->totara_version ?? self::NOTSET,
            'totarabuild' => $this->totara_build ?? self::NOTSET,
            'totararelease' => $this->totara_release ?? self::NOTSET,
            'moodlerelease' => $this->release ?? self::NOTSET,
            'moodlebranch' => $this->branch ?? self::NOTSET,
        ];
    }
}
