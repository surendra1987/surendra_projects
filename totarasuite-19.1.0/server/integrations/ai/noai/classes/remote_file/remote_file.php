<?php
/*
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package ai_noai
 */

namespace ai_noai\remote_file;

use core_ai\remote_file\remote_file_base;
use core_ai\remote_file\remote_file_request;
use core_ai\remote_file\response\create;
use core_ai\remote_file\response\delete;
use core_ai\remote_file\response\list_files;
use core_ai\remote_file\response\retrieve;

/**
 * No AI remote_file class, does not provide any remote file support.
 */
class remote_file extends remote_file_base {
    /**
     * Get the name of this implementation
     *
     * @return string
     */
    public static function get_name(): string {
        return 'No File Support';
    }

    protected function call_list(remote_file_request $request): list_files {
        throw new \coding_exception('File operations are not supported by this feature');
    }

    protected function call_retrieve(remote_file_request $request): retrieve {
        throw new \coding_exception('File operations are not supported by this feature');
    }

    protected function call_download(remote_file_request $request): string {
        throw new \coding_exception('File operations are not supported by this feature');
    }

    protected function call_upload(remote_file_request $request): create {
        throw new \coding_exception('File operations are not supported by this feature');
    }

    protected function call_delete(remote_file_request $request): delete {
        throw new \coding_exception('File operations are not supported by this feature');
    }
}
