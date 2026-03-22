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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 * Some of the unit tests for the DBManagerHelper class are in the config_table_test file.
 *
 * @package    bi_intellidata
 * @copyright  2020 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

namespace bi_intellidata\helpers;

use xmldb_file;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

class DBManagerHelper {

    /**
     * Get tables list from install files.
     *
     * @return array
     */
    public static function get_install_xml_tables() {
        $tables = [];

        foreach (self::get_install_xml_files() as $plugintype => $plugins) {
            foreach ($plugins as $plugin => $filename) {
                $xmldbfile = new \xmldb_file($filename);
                if (!$xmldbfile->loadXMLStructure()) {
                    continue;
                }
                $structure = $xmldbfile->getStructure();
                $xmltables = $structure->getTables();

                foreach ($xmltables as $xmltable) {

                    // Prepare table details.
                    $table = [
                        'name' => $xmltable->getName(),
                        'plugintype' => $plugintype,
                        'plugin' => $plugin,
                    ];

                    // Prepare table keys.
                    $xmlkeys = $xmltable->getKeys();
                    if (count($xmlkeys)) {
                        foreach ($xmlkeys as $key) {
                            $table['keys'][$key->getName()] = self::extract_xml_key($key);
                        }
                    }

                    $tables[$xmltable->getName()] = $table;
                }
            }
        }

        return $tables;
    }

    /**
     * @param $key
     * @return array
     */
    private static function extract_xml_key($key) {
        return [
            'name'      => $key->getName(),
            'fields'    => $key->getFields(),
            'reftable'  => $key->getReftable() ?? '',
            'reffields' => $key->getReffields()
        ];
    }

    /**
     * Get the list of install.xml files.
     *
     * @param bool $result_as_simple_array
     * @return array
     */
    public static function get_install_xml_files(bool $result_as_simple_array = false) {
        global $CFG;

        $files = [];
        if ($result_as_simple_array) {
            $files[] = $CFG->libdir.'/db/install.xml';
        } else {
            $files['moodle']['core'] = $CFG->libdir . '/db/install.xml';
        }

        // Then, all the ones defined by core_component::get_plugin_types().
        $plugintypes = \core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $pluginbasedir) {
            if ($plugins = \core_component::get_plugin_list($plugintype)) {
                foreach ($plugins as $plugin => $plugindir) {
                    $filename = "{$plugindir}/db/install.xml";
                    if (file_exists($filename)) {
                        if ($result_as_simple_array) {
                            $files[] = $filename;
                        } else {
                            $files[$plugintype][$plugin] = $filename;
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * @param $column
     * @return mixed|string
     */
    public static function get_field_default_value($column) {
        return ($column->has_default) ? $column->default_value : '';
    }

    /**
     * Create DB index.
     *
     * @param $key
     * @return array
     */
    public static function create_index($tablename, $indexname) {
        global $DB;

        $dbman = $DB->get_manager();

        $table = new \xmldb_table($tablename);
        $index = new \xmldb_index($indexname . '_idx', XMLDB_INDEX_NOTUNIQUE, [$indexname]);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    /**
     * Delete DB index.
     *
     * @param $key
     * @return array
     */
    public static function delete_index($tablename, $indexname) {
        global $DB;

        $dbman = $DB->get_manager();

        $table = new \xmldb_table($tablename);
        $index = new \xmldb_index($indexname . '_idx', XMLDB_INDEX_NOTUNIQUE, [$indexname]);

        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
    }

    /**
     * @param string $comment
     * @return string
     */
    public static function format_comment(string $comment): string {
        // UX requirement to avoid the awkward table description default when it hasn't been filled in.
        $default_comment = 'Default comment for the table, please edit me';
        if ($comment == $default_comment) {
            $comment = '';
        }

        // UX requirement to format quotes around table names. Should be 'smart' angled quotes.
        if (stripos($comment, " '") > -1) {
            $comment = str_replace( " '", " ′", $comment);
            $comment = str_replace( "' ", "′ ", $comment);
        }

        return $comment;
    }

    /**
     * @param string $table_name
     * @param array $xmldb_files
     * @param string $field
     * @return string
     */
    public static function get_field_for_table_from_xml_info(string $table_name, array $xmldb_files, string $field = 'comment_attribute'): string {
        $table_comment_description = '';

        foreach ($xmldb_files as $xmldb_file_path) {
            $xmldb_file = new xmldb_file($xmldb_file_path);
            $loaded = $xmldb_file->loadXMLStructure();
            $xmldb_structure = $xmldb_file->getStructure();
            foreach ($xmldb_structure->getTables() as $xmldb_table) {
                if ($xmldb_table->getName() == $table_name) {
                    if ($field == 'comment_attribute') {
                        return self::format_comment($xmldb_table->getComment());
                    }
                }
            }
        }
        return $table_comment_description;
    }
}
