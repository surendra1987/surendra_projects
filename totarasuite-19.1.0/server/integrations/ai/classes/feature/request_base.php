<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\feature;

use core_ai\data_transfer_object_base;

/**
 * An AI feature request is a JSON object. The exact structure is part of the feature
 * implementation, but it must be representable as an array.
 */
abstract class request_base extends data_transfer_object_base {

    protected ?string $model = null;

    /**
     * Return the request data as a PHP array.
     *
     * @return array
     * @deprecated since Totara 19.1.0
     */
    public function get_data(): array {
        debugging('The method ' . __METHOD__ . '() has been deprecated. Please use to_array() instead.', DEBUG_DEVELOPER);
        return $this->to_array();
    }

    /**
     * Set a preferred model to use for the request.
     *
     * @param string|null $model
     * @return $this
     */
    public function set_model(?string $model): static {
        $this->model = $model;
        return $this;
    }

    /**
     * Get the preferred model to use for the request.
     * @return string|null
     */
    public function get_model(): ?string {
        return $this->model;
    }
}
