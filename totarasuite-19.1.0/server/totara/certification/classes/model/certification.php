<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_certification
 */

namespace totara_certification\model;

use core\orm\entity\model;
use totara_certification\entity\certification as entity;
use totara_program\program;
use totara_program\entity\program as program_entity;

/**
 * Certification entity
 *
 * @property-read int $id
 * @property-read int $learningcomptype
 * @property-read string $activeperiod
 * @property-read string $minimumactiveperiod
 * @property-read string $windowperiod
 * @property-read int $recertifydatetype
 * @property-read int $timemodified
 */
class certification extends model {

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'learningcomptype',
        'activeperiod',
        'minimumactiveperiod',
        'windowperiod',
        'recertifydatetype',
        'timemodified',
    ];

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @param program $origin_program
     * @param bool $shallow_clone
     * @return self
     * @throws \coding_exception
     */
    public function clone(program $origin_program, bool $shallow_clone = false): self {
        $cloned = new entity();

        if (!$shallow_clone) {
            $cloned->recertifydatetype = $this->recertifydatetype;
            $cloned->activeperiod = $this->activeperiod;
            $cloned->minimumactiveperiod = $this->minimumactiveperiod;
            $cloned->learningcomptype = $this->learningcomptype;
            $cloned->windowperiod = $this->windowperiod;
        } else {
            $cloned->learningcomptype = CERTIFTYPE_PROGRAM;
            $cloned->activeperiod = '1 year';
            $cloned->windowperiod = '1 month';
            $cloned->recertifydatetype = CERTIFRECERT_EXPIRY;
        }
        $cloned_entity = $cloned->save();

        if (!$origin_program->is_certif()) {
            program_entity::repository()->update_record(['certifid' => $cloned_entity->id, 'id' => $origin_program->id]);
        }

        return self::load_by_entity($cloned_entity);
    }
}