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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

namespace mfa_totp;

use core\collection;
use core_mfa\factor as base_factor;
use core_mfa\data_provider\instance as instance_provider;
use core_mfa\model\instance;
use core_user;
use mfa_totp\entity\token as token_entity;

/**
 * TOTP (Authenticator app) factor.
 *
 * @package mfa_totp
 */
class factor extends base_factor {
    /** @inheritDoc */
    protected string $name = 'totp';

    /**
     * @var callable|null Function that will provide the timestamp used for totp calculation.
     */
    protected $time_provider;

    /** @inheritDoc */
    public function user_can_register(int $user_id): bool {
        return instance_provider::get_user_instances_for_factor($user_id, $this->name)->count() === 0;
    }

    /** @inheritDoc */
    public function has_verify_ui(): bool {
        return true;
    }

    /** @inheritDoc */
    public function has_register_ui(): bool {
        return true;
    }

    /** @inheritDoc */
    public function get_register_data(int $user_id): array {
        global $SITE;

        $user = core_user::get_user($user_id);

        $totp = new totp();
        $oath_uri = $totp->oath_uri($user, $SITE->shortname);

        return [
            'secret' => $totp->get_secret(),
            'qr_url' => $totp->qr_code($oath_uri),
        ];
    }

    /** @inheritDoc */
    public function verify(int $user_id, array $data, array $instances): bool {
        $used = token_entity::repository()
            ->where('user_id', $user_id)
            ->where('token', $data['token'])->exists();

        if ($used) {
            return false;
        }

        $instance = collection::new($instances)->find(function ($instance) use ($data) {
            /** @var instance $instance */
            $secret = $instance->secure_config['secret'] ?? null;
            $totp = new totp($secret);
            return $totp->validate($this->get_time(), $data['token']);
        });

        if (!$instance) {
            return false;
        }

        $token_entity = new token_entity(['user_id' => $user_id, 'token' => $data['token']]);
        $token_entity->save();
        return true;
    }

    /**
     * Return the time to use for calculations
     *
     * @return int
     */
    protected function get_time(): int {
        if (is_callable($this->time_provider)) {
            return call_user_func($this->time_provider, $this);
        }

        return time();
    }

    /**
     * Set the function used to provide the timestamp for TOTP calculations.
     * If null, time() will be used.
     *
     * Used when testing/debugging TOTP and calculating known responses.
     *
     * @param callable|null $provider
     * @return void
     */
    protected function set_time_provider(?callable $provider): void {
        $this->time_provider = $provider;
    }
}
