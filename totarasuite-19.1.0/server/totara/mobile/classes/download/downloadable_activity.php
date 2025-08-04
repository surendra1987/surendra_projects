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
 * @package totara_mobile
 */

namespace totara_mobile\download;

use cm_info;
use context_module;
use stdClass;

abstract class downloadable_activity {
    /**
     * @var int
     */
    public int $id;

    /**
     * @var int
     */
    public int $instanceid;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string|null
     */
    public ?string $intro;

    /**
     * @var string|null
     */
    public ?string $introformat;

    /**
     * @var cm_info
     */
    protected cm_info $cm_info;

    /**
     * @var array
     */
    public array $attachments;

    /**
     * @var string|null
     */
    public ?string $viewurl;

    /**
     * @var bool
     */
    public bool $showdescription;

    /**
     * @var string|null
     */
    public ?String $completion;

    /**
     * @var string|null
     */
    public ?String $completionstatus;

    /**
     * @var bool
     */
    public bool $completionenabled;

    /**
     * @var int
     */
    public int $downloadsize;

    /**
     * @param cm_info $cm_info
     */
    public function __construct(cm_info $cm_info) {
        $this->cm_info = $cm_info;
        $this->attachments = [];
        $this->viewurl = null;
        $this->intro = null;
        $this->introformat = FORMAT_HTML;
        $this->showdescription = false;
        $this->completion = null;
        $this->completionstatus = null;
        $this->completionenabled = false;
        $this->downloadsize = 0;
    }

    /**
     * To get activity content for specific activity.
     *
     * @return self
     */
    abstract public function get_content(): self;

    /**
     * @return string
     */
    public function get_modtype(): string {
        return $this->cm_info->modname;
    }

    /**
     * @return context_module
     */
    public function get_mod_context(): context_module {
        return $this->cm_info->context;
    }

    /**
     * @return stdClass
     */
    public function get_course(): stdClass {
        return $this->cm_info->get_course();
    }

    /**
     * @return stdClass
     */
    public function get_cm(): stdClass {
        return $this->cm_info->get_course_module_record();
    }

    /**
     * Get each activity download total size
     *
     * @return int
     */
    abstract public function get_total_download_size(): int;

    /**
     * @param array $areas
     * @param int $item
     *
     * @return int
     */
    final public function get_activity_size_by_area(array $areas, int $item = 0): int {
        $fs = get_file_storage();
        $total_size = 0;

        foreach ($areas as $area) {
            foreach ($fs->get_area_files($this->get_mod_context()->id, 'mod_'.$this->get_modtype(), $area, $item) as $file) {
                if ($file->is_directory()) {
                    continue;
                }

                if ($file->get_filename() == '.') {
                    continue;
                }

                $total_size += $file->get_filesize();
            }
        }

        return $total_size;
    }
}