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

class environment implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('environment_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    public function export(): array {
        global $DB, $CFG;

        $dbinfo = $DB->get_server_info();
        return [
            // Operating environment information.
            'php_version' => phpversion() ?? self::NOTSET,
            // Operating system name. eg. FreeBSD, Darwin, etc
            'osname' => php_uname('s') ?? self::NOTSET,
            // Operating system name. eg. FreeBSD, Darwin, etc
            'osversion' => php_uname('r') ?? self::NOTSET,

            'dbfamily' => $CFG->dbfamily ?? self::NOTSET,
            'dbtype' => $CFG->dbtype ?? self::NOTSET,
            'dbversion' => $dbinfo['version'] ?? self::NOTSET,
            'dbdescription' => $dbinfo['description'] ?? self::NOTSET,
            'dbcollation' => !empty($CFG->dboptions['dbcollation']) ? $CFG->dboptions['dbcollation'] : self::NOTSET,
            'dbengine' => !empty($CFG->dboptions['dbengine']) ? $CFG->dboptions['dbengine'] : self::NOTSET,
            'dbclient_ssl' => !empty($CFG->dboptions['client_ssl'])
                || !empty($CFG->dboptions['sslmode'])
                || !empty($CFG->dboptions['encrypt']),
            'dbftslanguage' => !empty($CFG->dboptions['ftslanguage']) ? $CFG->dboptions['ftslanguage'] : self::NOTSET,
            'dbftsngram' => !empty($CFG->dboptions['ftsngram']),
            'dbreadonlyreportbuilderdatabase' => !empty($CFG->clone_dbname),

            // System path usage
            'uses_du' => !empty($CFG->pathtodu),
            'uses_aspellpath' => !empty($CFG->aspellpath),
            'uses_pathtodot' => !empty($CFG->pathtodot),
            'uses_pathtogs' => !empty($CFG->pathtogs),
            'uses_pathtowkhtmltopdf' => !empty($CFG->pathtowkhtmltopdf),

            // HTTP
            'uses_proxy' => !empty($CFG->proxyhost),
            'uses_reverseproxyignore' => !empty($CFG->reverseproxyignore),

            // Config.php only
            'xsendfile' => !empty($CFG->xsendfile) ? $CFG->xsendfile : self::NOTSET,
            'reverseproxy' => !empty($CFG->reverseproxy),
            'sslproxy' => !empty($CFG->sslproxy),
            'lock_factory' => !empty($CFG->lock_factory) ? $CFG->lock_factory : self::NOTSET,
            'uses_altcacheconfig' => !empty($CFG->altcacheconfigpath),
        ];
    }
}
