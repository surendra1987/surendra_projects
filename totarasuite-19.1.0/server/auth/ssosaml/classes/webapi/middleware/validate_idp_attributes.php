<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\data_provider\user_fields;
use auth_ssosaml\exception\invalid_payload;
use auth_ssosaml\exception\validation_error;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\metadata;
use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Middleware to validate the IdP input
 *
 */
class validate_idp_attributes implements middleware {
    /**
     * Execute the middleware, and if the capability check fails throw an exception.
     *
     * @param payload $payload
     * @param Closure $next
     * @return result
     */
    public function handle(payload $payload, Closure $next): result {
        $input = $payload->get_variable('input');
        if (empty($input)) {
            throw new invalid_payload('input');
        }
        if (empty($input['attributes'])) {
            throw new invalid_payload('input.attributes');
        }

        $attributes = $input['attributes'];

        // If metadata is updated, verify the source => attribute mappings behave
        if (empty($attributes['metadata'])) {
            throw new invalid_payload('input.attributes.metadata');
        }
        $attributes['metadata'] = $this->validate_metadata($attributes['metadata']);

        // If the field mapping config is provided, validate the fields don't create weird loops.
        if (!empty($attributes['field_mapping_config'])) {
            $attributes = $this->validate_field_maps($attributes);
        }

        if (isset($attributes['autolink_users'])) {
            $attributes['autolink_users'] = $this->validate_autolink_users($attributes['autolink_users']);
        }

        $input['attributes'] = $attributes;
        
        $saml_config = $input['saml_config'] ?? null;
        if ($saml_config) {
            if (strlen($saml_config['entity_id'] ?? '') > 1024) {
                throw validation_error::make('error:entity_id_max', 1024);
            }
        }

        $payload->set_variable('input', $input);

        return $next($payload);
    }

    /**
     * Validate the provided field mappings are correct/valid.
     *
     * @param array $attributes
     * @return void
     */
    private function validate_field_maps(array $attributes): array {
        $field_mapping_config = &$attributes['field_mapping_config'];

        $idp_user_id_field = $attributes['idp_user_id_field'];
        $totara_user_id_field = $attributes['totara_user_id_field'];

        $user_id_fields = user_fields::get_user_id_fields();
        if (!isset($user_id_fields[$totara_user_id_field])) {
            throw validation_error::make('error:mapping_internal_invalid', $totara_user_id_field);
        }

        $regular_map_fields = user_fields::get_info();

        // Make sure the totara_field entries are legit
        foreach ($field_mapping_config['field_maps'] as $i => $field_map) {
            if (empty($field_map['external'])) {
                throw new \coding_exception('Missing external in map');
            }
            if (empty($field_map['internal'])) {
                throw new \coding_exception('Missing internal in map');
            }
            if (empty($field_map['update'])) {
                throw new \coding_exception('Missing update in map');
            }
            if (!in_array($field_map['update'], ['LOGIN', 'CREATE'])) {
                throw new \coding_exception('Invalid update entry, must be LOGIN or CREATE');
            }
            if (strlen($field_map['external']) > 255) {
                throw validation_error::make('error:mapping_field_too_long', ['field' => $field_map['internal'], 'max' => 255]);
            }
            if (!isset($regular_map_fields[$field_map['internal']])) {
                throw validation_error::make('error:mapping_internal_invalid', $field_map['internal']);
            }

            // The identity field must be set to CREATE only and must match the mapping value
            if ($field_map['internal'] === $totara_user_id_field) {
                if ($idp_user_id_field !== $field_map['external']) {
                    throw validation_error::make('error:user_identifier_invalid_map');
                }

                // In this case we force the $update value to CREATE as the FE will handle it normally
                $field_mapping_config['field_maps'][$i]['update'] = 'CREATE';
            }

            // Special case, the username field can only be set to create, not login.
            if ($field_map['internal'] === 'username' && $field_map['update'] !== 'CREATE') {
                $field_mapping_config['field_maps'][$i]['update'] = 'CREATE';
            }
        }

        return $attributes;
    }

    /**
     * @param array $metadata
     * @return array
     */
    private function validate_metadata(array $metadata): array {
        $source = $metadata['source'] ?? null;

        if (empty($source) || !metadata::valid_source($source)) {
            throw new invalid_payload('input.metadata.source');
        }
        if ($source === metadata::SOURCE_URL && empty($metadata['url'])) {
            throw validation_error::make('error:metadata_url');
        }
        if ($source === metadata::SOURCE_XML && empty($metadata['xml'])) {
            throw validation_error::make('error:metadata_xml');
        }

        return $metadata;
    }

    /**
     * @param mixed $autolink_users
     * @return int
     */
    private function validate_autolink_users($autolink_users): int {
        switch ($autolink_users) {
            case 'LINK_WITH_CONFIRMATION':
                return idp::AUTOLINK_WITH_CONFIRMATION;
            case 'LINK_NO_CONFIRMATION':
                return idp::AUTOLINK_NO_CONFIRMATION;
            case 'NO_LINK':
                return idp::AUTOLINK_NONE;
        }
        throw new \coding_exception('Invalid option provided for autolink_users');
    }
}