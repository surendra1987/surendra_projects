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
namespace mod_label\download;

use totara_mobile\download\download_helper;
use totara_mobile\download\downloadable_activity;

class label extends downloadable_activity {

    /**
     * @return self
     */
    public function get_content(): self {
        return download_helper::get_generic_activity_content($this->cm_info, $this);
    }

    /**
     * @inheritDoc
     */
    public function get_total_download_size(): int {
        return $this->get_activity_size_by_area(['intro']);
    }
}