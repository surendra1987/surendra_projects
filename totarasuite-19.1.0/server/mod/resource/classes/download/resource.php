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
 * @package mod_resource
 */
namespace mod_resource\download;

use mod_resource\output\view_generator_factory;
use moodle_exception;
use totara_core\form\menu\reset;
use totara_mobile\download\download_helper;
use totara_mobile\download\downloadable_activity;

class resource extends downloadable_activity {
    /**
     * @var string
     */
    public string $display_type;

    /**
     * @var object|null
     */
    public object|null $file = null;

    /**
     * @var string
     */
    public string $extra_info;

    /**
     * @return self
     */
    public function get_content(): self {
        global $CFG;
        require_once $CFG->dirroot.'/mod/resource/locallib.php';

        $activity = download_helper::get_generic_activity_content($this->cm_info, $this);
        $resource = download_helper::get_activity_instance($activity->cm_info);

        $files = download_helper::get_attachments_for_content($activity->get_mod_context(), 'mod_resource', 'content', 0, 'sortorder DESC, id ASC');

        if (!empty($files)) {
            $activity->file = reset($files);
            unset($files);
        }

        $activity->extra_info = resource_get_optional_details($resource, $activity->cm_info->get_course_module_record());
        $activity->display_type = $this->convert_display_type(resource_get_final_display_type($resource));
        return $activity;
    }

    /**
     * @inheritDoc
     */
    public function get_total_download_size(): int {
        $size = $this->get_activity_size_by_area(['intro']);

        $fs = get_file_storage();
        foreach ($fs->get_area_files($this->get_mod_context()->id, 'mod_'.$this->get_modtype(), 'content', 0, 'sortorder DESC, id ASC', false) as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }

            $size += $file->get_filesize();
            break;
        }

        return $size;
    }

    /**
     * Convert display type to human-readable name.
     *
     * @param int $display_type
     *
     * @return string
     * @throws moodle_exception
     */
    private function convert_display_type(int $display_type): string {
        switch ($display_type) {
            case RESOURCELIB_DISPLAY_AUTO: {
                return 'DISPLAY_AUTO';
            }
            case RESOURCELIB_DISPLAY_FRAME: {
                return 'DISPLAY_FRAME';
            }
            case RESOURCELIB_DISPLAY_EMBED: {
                return 'DISPLAY_EMBED';
            }
            case RESOURCELIB_DISPLAY_NEW: {
                return 'DISPLAY_NEW';
            }
            case RESOURCELIB_DISPLAY_DOWNLOAD: {
                return 'DISPLAY_DOWNLOAD';
            }
            case RESOURCELIB_DISPLAY_OPEN: {
                return 'DISPLAY_OPEN';
            }
            case RESOURCELIB_DISPLAY_POPUP: {
                return 'DISPLAY_POPUP';
            }
            case RESOURCELIB_DISPLAY_LINK: {
                return 'DISPLAY_LINK';
            }
            default: {
                throw new moodle_exception('Unsupported display type');
            }
        }
    }
}