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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\feature\generative_image;

use core_ai\feature\response_base;

/**
 * Class representing an image generation response.
 */
class response extends response_base {
    /** @var string|null The URL of the generated image */
    public ?string $url;

    /** @var string|null The base64 encoded image data */
    public ?string $b64_json;

    /** @var string|null The caption for the generated image */
    public ?string $caption;

    /** @var string|null Error message if any */
    public ?string $error = null;

    /**
     * Constructor.
     *
     * @param string|null $url The URL of the generated image
     * @param string|null $b64_json The base64 encoded image data
     * @param string|null $caption The caption for the generated image
     * @param string|null $error Error message if any
     */
    public function __construct(?string $url = null, ?string $b64_json = null, ?string $caption = null, ?string $error = null) {
        $this->url = $url;
        $this->b64_json = $b64_json;
        $this->caption = $caption;
        $this->error = $error;
    }

    /**
     * Get the data for serialization.
     *
     * @return array
     */
    public function to_array(): array {
        return array_filter([
            'url' => $this->url,
            'b64_json' => $this->b64_json,
            'caption' => $this->caption,
            'error' => $this->error,
        ], fn($value) => !is_null($value));
    }

    public function get_error(): ?string {
        return $this->error;
    }

    public function has_error(): bool {
        return !empty($this->error);
    }

    public function is_structured(): bool {
        return true;
    }
}
