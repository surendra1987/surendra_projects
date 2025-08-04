<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package core_phpunit
 */

namespace core_phpunit;

use totara_core\hook\base;

/**
 * Hook redirection sink.
 */
class hook_sink {

    /** @var base[] array of hooks */
    protected $hooks = array();

    /**
     * Stop hook redirection.
     *
     * Use if you do not want hooks redirected any more.
     */
    public function close() {
        internal_util::stop_hook_redirection();
    }

    /**
     * To be called from phpunit_util only!
     *
     * @private
     * @param base $hook
     */
    public function add_hook(base $hook) {
        $this->hooks[] = $hook;
    }

    /**
     * Returns all redirected hooks.
     *
     * @return base[]
     */
    public function get_hooks() {
        return $this->hooks;
    }

    /**
     * Return hooks according to the given hook class
     *
     * @param string $classname
     * @param bool $match
     * @return base[]
     */
    public function get_hooks_by_classname(string $classname, bool $match=true) {
        return array_filter($this->hooks, function (base $hooks) use ($classname, $match){
            $flag = get_class($hooks) === $classname;
            return $match ? $flag : !$flag;
        });
    }

    /**
     * Return number of hooks redirected to this sink.
     *
     * @return int
     */
    public function count() {
        return count($this->hooks);
    }

    /**
     * Removes all previously stored hooks.
     */
    public function clear() {
        $this->hooks = array();
    }
}
