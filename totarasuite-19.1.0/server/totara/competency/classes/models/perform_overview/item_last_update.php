<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\models\perform_overview;

/**
 * Competency perform overview data.
 */
class item_last_update {
    /**
     * @var int date the overview item was last updated in seconds since the
     *      Epoch.
     */
    private int $date;

    /**
     * @var string|null description of what changed in the last update of the overview
     *      item.
     */
    private ?string $description;

    /**
     * Default constructor.
     *
     * @param int $date date the overview item was last updated in seconds since
     *        the Epoch.
     * @param ?string $desc what changed in the last update.
     */
    public function __construct(int $date, ?string $desc) {
        $this->date = $date;
        $this->description = $desc;
    }

    /**
     * Returns the date the overview item was updated.
     *
     * @return int the date in seconds since the Epoch.
     */
    public function get_date(): int {
        return $this->date;
    }

    /**
     * Returns a description of what changed since the last update.
     *
     * @return ?string the description.
     */
    public function get_description(): ?string {
        return $this->description;
    }
}
