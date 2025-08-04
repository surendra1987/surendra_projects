<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

namespace core_mfa\model;

use core\event\mfa_instance_created;
use core\event\mfa_instance_deleted;
use core\orm\entity\encrypted_model;
use core_mfa\entity\instance as instance_entity;
use core_mfa\factor;
use core_mfa\factor_factory;

/**
 * @property-read int $id ID
 * @property-read int $user_id
 * @property-read string $type
 * @property-read string|null $label
 * @property-read array $config
 * @property-read array $secure_config
 * @property-read int $created_at
 * @property-read int $updated_at
 * @property-read factor $factor
 */
class instance extends encrypted_model {
    /** @var factor|null */
    private ?factor $factor_instance = null;

    /** @inheritDoc */
    protected $entity_attribute_whitelist = [
        'id',
        'user_id',
        'type',
        'label',
        'instance',
        'created_at',
        'updated_at',
    ];

    /** @inheritDoc */
    protected $model_accessor_whitelist = [
        'config',
        'secure_config',
        'factor',
    ];

    /** @inheritDoc */
    protected $encrypted_attribute_list = [
        'secure_config',
    ];

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return instance_entity::class;
    }

    /**
     * Create a new MFA instance.
     *
     * @param int $user_id
     * @param string $type
     * @param null|string $label
     * @param array $config
     * @param array|null $secure_config
     * @return instance
     */
    public static function create(int $user_id, string $type, ?string $label, array $config, ?array $secure_config = null): self {
        $entity = new instance_entity([
            'user_id' => $user_id,
            'type' => $type,
            'label' => $label,
            'config' => json_encode((object)$config),
        ]);
        $entity->save();
        mfa_instance_created::create_event($entity)->trigger();

        $model = new self($entity);

        if ($secure_config !== null) {
            $model->set_encrypted_attribute('secure_config', json_encode($secure_config));
        }

        return $model;
    }

    /**
     * Delete the instance.
     *
     * @return void
     */
    public function delete(): void {
        $event_delete_factor = mfa_instance_deleted::create_event($this);
        $this->entity->delete();
        $event_delete_factor->trigger();
    }

    /**
     * Get data for listing frontend.
     *
     * @return array
     */
    public function format_for_listing(): array {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'factor' => $this->type,
            'factor_name' => $this->factor->get_display_name(),
            'created_at_formatted' => userdate($this->created_at, get_string('strftimedate', 'langconfig')),
        ];
    }

    /**
     * Get the associated factor class.
     *
     * @return factor
     */
    protected function get_factor(): factor {
        if ($this->factor_instance) {
            return $this->factor_instance;
        }
        $this->factor_instance = (new factor_factory())->get($this->type);
        return $this->factor_instance;
    }

    /**
     * Get the configuration data (factor specific).
     *
     * @return array
     */
    protected function get_config(): array {
        return json_decode($this->entity->config, true);
    }

    /**
     * Get the secure configuration data (factor specific).
     * This data is stored encrypted, and will return an empty array if unable to be read.
     *
     * @return array
     */
    protected function get_secure_config(): array {
        $config = $this->get_encrypted_attribute('secure_config');
        return empty($config) ? [] : json_decode($config, true);
    }
}
