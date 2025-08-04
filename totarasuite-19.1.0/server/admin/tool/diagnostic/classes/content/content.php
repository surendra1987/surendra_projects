<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\content;

class content {

    const TYPE_TEXT = 'TEXT';
    const TYPE_HTML = 'HTML';
    const TYPE_JSON = 'JSON';

    /** @var string */
    private $id;

    /** @var string */
    private $data;

    /** @var string */
    private $data_type;

    /**
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function set_id(string $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function get_data(): string {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function set_data(string $data): void {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function get_data_type(): string {
        return $this->data_type;
    }

    /**
     * @param string $data_type
     *
     * @return void
     */
    public function set_data_type(string $data_type): void {
        $this->data_type = $data_type;
    }

}
