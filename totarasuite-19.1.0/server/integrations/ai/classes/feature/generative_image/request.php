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

use coding_exception;
use core_ai\feature\request_base;

/**
 * Class representing an image generation request.
 */
class request extends request_base {
    /** @var string The prompt to generate the image from */
    private string $prompt;

    /** @var string|null The model to use for generation */
    public ?string $model = null;

    /** @var string|null The size of the generated image */
    public ?string $size = null;

    /** @var string|null The quality of the generated image */
    public ?string $quality = null;

    /** @var string|null The style of the generated image */
    public ?string $style = null;

    /** @var string|null The format of the response */
    public ?string $response_format = null;

    /**
     * Build an image generation request.
     *
     * @param array $input The only required parameter is $input['prompt']
     *
     */
    public function __construct(array $input) {
        if (!isset($input['prompt'])) {
            throw new coding_exception('prompt is required');
        }
        $this->prompt = $input['prompt'];
        $this->model = $input['model'] ?? null;
        $this->size = $input['size'] ?? null;
        $this->quality = $input['quality'] ?? null;
        $this->style = $input['style'] ?? null;
        $this->response_format = $input['response_format'] ?? null;
    }

    /**
     * Get the data for the request.
     *
     * @return array
     */
    public function to_array(): array {
        return array_filter([
            'prompt' => $this->prompt,
            'model' => $this->model,
            'size' => $this->size,
            'quality' => $this->quality,
            'style' => $this->style,
            'response_format' => $this->response_format,
        ], fn($value) => !is_null($value));
    }

    /**
     * Get the prompt.
     *
     * @return string
     */
    public function get_prompt(): string {
        return $this->prompt;
    }

    /**
     * Set the prompt.
     *
     * @param string $prompt
     * @return self
     */
    public function set_prompt(string $prompt): self {
        $this->prompt = $prompt;
        return $this;
    }
}
