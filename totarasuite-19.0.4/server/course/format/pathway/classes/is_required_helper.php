<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

namespace format_pathway;

use coding_exception;
use moodle_page;
use settings_navigation;

/**
 * Provides functions to help determine when the pathway format is required on a given page.
 */
class is_required_helper {
    protected static ?is_required_helper $instance;

    protected ?bool $result = null;

    protected string $page_hash;

    /**
     * Preventing this class from instantiation.
     */
    protected function __construct(string $page_hash) {
        $this->page_hash = $page_hash;
    }

    /**
     * Will reset the instance - allows page resets to handle this
     *
     * @return void
     */
    public static function reset_instance(): void {
        self::$instance = null;
    }

    /**
     * Creates the initial instance of the helper.
     *
     * @param moodle_page $page The current page
     * @param string $page_layout The layout that is currently configured
     * @return static
     * @throws coding_exception
     */
    public static function initialise_instance(moodle_page $page, string $page_layout): self {
        $page_hash = sha1(json_encode($page));
        if (isset(self::$instance)) {
            if (is_null(self::$instance->result)) {
                debugging('Loop detected - helper instance is not initialised');
                return self::$instance;
            } else if ($page_hash == self::$instance->page_hash) {
                return self::$instance;
            } else {
                debugging('Helper instance should only be instantiated once: ' . $page_hash);
                self::$instance = new self($page_hash);
                self::$instance->result = self::$instance->calculate_result($page, $page_layout);
                return self::$instance;
            }
        }
        self::$instance = new self($page_hash);
        self::$instance->result = self::$instance->calculate_result($page, $page_layout);
        return self::$instance;
    }

    /**
     * Get the helper instance, whihc must have been initialised earlier using initialise_instance.
     *
     * @return static
     * @throws coding_exception
     */
    public static function get_instance(): self {
        if (!isset(self::$instance)) {
            throw new coding_exception('Helper instance has not been initialised');
        }
        // Note $instance->result might be null if get_instance is called during initialise_instance.
        return self::$instance;
    }

    /**
     * Used for testing, to allow a mock instance to be set.
     *
     * @param self|null $instance
     * @return void
     */
    public static function set_instance(?self $instance): void {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('set_instance can only be used in phpunit tests');
        }
        self::$instance = $instance;
    }

    /**
     * Determine if the given page is for web - not CPI, API calls, AJAX requests etc.
     *
     * @param moodle_page $page
     * @return bool
     */
    protected function is_page_only_for_web(moodle_page $page): bool {
        return $page->requestorigin == 'web';
    }

    /**
     * Determine if the current request is an ajax script.
     *
     * @return bool
     */
    protected function is_current_request_ajax_script(): bool {
        return defined('AJAX_SCRIPT') && AJAX_SCRIPT;
    }

    /**
     * Determine if the given layout is one that can be replaced by pathway.
     *
     * Only replace a whitelisted set of layouts. This is because we don't want to impact some layouts that
     * are designed not to have chrome around them, for example redirect, webview, secure, popup, etc.
     *
     * @param string $layout
     * @return bool
     */
    protected function is_replaceable_layout(string $layout): bool {
        $replaceable_layouts = [
            'default',
            'base',
            'standard',
            'incourse',
            'course',
            'admin',
            'report',
        ];
        return in_array($layout, $replaceable_layouts);
    }

    /**
     * Determine if the page has a course set.
     *
     * @param moodle_page $page
     * @return bool
     */
    protected function is_course_id_set(moodle_page $page): bool {
        return !empty($page->course->id);
    }

    /**
     * Determine if the given page is for a course which uses the pathway format.
     *
     * @param moodle_page $page
     * @return bool
     */
    protected function is_course_format_pathway(moodle_page $page): bool {
        global $CFG;
        require_once($CFG->dirroot . '/course/format/lib.php');
        $format = course_get_format($page->course->id);
        $format_name = $format->get_format();
        return $format_name == 'pathway';
    }

    /**
     * Determine if the layout of the current request should be replaced by the pathway layout.
     *
     * @param moodle_page $page
     * @return bool
     */
    protected function calculate_result(moodle_page $page, string $page_layout): bool {
        // TODO check for issues during web install/upgrade.

        if (!$this->is_page_only_for_web($page)) {
            return false;
        }

        if ($this->is_current_request_ajax_script()) {
            return false;
        }

        if (!$this->is_replaceable_layout($page_layout)) {
            return false;
        }

        if (!$this->is_course_id_set($page)) {
            return false;
        }

        if (!$this->is_course_format_pathway($page)) {
            return false;
        }

        return true;
    }

    /**
     * Indicates if the pathway layout should be used.
     *
     * The result will be null if this function is called before initialise_instance has completed.
     *
     * @return bool|null
     */
    public function should_replace_layout(): ?bool {
        return $this->result;
    }
}