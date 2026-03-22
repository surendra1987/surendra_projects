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
 * Check API manager
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\check;

defined('MOODLE_INTERNAL') || die();

/**
 * Check API manager
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * Return all checks of a given type
     *
     * @param string $classification of checks to fetch
     * @return check[] of check objects
     */
    public static function get_checks(string $classification): array {
        if (!check::is_valid_classification($classification)) {
            throw new \moodle_exception('Invalid check type', '', '', null, $classification);
        }
        $checks = self::get_namespaced_checks_for_classification($classification);

        // Any plugin can add checks to this report by implementing a callback
        // It can be via one of three functions:
        // * <component>_security_checks()
        // * <component>_performance_checks()
        // * <component>_status_checks()
        // The use of this pattern is discouraged. Use namespaces instead. Better organisation, faster discovery.
        $morechecks = get_plugins_with_function($classification . '_checks', 'lib.php');
        $knownclasses = null;
        foreach ($morechecks as $plugintype => $plugins) {
            foreach ($plugins as $plugin => $pluginfunction) {
                if ($knownclasses === null) {
                    // First time here, we need to establish this array so that we can quickly check we don't already have a
                    // classes via the namespace loading.
                    $knownclasses = [];
                    foreach ($checks as $check) {
                        $knownclasses[] = get_class($check);
                    }
                }
                $result = $pluginfunction();
                foreach ($result as $check) {
                    if (!($check instanceof check)) {
                        \debugging('Invalid check class recieved: ' . get_class($check), DEBUG_DEVELOPER);
                        continue;
                    }
                    if (in_array(get_class($check), $knownclasses)) {
                        // This class was loaded via namespaces already.
                        continue;
                    }
                    $check->set_component($plugintype . '_' . $plugin);
                    $checks[] = $check;
                }
            }
        }
        return $checks;
    }

    /**
     * Gets the checks in the namespace for the given classification.
     * @param string $classification
     * @return check[]
     */
    private static function get_namespaced_checks_for_classification(string $classification): array {
        /** @var check[] $classes */
        $classes = \core_component::get_namespace_classes('check\\' . $classification, check::class);

        $checks = [];
        foreach ($classes as $class) {
            if ($class::include_in_classification()) {
                $checks[] = new $class();
            }
        }
        return $checks;
    }
}
