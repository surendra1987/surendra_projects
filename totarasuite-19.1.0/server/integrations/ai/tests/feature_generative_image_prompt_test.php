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
 */

use core_ai\feature\generative_image\request;
use core_ai\feature\generative_image\response;
use core_phpunit\testcase;

require_once __DIR__ . '/fixtures/testable_generative_image.php';

/**
 * @group ai_integrations
 */
class core_ai_feature_generative_image_prompt_test extends testcase
{
    public function test_valid_request(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => 'A cat in a hat',
            'model' => 'dall-e-2',
            'size' => '512x512',
            'quality' => 'standard',
            'response_format' => 'url'
        ]);
        $feature->validate_request($req);
        $this->assertEquals('A cat in a hat', $req->get_prompt());
        $resp = $feature->call_api($req);
        $this->assertInstanceOf(response::class, $resp);
        $data = $resp->to_array();
        $this->assertEquals('http://example.com/image.png', $data['url'] ?? null);
        $this->assertEquals('A test image', $data['caption'] ?? null);
    }

    public function test_invalid_model(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => 'A cat',
            'model' => 'invalid-model',
            'size' => '512x512',
            'quality' => 'standard',
            'response_format' => 'url'
        ]);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid model selection');
        $feature->validate_request($req);
    }

    public function test_invalid_size(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => 'A cat',
            'model' => 'dall-e-2',
            'size' => '999x999',
            'quality' => 'standard',
            'response_format' => 'url'
        ]);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid image size for dall-e-2 model');
        $feature->validate_request($req);
    }

    public function test_invalid_quality(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => 'A cat',
            'model' => 'dall-e-2',
            'size' => '512x512',
            'quality' => 'ultra',
            'response_format' => 'url'
        ]);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid quality setting');
        $feature->validate_request($req);
    }

    public function test_invalid_response_format(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => 'A cat',
            'model' => 'dall-e-2',
            'size' => '512x512',
            'quality' => 'standard',
            'response_format' => 'invalid_format'
        ]);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid response format');
        $feature->validate_request($req);
    }

    public function test_missing_prompt(): void
    {
        $feature = new testable_generative_image();
        $req = new request([
            'prompt' => '',
            'model' => 'dall-e-2',
            'size' => '512x512',
            'quality' => 'standard',
            'response_format' => 'url'
        ]);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Prompt is required');
        $feature->validate_request($req);
    }
}
