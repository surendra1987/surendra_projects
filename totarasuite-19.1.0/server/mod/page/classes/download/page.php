<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package mod_label
 */

namespace mod_page\download;

use core\format;
use totara_mobile\download\download_helper;
use totara_mobile\download\downloadable_activity;

class page extends downloadable_activity {
    /**
     * @var string|null
     */
    public ?string $content = null;

    /**
     * @var string
     */
    public string $timemodified;

    /**
     * @var string
     */
    public string $contentformat;

    /**
     * @return self
     */
    public function get_content(): self {
        return $this->get_page_instance_content(download_helper::get_generic_activity_content($this->cm_info, $this));
    }

    /**
     * Get the content from page instance.
     *
     * @param page $page
     * @return self
     */
    private function get_page_instance_content(page $page): self {
        $instance = download_helper::get_activity_instance($this->cm_info);
        $page->content = $instance->content;
        $page->contentformat = format::from_moodle($instance->contentformat);
        $page->timemodified = userdate($instance->timemodified, get_string('strftimedaydatetime', 'langconfig'));
        $page->attachments = array_merge($page->attachments, download_helper::get_attachments_for_content($this->cm_info->context, 'mod_page', 'content', 0));

        return $page;
    }

    /**
     * @inheritDoc
     */
    public function get_total_download_size(): int {
        return $this->get_activity_size_by_area(['intro', 'content']);
    }
}