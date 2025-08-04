<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\entity;

use core\orm\entity\entity;

/**
 * AI Interaction log entity
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $interaction
 * @property-read string $request
 * @property-read string $response
 * @property-read string $plugin
 * @property-read string $feature
 * @property-read string $configuration
 * @property-read int $created_at
 */
class interaction_log extends entity {

    public const TABLE = 'ai_interaction_log';

    public const CREATED_TIMESTAMP = 'created_at';
}
