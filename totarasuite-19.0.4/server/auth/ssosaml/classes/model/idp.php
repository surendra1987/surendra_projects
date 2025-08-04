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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model;

use auth_ssosaml\entity\idp as idp_entity;
use auth_ssosaml\event\certificate_regenerated;
use auth_ssosaml\exception\duplicate_idp_entity_id_exception;
use auth_ssosaml\model\idp\config;
use auth_ssosaml\model\idp\config\certificates;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\assertion_manager;
use auth_ssosaml\provider\logging\factory as logger_factory;
use auth_ssosaml\provider\session_manager;
use context;
use context_system;
use core\orm\entity\encrypted_model;

/**
 * Model class for an Identity Provider(IdP) instance
 *
 * @property-read int $id
 * @property-read metadata $metadata
 * @property-read string $label
 * @property-read string $idp_user_id_field
 * @property-read string $totara_user_id_field
 * @property-read array $field_mapping_config
 * @property-read bool $create_users
 * @property-read bool $status
 * @property-read bool $debug
 * @property-read array $saml_config
 * @property-read config $sp_config
 * @property-read array $certificates
 * @property-read bool $logout_idp When enabled, request to log out of the IdP will be made
 * @property-read string|null $logout_url Url to redirect to after loggging out of IdP
 * @property-read int $autolink_users
 * @property-read bool $login_hide
 * @property-read string $autolink_users_enum
 * @package auth_ssosaml\model
 */
class idp extends encrypted_model {
    /**
     * Indicates there's no autolinking, all users must be auth-type of SAML.
     */
    public const AUTOLINK_NONE = 0;

    /**
     * Indicates autolinking will work with no confirmation.
     */
    public const AUTOLINK_NO_CONFIRMATION = 1;

    /**
     * Indicates autolinking can work with email confirmation only.
     */
    public const AUTOLINK_WITH_CONFIRMATION = 2;

    /**
     * Direct properties that can be updated with simple values.
     *
     * Value is the default at create.
     *
     * @var array|string[]
     */
    protected static array $safe_update_fields = [
        'label' => null,
        'idp_user_id_field' => null,
        'totara_user_id_field' => null,
        'status' => false,
        'debug' => false,
        'create_users' => false,
        'autolink_users' => self::AUTOLINK_NONE,
        'login_hide' => false,
    ];

    /**
     * Metadata cache.
     *
     * @var metadata
     */
    protected metadata $metadata;

    /**
     * Fields that are stored encrypted.
     *
     * @var string[]
     */
    protected $encrypted_attribute_list = [
        'certificates',
    ];

    /**
     * Fields from the metadata.
     *
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'metadata',
        'field_mapping_config',
        'saml_config',
        'sp_config',
        'certificates',
        'autolink_users_enum',
    ];

    /**
     * Fields that are directly accessed from the entity.
     *
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'label',
        'idp_user_id_field',
        'totara_user_id_field',
        'status',
        'debug',
        'logout_idp',
        'logout_url',
        'create_users',
        'autolink_users',
        'login_hide',
    ];

    /**
     * Field Mapping config
     * @var array
     *      @type string $delimiter Delimiter to join multi-value attributes
     *      @type array[] $mapped_user_fields Collection of mapped user fields
     */
    private array $field_mapping_config;

    /**
     * @var array Decoded saml config from the entity
     */
    private array $persisted_saml_config;

    /**
     * @var config
     */
    private config $sp_config;


    /**
     * Create a new Identity Provider configuration.
     *
     * @param array $properties
     * @param array $saml_config
     * @return static
     */
    public static function create(array $properties, array $saml_config): self {
        $idp_metadata = metadata::none()
            ->set($properties['metadata'] ?? [])
            ->fetch_xml();

        if ($idp_metadata->source !== metadata::SOURCE_NONE) {
            $idp_metadata->validate();
        }

        $saml_config = config::filter_idp_defaults($saml_config);

        $attributes = [
            'metadata' => json_encode($idp_metadata),
            'saml_config' => json_encode($saml_config),
        ];

        // Field mapping configuration
        $field_mapping_config = [];
        if (isset($properties['field_mapping_config'])) {
            $input = $properties['field_mapping_config'];
            if (!empty($input['field_maps'])) {
                $field_mapping_config['field_maps'] = $input['field_maps'];
            }
            if (!empty($input['delimiter'])) {
                $field_mapping_config['delimiter'] = $input['delimiter'];
            }
        }

        if (isset($properties['logout_url'])) {
            $attributes['logout_url'] = clean_param($properties['logout_url'], PARAM_URL);
            if (empty($attributes['logout_url'])) {
                $attributes['logout_url'] = null;
            }
        }

        $attributes['logout_idp'] = $properties['logout_idp'] ?? true;

        foreach (self::$safe_update_fields as $key => $default_value) {
            $attributes[$key] = $properties[$key] ?? $default_value;
        }

        // auto-map user identifier
        if (isset($attributes['totara_user_id_field']) || isset($attributes['idp_user_id_field'])) {
            $field_mapping_config = self::map_user_identifier(
                $attributes['totara_user_id_field'] ?? null,
                $attributes['idp_user_id_field'] ?? null,
                $field_mapping_config
            );
        }

        if (!empty($field_mapping_config)) {
            $attributes['field_mapping_config'] = json_encode($field_mapping_config);
        }

        $idp = (new idp_entity($attributes))->save();

        $model = new self($idp);

        // Save the certificates
        $model->set_encrypted_attribute('certificates', certificates::create_certificates());

        return $model;
    }

    /**
     * Get an IdP by the entity id
     *
     * @param string $entity_id
     * @param bool $only_active restrict to active IdPs
     * @return idp
     */
    public static function get_by_issuer(string $entity_id, bool $only_active = true): idp {
        $query = idp_entity::repository()->where('idp_entity_id', $entity_id);

        if ($only_active) {
            $query->where('status', true);
        }

        $idp_entity = $query->order_by('id')->first_or_fail();

        return idp::load_by_entity($idp_entity);
    }

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return idp_entity::class;
    }

    /**
     * Delete the Idp.
     *
     * @return void
     */
    public function delete(): void {
        $this->entity->delete();
    }

    /**
     * Get the context relevant to the model.
     *
     * @return context
     */
    public function get_context(): context {
        return context_system::instance();
    }

    /**
     * Update an existing Identity Provider configuration.
     *
     * @param array $properties
     * @param array $saml_config
     * @return $this
     */
    public function update(array $properties, array $saml_config): self {
        // Update only what is provided in the payload
        $metadata_attributes = $properties['metadata'] ?? null;
        if (is_array($metadata_attributes)) {
            $metadata = $this->get_metadata()
                ->set($metadata_attributes)
                ->fetch_xml()
                ->validate();

            $this->entity->metadata = json_encode($metadata);
            $this->set_idp_entity_id($metadata->entity_id);
        }

        if (!empty($saml_config)) {
            $saml_config = config::filter_idp_defaults($saml_config);
            $this->entity->saml_config = json_encode($saml_config);
        }

        $mapping_needs_update = isset($properties['totara_user_id_field']) || isset($properties['idp_user_id_field']);

        // Field mapping configuration
        if (isset($properties['field_mapping_config'])) {
            $mapping_needs_update = true;
            $field_mapping_config = [];
            $input = $properties['field_mapping_config'];
            if (!empty($input['field_maps'])) {
                $field_mapping_config['field_maps'] = $input['field_maps'];
            }
            if (!empty($input['delimiter'])) {
                $field_mapping_config['delimiter'] = $input['delimiter'];
            }
        } else if ($mapping_needs_update) {
            $field_mapping_config = json_decode($this->entity->field_mapping_config ?? '{}', true);
        }

        if (array_key_exists('debug', $properties)) {
            if ($this->entity->debug && !$properties['debug']) {
                logger_factory::get_logger($this)->clear();
            }
        }

        foreach (self::$safe_update_fields as $key => $default_value) {
            if (array_key_exists($key, $properties)) {
                $this->entity->{$key} = $properties[$key];
            }
        }

        // auto-map user identifier, if any of the relevant fields are changing
        if ($mapping_needs_update) {
            $field_mapping_config = self::map_user_identifier(
                $this->entity->totara_user_id_field,
                $this->entity->idp_user_id_field,
                $field_mapping_config
            );

            $this->entity->field_mapping_config = empty($field_mapping_config) ? null : json_encode($field_mapping_config);
        }

        if (array_key_exists('logout_url', $properties)) {
            $logout_url = clean_param($properties['logout_url'], PARAM_URL);
            $this->entity->logout_url = $logout_url ?: null;
        }

        if (array_key_exists('logout_idp', $properties)) {
            $this->entity->logout_idp = $properties['logout_idp'] ?? true;
        }

        $this->entity->save();

        // Return a new instance of ourselves
        return new self($this->entity);
    }

    /**
     * Auto-map user identifier
     *
     * @param string|null $totara_field
     * @param string|null $idp_field
     * @param array $config Existing field mapping config
     * @return string New field mapping
     */
    protected static function map_user_identifier(?string $totara_field, ?string $idp_field, array $config): array {
        if (!$totara_field || !$idp_field) {
            return $config;
        }
        // remove any existing mapping for the user identifier field
        $config['field_maps'] =
            array_filter($config['field_maps'] ?? [], function ($map) use ($totara_field) {
                return $map['internal'] !== $totara_field;
            });
        // add a mapping for the user identifier
        array_unshift($config['field_maps'], [
            'internal' => $totara_field,
            'external' => $idp_field,
            'update' => 'CREATE'
        ]);
        return $config;
    }

    /**
     * Set the IdP entity_id from the metadata when updating
     *
     * @param string $entity_id
     * @return self
     * @throws duplicate_idp_entity_id_exception When entity_id is used in an existing IdP
     */
    private function set_idp_entity_id(string $entity_id): self {
        // Find existing IdP configuration with entity_id
        $existing_idps = idp_entity::repository()
            ->where('id', '!=', $this->entity->id)
            ->where('idp_entity_id', $entity_id)
            ->count();
        if ($existing_idps > 0) {
            throw new duplicate_idp_entity_id_exception();
        }

        $this->entity->idp_entity_id = $entity_id;
        return $this;
    }

    /**
     * Create a new set of certificates.
     *
     * @return $this
     */
    public function regenerate_certificates(): self {
        $this->set_encrypted_attribute('certificates', certificates::create_certificates());

        // Return a new instance of ourselves
        $idp = new self($this->entity);

        // Note that this certificate was regenerated
        certificate_regenerated::create_from_idp($idp)->trigger();
        return $idp;
    }

    /**
     * Get session manager for IdP
     *
     * @return session_manager
     */
    public function get_session_manager(): session_manager {
        return (new session_manager($this->id))->set_test_mode($this->get_sp_config()->test_mode);
    }

    /**
     * @return assertion_manager
     */
    public function get_assertion_manager(): assertion_manager {
        return new assertion_manager($this->id);
    }

    /**
     * @return array
     */
    protected function get_field_mapping_config(): array {
        if (!isset($this->field_mapping_config)) {
            $default = [
                'delimiter' => ',',
                'field_maps' => [],
            ];

            $field_mapping_config = !empty($this->entity->field_mapping_config)
                ? json_decode($this->entity->field_mapping_config, true)
                : $default;

            $this->field_mapping_config = array_merge($default, $field_mapping_config);
        }

        return $this->field_mapping_config;
    }

    /**
     * Get metadata model.
     *
     * @return metadata
     */
    protected function get_metadata(): metadata {
        if (!isset($this->metadata)) {
            $this->metadata = metadata::from_serialized($this->entity->metadata);
        }
        return $this->metadata;
    }

    /**
     * Refresh metadata if it is loaded remotely.
     *
     * @return void
     */
    public function refresh_metadata(): void {
        $metadata = $this->get_metadata();
        if ($metadata->source == metadata::SOURCE_URL) {
            $metadata = $metadata->fetch_xml(true)->validate();
            $this->entity->metadata = json_encode($metadata);
            $this->entity->save();
        }
    }

    /**
     * Overwritten values for the sp_config defaults.
     *
     * @return array
     */
    protected function get_saml_config(): array {
        if (!isset($this->persisted_saml_config)) {
            $this->persisted_saml_config = json_decode($this->entity->saml_config, true) ?? [];
        }

        return $this->persisted_saml_config;
    }

    /**
     * Fetch the sp config wrapper for this Idp.
     *
     * @return config
     */
    protected function get_sp_config(): config {
        if (!isset($this->sp_config)) {
            $this->sp_config = new config($this->id, $this->saml_config, $this->certificates);
        }

        return $this->sp_config;
    }

    /**
     * @return array
     */
    protected function get_certificates(): array {
        // Certificates are stored encrypted, so must be decoded
        $certificates = $this->get_encrypted_attribute('certificates');

        if ($certificates === false) {
            throw new \coding_exception('Could not decrypt the SAML certificates.');
        }

        return certificates::get_certificates($certificates);
    }

    /**
     * @return string
     */
    protected function get_autolink_users_enum(): string {
        switch ($this->entity->autolink_users) {
            case self::AUTOLINK_WITH_CONFIRMATION:
                return 'LINK_WITH_CONFIRMATION';
            case self::AUTOLINK_NO_CONFIRMATION:
                return 'LINK_NO_CONFIRMATION';
            default:
                return 'NO_LINK';
        }
    }
}
