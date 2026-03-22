<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Check the presence of public paths via curl.
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Check the public access of various paths.
 *
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publicpaths extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_publicpaths_name', 'admin');
    }

    /**
     * Returns a list of test urls and metadata.
     * @return array
     */
    public function get_pathsets() {
        global $CFG;

        // The intention here is that each pattern is a simple regex such that
        // in future perhaps the various webserver config could be generated as more
        // pattens are added to these checks.
        return [
            [
                'pattern'   => '/vendor/',
                '404'       => [
                    'vendor/',
                    'vendor/bin/behat',
                ],
                'details'   => get_string('check_vendordir_details', 'admin', ['path' => $CFG->dirroot.'/vendor']),
                'summary'   => get_string('check_vendordir_info', 'admin'),
            ],
            [
                'pattern'   => '/node_modules/',
                '404'       => [
                    'node_modules/',
                    'node_modules/cli/cli.js',
                ],
                'summary'   => get_string('check_nodemodules_info', 'admin'),
                'details'   => get_string('check_nodemodules_details', 'admin',
                        ['path' => $CFG->dirroot . '/node_modules']),
            ],
            [
                'pattern'   => '^\..*',
                '404'       => [
                    '.git/',
                    '.git/HEAD',
                    '.github/FUNDING.yml',
                    '.stylelintrc',
                ],
            ],
            [
                'pattern'   => 'composer.json',
                '404'       => [
                    'composer.json',
                ],
            ],
            [
                'pattern'   => '.lock',
                '404'       => [
                    'composer.lock',
                ],
            ],
            [
                'pattern'   => 'environment.xml',
                '404'       => [
                    'admin/environment.xml',
                ],
            ],
            [
                'pattern'   => '',
                '404'       => [
                    'doesnotexist', // Just to make sure that real 404s are still 404s.
                ],
                'summary'   => '',
            ],
            [
                'pattern'   => '',
                '404'       => [
                    'lib/classes/',
                ],
                'summary'   => get_string('check_dirindex_info', 'admin'),
            ],
            [
                'pattern'   => 'db/install.xml',
                '404'       => [
                    'lib/db/install.xml',
                    'mod/assign/db/install.xml',
                ],
            ],
            [
                'pattern'   => 'readme.txt',
                '404'       => [
                    'lib/scssphp/readme_moodle.txt',
                    'mod/resource/readme.txt',
                ],
            ],
            [
                'pattern'   => 'README',
                '404'       => [
                    'mod/README.txt',
                    'mod/book/README.md',
                    'mod/chat/README.txt',
                ],
            ],
            [
                'pattern'   => '/upgrade.txt',
                '404'       => [
                    'auth/manual/upgrade.txt',
                    'lib/upgrade.txt',
                ],
            ],
            [
                'pattern'   => 'phpunit.xml',
                '404'       => ['phpunit.xml.dist'],
            ],
            [
                'pattern'   => '/fixtures/',
                '404'       => [
                    'privacy/tests/fixtures/logo.png',
                    'enrol/lti/tests/fixtures/input.xml',
                ],
            ],
            [
                'pattern'   => '/behat/',
                '404'       => ['blog/tests/behat/delete.feature'],
            ],
        ];
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        /** @var \core_renderer $OUTPUT */
        global $CFG, $OUTPUT;

        $status = result::OK;
        $details = '';
        $summary = get_string('check_publicpaths_ok', 'admin');
        $errors = [];

        $paths = $this->get_pathsets();

        $table = new \html_table();
        $table->align = ['center', 'right', 'left'];
        $table->size = ['1%', '1%', '1%', '1%', '1%', '99%'];
        $table->head = [
            get_string('status'),
            get_string('checkexpected'),
            get_string('checkactual'),
            get_string('url'),
            get_string('category'),
            get_string('details'),
        ];
        $table->attributes['class'] = 'flexible generaltable generalbox table-sm';
        $table->data = [];

        // Used to track duplicated errors.
        $curl = new \curl();
        $requests = [];

        // Build up a list of all url so we can load them in parallel.
        foreach ($paths as $path) {
            foreach (['200', '404'] as $expected) {
                if (!isset($path[$expected])) {
                    continue;
                }
                foreach ($path[$expected] as $test) {
                    $requests[] = [
                        'nobody'    => true,
                        'header'    => 1,
                        'url'       => $CFG->wwwroot . '/' . $test,
                        'returntransfer' => true,
                    ];
                }
            }
        }

        $headers = $curl->download($requests);

        $has_errors = 0;
        $has_unable = 0;
        foreach ($paths as $path) {
            foreach (['200', '404'] as $expected) {
                if (!isset($path[$expected])) {
                    continue;
                }
                foreach ($path[$expected] as $test) {
                    $rowdetail = '';

                    $url = $CFG->wwwroot . '/' . $test;

                    // Parse the HTTP header to get the 200 / 404 code.
                    $actual = '';
                    $header = array_shift($headers);
                    if (!empty($header)) {
                        list($line) = explode("\n", $header);
                        $http = explode(" ", trim($line));
                        if (count($http) > 1) {
                            list($unused, $actual) = $http;
                        }
                    }

                    $rowsummary = $path['summary'] ?? null;
                    if (empty($actual)) {
                        if (empty($rowsummary)) {
                            $rowsummary = get_string('check_publicpaths_generic_unable', 'admin', $path['pattern']);
                        }

                        $rowdetail = isset($path['details']) ? $path['details'] : $rowsummary;
                        $result = new result(result::WARNING, '', '');
                        if ($status == result::OK) {
                            $status = result::WARNING;
                        }
                        $actual = get_string('check_publicpaths_unknown', 'admin');
                        $has_unable++;
                    } else if ($actual != $expected) {
                        if (empty($rowsummary)) {
                            $rowsummary = get_string('check_publicpaths_generic', 'admin', $path['pattern']);
                        }

                        // Special case where a 404 is ideal but a 403 is ok too.
                        if ($actual == 403) {
                            $result = new result(result::INFO, '', '');
                            $rowsummary .= get_string('check_publicpaths_403', 'admin');
                        } else {
                            $result = new result(result::ERROR, '', '');
                            $status = result::ERROR;
                            $has_errors++;
                        }

                        $rowdetail = isset($path['details']) ? $path['details'] : $rowsummary;

                        if (empty($errors[$path['pattern']])) {
                            $errors[$path['pattern']] = 1;
                        }

                    } else {
                        $result = new result(result::OK, '', '');
                    }

                    $table->data[] = [
                        $OUTPUT->check_result($result),
                        $expected,
                        $actual,
                        $OUTPUT->action_link($url, $test, null, ['target' => '_blank']),
                        "<pre>{$path['pattern']}</pre>",
                        $rowdetail,
                    ];
                }
            }
        }
        if ($has_errors) {
            $summary = get_string('check_publicpaths_warning', 'admin', $has_errors);
        } elseif ($has_unable) {
            $summary = get_string('check_publicpaths_unable', 'admin', $has_unable);
        }
        $details .= $OUTPUT->render($table);

        return new result($status, $summary, $details);
    }

    /**
     * Link to the dev docs for more info.
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url(\get_docs_url('installing-totara#web-server')),
            get_string('moodledocs'));
    }

}

