<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Samantha Jayasinghe <samantha.jayasinghe@totaralearning.com>
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package core_orm
 * @category test
 */

use core\orm\entity\encrypted_model;
use core\orm\entity\model;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tests/orm_entity_testcase.php');

/**
 * Class core_orm_entity_model_test
 *
 * @package core
 * @group orm
 */
class core_orm_entity_model_test extends orm_entity_testcase {

    public function test_get_id() {
        $params = [
            'id' => 1001
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model($entity);
        $this->assertSame(1001, $model->get_id());
    }

    public function test_load_by_entity() {
        $params = [
            'id' => 123,
            'name' => 'JohnP',
            'created_at' => '1544499389'
        ];

        $entity = new sample_entity($params, false);
        $model = sample_model::load_by_entity($entity);
        $this->assertEquals(123, $model->id);
        $this->assertEquals('JohnP', $model->name);
        $this->assertEquals('1544499389', $model->created_at);
    }

    public function test_load_by_id() {
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'JohnP';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $model = sample_model::load_by_id($entity->id);
        $this->assertSame('JohnP', $model->name);
        $this->assertSame('1544499389', $model->created_at);
    }

    public function test_limited_access_to_array() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);
        $this->assertEquals('corona', $model->type);
        $this->assertEquals(1001, $model->parent_id);
        $this->assertEquals(sample_entity::class, $model->entity_class);
    }

    public function test_isset() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->assertTrue(isset($model->type));
        $this->assertTrue(isset($model->parent_id));
        $this->assertTrue(isset($model->entity_class));
        $this->assertTrue(isset($model->name));
        $this->assertFalse(isset($model->id));
        $this->assertFalse(isset($model->foobar));

        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => null,
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        // Null values should return false as in other cases
        $this->assertFalse(isset($model->type));
    }

    public function test_limited_access_non_existing_attribute() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model($entity);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Tried to access a property that is not available: idonotexist');

        $test = $model->idonotexist;
    }

    public function test_accessor_whitelist_takes_precedence() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->assertSame('JOHN', $model->name);
    }

    public function test_limited_access_not_defined_attribute() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Tried to access a property that is not available: idonotexist');

        $test = $model->idonotexist;
    }

    public function test_limited_access_attribute_not_on_whitelist() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Tried to access a property that is not available: is_deleted');

        $test = $model->is_deleted;
    }

    public function test_limited_access_attribute_missing_method_for_whitelisted_property() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(
            'Tried to access a method attribute which should exist but does not: get_method_not_implemented'
        );

        $test = $model->method_not_implemented;
    }

    public function test_limited_access_attribute_missing_property_for_whitelisted_property() {
        $params = [
            'id'        => 123,
            'name'      => 'John',
            'type'      => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model_limited_access($entity);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Tried to access an entity attribute which should exist but does not: unknown_property');

        $test = $model->unknown_property;
    }

    public function test_wrong_entity_class() {
        $params = [
            'id' => 123,
        ];

        $entity = new sample_entity($params, false);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Expected entity class to match model class');

        sample_model_wrong_entity::load_by_entity($entity);
    }

    public function test_non_exist_entity() {
        $entity = new sample_entity();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Can load only existing entities');

        sample_model::load_by_entity($entity);
    }

    public function test_get_entity_copy() {
        $params = [
            'id' => 123,
            'name' => 'John',
            'type' => 'corona',
            'parent_id' => 1001,
        ];

        $entity = new sample_entity($params, false);
        $model = new sample_model($entity);
        $cloned_entity = $model->get_entity_copy();

        $this->assertEquals($entity->name, $cloned_entity->name);
        $this->assertEquals('John', $entity->name);
        $this->assertEquals('John', $cloned_entity->name);

        $cloned_entity->name = 'Steve';

        $this->assertNotEquals($entity->name, $cloned_entity->name);
        $this->assertEquals('John', $entity->name);
        $this->assertEquals('Steve', $cloned_entity->name);

        $entity->name = 'Eric';

        $this->assertNotEquals($entity->name, $cloned_entity->name);
        $this->assertEquals('Eric', $entity->name);
        $this->assertEquals('Steve', $cloned_entity->name);
    }

    public function test_set_encrypted_attribute() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Alice';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $this->assertEmpty($entity->token);

        $model = new sample_model($entity);
        $model->set_encrypted_attribute('token', 'The goat is in the red barn.');
        $entity->refresh();

        $this->assertNotEmpty($entity->token);
        list($key_id, $ciphertext, $iv, $tag) = explode('::', $entity->token);
        $this->assertEquals('1686744000', $key_id);
        $this->assertNotEmpty($ciphertext);
        $this->assertNotEmpty($iv);
        $this->assertNotEmpty($tag);

        $this->reset_encryption_keys();
    }

    public function test_that_model_protects_encrypted_attributes_by_default() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Bob';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $model = new sample_model_limited_access($entity);
        $model->set_encrypted_attribute('token', 'The goat is in the red barn.');

        // Try using __get()
        try {
            $value = $model->token;
            $this->fail('Expected token to not be in the attributed whitelist.');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Tried to access a property that is not available', $e->getMessage());
        }

        // Try using get_encrypted_attribute()
        $reflection = new ReflectionMethod($model, 'get_encrypted_attribute');
        $this->assertTrue($reflection->isProtected());
        $this->assertFalse($reflection->isPublic(), 'Expected get_encrypted_attribute method to be protected, do not make it public.');

        $this->reset_encryption_keys();
    }

    public function test_roundtrip_set_get_encrypted_attribute() {
        $this->use_encryption_keys();
        $this->create_table();
        $this->create_sample_records();

        $entity = new sample_entity();
        $entity->name = 'Alice';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $model = new sample_model($entity);
        $model->set_encrypted_attribute('token', 'The goat is in the red barn.');
        $entity->refresh();

        $this->assertNotEmpty($entity->token);
        $this->assertNotEquals('The goat is in the red barn.', $entity->token);
        list($key_id, $ciphertext, $iv, $tag) = explode('::', $entity->token);
        $this->assertEquals('1686744000', $key_id);
        $this->assertNotEmpty($ciphertext);
        $this->assertNotEmpty($iv);
        $this->assertNotEmpty($tag);

        $value = $model->token;
        $this->assertEquals('The goat is in the red barn.', $value);
        unset($model);

        $model_new = new sample_model($entity);
        $this->assertEquals('The goat is in the red barn.', $model_new->token);

        $this->reset_encryption_keys();
    }

    public function test_decryption_limited_to_original_model_class() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Alice';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $model = new sample_model($entity);
        $model->set_encrypted_attribute('token', 'The goat is in the red barn.');

        // Save the sample_model encrypted token for later.
        $entity->refresh();
        $original_token = $entity->token;

        $model_new = new sample_model_extended($entity);
        $this->assertNotEquals('The goat is in the red barn.', $model_new->token);
        $this->assertFalse($model_new->token);

        // However if the extended class saves it, then it can be read.
        $model_new->set_encrypted_attribute('token', 'The goat is in the red barn.');
        $this->assertEquals('The goat is in the red barn.', $model_new->token);

        // Let's compare encrypted tokens.
        $entity->refresh();
        $extended_token = $entity->token;
        // The key_id is the same, but everything else is different.
        $this->assertEquals(substr($original_token, 0, 12), substr($extended_token, 0, 12));
        $this->assertNotEquals($original_token, $extended_token);

        $this->reset_encryption_keys();
    }

    public function test_decryption_returns_plaintext() {
        $this->use_encryption_keys();

        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Alice';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->token = 'I am in plain text';
        $entity->save();

        $model = new sample_model($entity);
        $this->assertSame('I am in plain text', $model->token);
        $this->assertSame('I am in plain text', $entity->token);

        $model->set_encrypted_attribute('token', 'I am in plain text');
        $entity->refresh();
        $this->assertSame('I am in plain text', $model->token);
        $this->assertNotSame('I am in plain text', $entity->token);

        $this->reset_encryption_keys();
    }

    public function test_encrypted_attribute_locked_to_entity_id() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity1 = new sample_entity();
        $entity1->name = 'Alice';
        $entity1->created_at = '1544499389';
        $entity1->parent_id = 1;
        $entity1->save();

        $model1 = new sample_model($entity1);
        $model1->set_encrypted_attribute('token', 'The goat is in the red barn.');
        $entity1->refresh();

        $entity2 = new sample_entity();
        $entity2->name = 'Bob';
        $entity2->created_at = '1544499392';
        $entity2->parent_id = 1;
        // Try to re-use Alice's token on Bob's record.
        $entity2->token = $entity1->token;
        $entity2->save();

        $model2 = new sample_model($entity2);
        $this->assertNotEquals('The goat is in the red barn.', $model2->token);
        $this->assertFalse($model2->token);

        // Still works for Alice, though.
        $this->assertEquals('The goat is in the red barn.', $model1->token);

        $this->reset_encryption_keys();
    }

    public function test_get_encrypted_attribute_saved_with_previous_key() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Bob';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        /**
         * You should not need to replace this value in future, as long as the '1655208000' key doesn't change, and
         * we don't make any breaking changes to how encrypted attributes work. Nevertheless, if you DO need to
         * replace it, then comment out the '1655208000' key in `orm_entity_testcase.php`, and use the following
         * code to get the new value:
         *
         * $entity->save();
         * $model = new sample_model($entity);
         * $model->set_encrypted_attribute('token', 'The goat is in the red barn.');
         * $entity->refresh();
         * exit($entity->token);
         */

        $entity->token = '1655208000::aHNuRkd2Ly8yYnlMc1plTHVlVXUwNzFiVEVRVjFJaGQ5WThUUEE9PQ==::qEncboyzdBxxWfXo::YOuEAT4iJTAfnQXXZkIskg==';
        $entity->save();

        $model = new sample_model($entity);
        $value = $model->token;
        $this->assertEquals('The goat is in the red barn.', $value);

        $this->reset_encryption_keys();
    }

    public function test_to_stdClass() {
        $this->use_encryption_keys();
        $this->create_table();

        $entity = new sample_entity();
        $entity->name = 'Tom';
        $entity->created_at = '1544499389';
        $entity->parent_id = 1;
        $entity->save();

        $model = new sample_model_limited_access($entity);
        $model->set_encrypted_attribute('token', 'The goat is in the red barn.');

        $expected = new stdClass();
        $expected->type = '0';
        $expected->parent_id = 1;
        $expected->entity_class = 'sample_entity';
        $expected->name = 'TOM';
        $expected->token = 'The goat is in the red barn.';

        $object = $model->to_stdClass();
        $this->assertEquals($expected, $object);
    }

}

/**
 * Class sample_model
 *
 * @property-read string $name
 * @property-read string $token
 * @property-read int $created_at
 * @property-read string $type
 * @property-read string $entity_class
 */
class sample_model extends encrypted_model {

    // Note: There is not entity_attribute_whitelist, so all attributes are available.

    protected $encrypted_attribute_list = ['token'];

    protected static function get_entity_class(): string {
        return sample_entity::class;
    }
}

/**
 * Class sample_model_extended, extends the sample_model class.
 *
 * @property-read string $token
 */
class sample_model_extended extends sample_model {

    protected $entity_attribute_whitelist = ['token'];

    protected $encrypted_attribute_list = ['token'];

    protected static function get_entity_class(): string {
        return sample_entity::class;
    }
}

/**
 * Class sample_model_limited_access
 *
 * @property-read string $type
 * @property-read int $parent_id
 * @property-read string $name will be UPPERCASE
 * @property-read string $entity_class
 */
class sample_model_limited_access extends encrypted_model {
    protected $encrypted_attribute_list = ['token'];

    // Do not add token to this list.
    protected $entity_attribute_whitelist = [
        'type',
        'parent_id',
        'unknown_property'
    ];

    protected $model_accessor_whitelist = [
        'entity_class',
        'method_not_implemented',
        'name'
    ];

    protected static function get_entity_class(): string {
        return sample_entity::class;
    }

    public function get_name(): string {
        return strtoupper($this->entity->name);
    }
}

class sample_model_wrong_entity extends model {
    protected static function get_entity_class(): string {
        return 'abc';
    }
}
