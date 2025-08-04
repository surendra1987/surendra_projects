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
 * @package mod_scorm
 */
namespace mod_scorm\download;

use totara_mobile\download\attachment_info;
use totara_mobile\download\download_helper;
use totara_mobile\download\downloadable_activity;

class scorm extends downloadable_activity {

    /**
     * @return self
     */
    public function get_content(): self {
        global $CFG;
        $urlbase = $CFG->wwwroot . '/totara/mobile/pluginfile.php';

        $activity = download_helper::get_generic_activity_content($this->cm_info, $this);
        $scorm = download_helper::get_activity_instance($this->cm_info);
        if ($scorm->allowmobileoffline &&
            scorm_version_check($scorm->version, SCORM_12) &&
            ($scorm->scormtype === SCORM_TYPE_LOCAL || $scorm->scormtype === SCORM_TYPE_LOCALSYNC)
        ) {
            $fs = get_file_storage();
            $context = $this->get_mod_context();
            if ($file = $fs->get_file($context->id, 'mod_scorm', 'package', 0, '/', $scorm->reference)) {
                $activity->attachments = array_merge($activity->attachments, [new attachment_info(\moodle_url::make_file_url($urlbase, "/$context->id/mod_scorm/package/$scorm->revision/$scorm->reference")->out(false), $file->get_filesize())]);
            }
        }
        return $activity;
    }

    /**
     * @inheritDoc
     */
    public function get_total_download_size(): int {
        $size = $this->get_activity_size_by_area(['intro']);
        $scorm = download_helper::get_activity_instance($this->cm_info);
        if ($scorm->allowmobileoffline && scorm_version_check($scorm->version, SCORM_12) && ($scorm->scormtype === SCORM_TYPE_LOCAL || $scorm->scormtype === SCORM_TYPE_LOCALSYNC)) {
            $fs = get_file_storage();
            $file = $fs->get_file($this->get_mod_context()->id, 'mod_scorm', 'package', 0, '/', $scorm->reference);
            if ($file && !$file->is_directory()) {
                $size += $file->get_filesize();
            }
        }

        return $size;
    }
}