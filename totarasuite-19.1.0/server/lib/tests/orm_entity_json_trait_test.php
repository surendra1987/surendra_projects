<?php
/**
 * This file is part of Totara Learn
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_orm
 * @category test
 */

use core\orm\entity\model;
use core\orm\entity\traits\json_trait;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tests/orm_entity_testcase.php');

/**
 * Class core_orm_entity_json_trait_testcase
 *
 * See the end of this file for the sample model class that mixes in the json_trait.
 *
 * @package core
 * @group orm
 */
class core_orm_entity_json_trait_test extends orm_entity_testcase {

    private function sample_params_attribute(): array {
        return ['params' => '{ "label": "Bunnies!", "status": true, "count": 72 }'];
    }

    public function test_json_decode() {
        $record = $this->create_sample_record($this->sample_params_attribute());
        $model = json_sample_model::load_by_id($record['id']);

        $expected = new stdClass();
        $expected->label = 'Bunnies!';
        $expected->status = true;
        $expected->count = 72;
        $this->assertEquals($expected, $model->params);

        $this->assertEquals('Bunnies!', $model->params->label);
    }

    public function test_json_decode_to_array() {
        $record = $this->create_sample_record($this->sample_params_attribute());
        $model = json_sample_model::load_by_id($record['id']);

        $expected = [
            'label' => 'Bunnies!',
            'status' => true,
            'count' => 72
        ];
        $this->assertEquals($expected, $model->params_array);
    }

    public function test_json_encode() {
        $record = $this->create_sample_record($this->sample_params_attribute());
        $model = json_sample_model::load_by_id($record['id']);

        $params = $model->params;
        $params->label = 'Frogs?';
        $model->set_params($params);

        $this->assertEquals('{"label":"Frogs?","status":true,"count":72}', $model->params_raw);
    }

    public function test_json_encode_edge_cases() {
        $record = $this->create_sample_record(['params' => '']);
        $model = json_sample_model::load_by_id($record['id']);

        // PHP null
        $model->set_params(null);
        $this->assertEquals("null", $model->params_raw);

        // Empty string
        $model->set_params('');
        $this->assertEquals('""', $model->params_raw);

        // Scalar
        $model->set_params("I'm just a string!");
        $this->assertEquals('"I\'m just a string!"', $model->params_raw);

        // Float
        $model->set_params(3.14159);
        $this->assertEquals('3.1415899999999999', $model->params_raw);

        // Empty array
        $model->set_params([]);
        $this->assertEquals('{}', $model->params_raw);

        // Empty object
        $model->set_params(new stdClass());
        $this->assertEquals('{}', $model->params_raw);
    }

    public function test_json_decode_edge_cases() {
        $expected = new stdClass();

        // PHP null
        $record = $this->create_sample_record(['params' => 'null']);
        $model = json_sample_model::load_by_id($record['id']);
        $this->assertEquals($expected, $model->params);

        // Empty string
        $record = $this->create_sample_record(['params' => '""']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected->scalar = '';
        $this->assertEquals($expected, $model->params);

        // Scalar
        $record = $this->create_sample_record(['params' => '"I\'m just a string!"']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected->scalar = "I'm just a string!";
        $this->assertEquals($expected, $model->params);

        // Float
        $record = $this->create_sample_record(['params' => '3.14159']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected->scalar = 3.14159;
        $this->assertEquals($expected, $model->params);

        // Empty array
        $record = $this->create_sample_record(['params' => '[]']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected = new stdClass();
        $this->assertEquals($expected, $model->params);

        // Empty object
        $record = $this->create_sample_record(['params' => '{}']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected = new stdClass();
        $this->assertEquals($expected, $model->params);
    }

    public function test_json_decode_to_array_edge_cases() {
        $expected = [];

        // PHP null
        $record = $this->create_sample_record(['params' => 'null']);
        $model = json_sample_model::load_by_id($record['id']);
        $this->assertEquals($expected, $model->params_array);

        // Empty string
        $record = $this->create_sample_record(['params' => '""']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected[0] = '';
        $this->assertEquals($expected, $model->params_array);

        // Scalar
        $record = $this->create_sample_record(['params' => '"I\'m just a string!"']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected[0] = "I'm just a string!";
        $this->assertEquals($expected, $model->params_array);

        // Float
        $record = $this->create_sample_record(['params' => '3.14159']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected[0] = 3.14159;
        $this->assertEquals($expected, $model->params_array);

        // Empty array
        $record = $this->create_sample_record(['params' => '[]']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected = [];
        $this->assertEquals($expected, $model->params_array);

        // Empty object
        $record = $this->create_sample_record(['params' => '{}']);
        $model = json_sample_model::load_by_id($record['id']);
        $expected = [];
        $this->assertEquals($expected, $model->params_array);
    }

    public function test_json_decode_throw_on_error() {
        $record = $this->create_sample_record(['params' => '{ status: "foo", count: 26 }']);
        $model = json_sample_model::load_by_id($record['id']);

        try {
            $params = $model->params;
            $this->fail('Expected JsonException decoding a bad string');
        } catch (\JsonException $e) {
            $this->assertStringContainsString("Syntax error", $e);
        }
    }

    public function test_json_encode_decode_force_object() {
        $record = $this->create_sample_record(['params' => '["List","Of","Strings"]']);
        $model = json_sample_model::load_by_id($record['id']);

        $expected = new stdClass();
        $expected->{0} = 'List';
        $expected->{1} = 'Of';
        $expected->{2} = 'Strings';
        $this->assertEquals($expected, $model->params);

        // Indexed array, forced to object.
        $new_params = ["List", "Of", "String cheeses"];
        $model->set_params($new_params);
        $this->assertEquals('{"0":"List","1":"Of","2":"String cheeses"}', $model->params_raw);

        $expected->{2} = 'String cheeses';
        $this->assertEquals($expected, $model->params);

        // Still comes back as an array if you like.
        $this->assertEquals($new_params, $model->params_array);

        // Associative array is also forced to object.
        $new_params = ["Type" => "List", "Of" => "Pizzas"];
        $model->set_params($new_params);
        $this->assertEquals('{"Type":"List","Of":"Pizzas"}', $model->params_raw);

        // Object with indexed array as a property, it is also forced to an object.
        $new_params = $model->params;
        $new_params->items = ["New York", "Hawaiian", "Sicilian"];
        $model->set_params($new_params);
        $this->assertEquals('{"Type":"List","Of":"Pizzas","items":{"0":"New York","1":"Hawaiian","2":"Sicilian"}}', $model->params_raw);
    }

    public function test_json_encode_decode_unescaped_slashes() {
        $record = $this->create_sample_record(['params' => '']);
        $model = json_sample_model::load_by_id($record['id']);

        // Encode.
        $model->set_params(['This isn\'t double-escaped']);
        $this->assertEquals('{"0":"This isn\'t double-escaped"}', $model->params_raw);

        // Decode.
        $this->assertEquals("This isn't double-escaped", $model->params->{0});
    }

    public function test_json_encode_invalid_utf8() {
        $invalid_utf8 = "\xff";
        $record = $this->create_sample_record(['params' => '']);
        $model = json_sample_model::load_by_id($record['id']);

        $model->set_params([$invalid_utf8]);
        $this->assertNotEquals('{"0":"\u07ff"}', $model->params_raw);
        $this->assertEquals('{"0":"\ufffd"}', $model->params_raw);
    }

    public function test_json_decode_invalid_utf8() {
        $invalid_utf8 = "\xff";
        $params = [
            'id' => 123,
            'name' => 'JohnP',
            'created_at' => '1544499389',
            'params' => '{"0":"'. $invalid_utf8 . '"}'
        ];

        // Can't store this one in the database because of the invalid character, so
        // just fake up an non-existent entity.
        $entity = new sample_entity($params, false);
        $model = json_sample_model::load_by_entity($entity);
        $this->assertEquals("�", $model->params->{0});
    }

    public function test_json_decode_big_int() {
        $record = $this->create_sample_record(['params' => '{"little": 123, "big": 12345678901234567890}']);
        $model = json_sample_model::load_by_id($record['id']);

        $this->assertIsInt($model->params->little);
        $this->assertIsNotInt($model->params->big);
        $this->assertEquals("12345678901234567890", $model->params->big);
    }

    private function add_depth(stdClass $obj, int $depth) {
        $depth++;
        if ($depth < 34) {
            $obj->test = new stdClass();
            $this->add_depth($obj->test, $depth);
        } else {
            $obj->test = 'Hello world';
        }
        return $obj;
    }

    public function test_json_encode_decode_too_deep() {
        $test = new stdClass();
        $test = $this->add_depth($test, 0);

        // Encode.
        $record = $this->create_sample_record(['params' => '']);
        $model = json_sample_model::load_by_id($record['id']);
        try {
            $model->set_params($test);
            $this->fail("Expected JsonException due to depth");
        } catch (\JsonException $e) {
            $this->assertStringContainsString("Maximum stack depth exceeded", $e);
        }

        // Decode.
        $decode_test_json = json_encode($test);
        $record = $this->create_sample_record(['params' => $decode_test_json]);
        $model = json_sample_model::load_by_id($record['id']);
        try {
            $foo = $model->params;
            $this->fail("Expected JsonException due to depth");
        } catch (\JsonException $e) {
            $this->assertStringContainsString("Maximum stack depth exceeded", $e);
        }
    }
}

/**
 * Class json_sample_model used for testing a model with the json_trait
 *
 */
/**
 * Class json_sample_model
 *
 * @property-read stdClass $params
 * @property-read array $params_array
 * @property-read string $params_raw
 */
class json_sample_model extends model {

    use json_trait;

    /** @var sample_entity */
    protected $entity;

    protected $model_accessor_whitelist = [
        'params',
        'params_array',
        'params_raw',
    ];

    protected static function get_entity_class(): string {
        return sample_entity::class;
    }

    public function get_params(): stdClass {
        return self::json_decode($this->entity->params);
    }

    public function get_params_array(): array {
        return self::json_decode_to_array($this->entity->params);
    }

    public function get_params_raw(): ?string {
        return $this->entity->params;
    }

    public function set_params($data): self {
        $this->entity->params = self::json_encode($data);
        $this->entity->save();

        return $this;
    }
}
