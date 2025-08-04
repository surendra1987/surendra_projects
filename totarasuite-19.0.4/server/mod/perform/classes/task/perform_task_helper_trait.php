<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 *
 * @package mod_perform
 */

namespace mod_perform\task;

/**
 * Methods to help with adhoc / scheduled tasks for mod_perform.
 */
trait perform_task_helper_trait {
    /**
     * @var callable $log sink if any.
     */
    protected $write_to_log = null;

    /**
     * Declares the logging function to use. The adhoc task interface is not
     * very test friendly so need a way to check on its execution flow.
     *
     * @param callable $write_to_log string->void function to log a message.
     *
     * @return self this object.
     */
    public function set_logger(callable $write_to_log): self {
        $this->write_to_log = $write_to_log;
        return $this;
    }

    /**
     * Logs a message.
     *
     * @param string $message the message to log.
     */
    private function log(
        string $format,
               ...$args
    ): void {
        $message = sprintf($format, ...$args);
        $is_test = defined('PHPUNIT_TEST') && PHPUNIT_TEST;

        $write_to_log = $this->write_to_log;
        if ($write_to_log) {
            $write_to_log($message);
        } else if (!$is_test) {
            mtrace($message);
        }
    }
}
