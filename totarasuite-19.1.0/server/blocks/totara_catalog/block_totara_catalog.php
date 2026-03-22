<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package block_totara_catalog
 */

use core\format;
use core\webapi\formatter\field\string_field_formatter;
use totara_catalog\cache_handler;
use totara_catalog\local\query_helper;
use totara_tui\output\component;
use totara_webapi\serverless;

class block_totara_catalog extends block_base {

    /**
     * @inheritDoc
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_totara_catalog');
    }

    /**
     * @inheritDoc
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function display_with_border_by_default() {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function display_with_header_by_default() {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function get_content() {
        global $PAGE;

        if (!isloggedin() || isguestuser()) {
            return '';
        }

        if ($this->content !== null) {
            return $this->content;
        }

        /** @var \stdClass $config */
        $config = $this->config;

        $this->content = new stdClass;

        // Reset the catalog static caches.
        cache_handler::reset_all_caches();

        if (!totara_catalog_admin_setting_catalogtype::is_totara() && $PAGE->user_is_editing()) {
            $this->content->text = html_writer::tag('p', get_string('catalogtypeerror', 'block_totara_catalog'));
            return $this->content;
        }

        $url = null;
        if (isset($config->catalogurl)) {
            $url = new moodle_url($config->catalogurl);
            $query_strings = $url->get_query_string(false);
            if (!query_helper::validate_active_filters($query_strings)) {
                if ($PAGE->user_is_editing()) {
                    $this->content->text = html_writer::tag('p', get_string('urlnotavailableerror', 'block_totara_catalog'));
                    return $this->content;
                }
                return '';
            }
        }

        $props = [];
        if (isset($config->catalogurl) && isset($config->numberofitems)) {
            if (is_null($url)) {
                $url = new moodle_url($config->catalogurl);
            }

            $options = range(10, 20);
            $query_string = parse_url($config->catalogurl, PHP_URL_QUERY);
            $query_string .= "&perpageload=". $options[$config->numberofitems];
            $items = serverless::execute_operation('totara_catalog_items', ['input' => ['query_string' => $query_string]])['data']['totara_catalog_items'];
            $props =  [
                'catalog-url' => $url->out(false),
                'learning-items' => $items
            ];
        }

        // If no items found, do not display block.
        if (empty($items) || empty($items['items'])) {
            return null;
        }

        // Add title into props.
        $title = $config->collectiontitle ?? get_string('explore_learning', 'block_totara_catalog');
        $formatted_title = (new string_field_formatter(format::FORMAT_PLAIN, $this->context))
            ->format($title);
        $props = array_merge($props, ['title' => $formatted_title]);

        $component = new component('block_totara_catalog/components/LearningItemsScroller', $props);

        if ($PAGE->state < moodle_page::STATE_IN_BODY) {
            $component->register($PAGE);
        }

        $this->content->text = $component->out_html();

        return $this->content;
    }
}
