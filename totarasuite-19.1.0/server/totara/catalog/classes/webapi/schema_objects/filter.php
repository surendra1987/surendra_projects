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
 * @package totara_catalog
 */

namespace totara_catalog\webapi\schema_objects;

class filter {
    /** @var string */
    private $key;

    /** @var string */
    private $title;

    /** @var array */
    private $options;

    /** @var string */
    private $type;

    /**
     * @param string $key
     * @param string $title
     * @param string $type
     * @param array $options
     */
    public function __construct(string $key, string $title, string $type, array $options = []) {
        $this->key = $key;
        $this->title = $title;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function get_key(): string {
        return $this->key;
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * @return array
     */
    public function get_options(): array {
        return $this->options;
    }

    /**
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }
}