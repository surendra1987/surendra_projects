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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\event;

use auth_ssosaml\entity\idp as idp_entity;
use auth_ssosaml\model\idp as idp_model;
use core\event\base;
use moodle_url;

/**
 * Register a certificate being regenerated.
 */
class certificate_regenerated extends base {
    /**
     * Record a certificate regeneration for this IdP.
     *
     * @param idp_model $idp The IdP the certificate was generated for.
     * @return self
     */
    public static function create_from_idp(idp_model $idp): self {
        global $USER;

        $data = [
            'objectid' => $idp->id,
            'context' => $idp->get_context(),
            'userid' => $USER->id,
        ];

        /** @var static $event */
        $event = static::create($data);
        return $event;
    }

    /**
     * Name shown in the event log report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('certificate_regenerated_event', 'auth_ssosaml');
    }

    /**
     * Description shown in the event log report.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '$this->userid' regenerated the certificate for IdP with id '$this->objectid'.";
    }

    /**
     * Link in the event log report.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/auth/ssosaml/idp.php', ['id' => $this->objectid]);
    }

    /**
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = idp_entity::TABLE;
    }
}
