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

use moodle_url;
use totara_competency\entity\competency_achievement;
use totara_competency\models\assignment;

/**
 * Competency perform overview item data.
 */
class item {
    /**
     * @var competency_achievement competency achievement entity.
     */
    private competency_achievement $achievement;

    /**
     * Default constructor.
     *
     * @param competency_achievement $achievement competency achievement entity.
     */
    public function __construct(competency_achievement $achievement) {
        $this->achievement = $achievement;
    }

    /**
     * Returns the date the overview item was assigned to the user.
     *
     * @return int the date in seconds since the Epoch.
     */
    public function get_assignment_date(): int {
        return $this->achievement->assignment
            ->assignment_user()
            ->where('user_id', $this->achievement->user_id)
            ->one()
            ->created_at;
    }

    /**
     * Returns the achievement id.
     *
     * @return int the id.
     */
    public function get_achievement_id(): int {
        return $this->achievement->id;
    }

    /**
     * Returns the achievement date.
     *
     * @return int the date in seconds since the Epoch.
     */
    public function get_achievement_date(): int {
        return $this->achievement->last_aggregated;
    }

    /**
     * Returns the achievement level.
     *
     * @return string the achievement level.
     */
    public function get_achievement_level(): string {
        $value = $this->achievement->value;

        return $value
            ? $value->name
            : get_string('perform_overview_not_started', 'totara_competency');
    }

    /**
     * Returns the assignment.
     *
     * @return int the assignment.
     */
    public function get_assignment_id(): int {
        return $this->achievement->assignment_id;
    }

    /**
     * Returns the assignment type.
     *
     * @return string the type.
     */
    public function get_assignment_type(): string {
        return assignment::load_by_entity($this->achievement->assignment)
            ->progress_name;
    }

    /**
     * Returns the overview competency id.
     *
     * @return int the id.
     */
    public function get_competency_id(): int {
        return $this->achievement->competency->id;
    }

    /**
     * Returns the overview item description.
     *
     * @return ?string the description.
     */
    public function get_competency_description(): ?string {
        return $this->achievement->competency->description;
    }

    /**
     * Returns the overview item name.
     *
     * @return string the name.
     */
    public function get_competency_name(): string {
        return $this->achievement->competency->fullname;
    }

    /**
     * Returns the raw achievement entity associated with this item. This should
     * only be used for testing purposes.
     *
     * @return competency_achievement the entity.
     */
    public function get_entity(): competency_achievement {
        return $this->achievement;
    }

    /**
     * Returns the overview last update details.
     *
     * @return item_last_update the details.
     */
    public function get_last_update(): item_last_update {
        $value = $this->achievement->value;
        $desc = $value
            ? get_string(
                'perform_overview_last_update_description',
                'totara_competency',
                ['scale_value_name' => $value->name]
            )
            : get_string('perform_overview_last_update_description_not_started', 'totara_competency');

        return new item_last_update($this->achievement->last_aggregated, $desc);
    }

    /**
     * Returns the associated url.
     *
     * @return string the url.
     */
    public function get_url(): string {
        $url = new moodle_url(
            '/totara/competency/profile/details/index.php',
            [
                'competency_id' => $this->achievement->competency->id,
                'user_id' => $this->achievement->user_id
            ]
        );

        return $url->out(false);
    }

    /**
     * Returns the user id whose achievement this is.
     *
     * @return int the user id.
     */
    public function get_user_id(): int {
        return $this->achievement->user_id;
    }
}
