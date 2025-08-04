<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Aleksandr Baishev <aleksandr.baishev@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\controllers\profile;

use moodle_url;
use pathway_manual\controllers\rate_competencies;
use pathway_manual\models\user_competencies;
use totara_competency\aggregation_users_table;
use totara_competency\helpers\capability_helper;
use totara_mvc\tui_view;


class index extends base {

    /**
     * @param array $params
     * @return moodle_url
     */
    public static function get_url(array $params = []): moodle_url {
        return new moodle_url(self::PROFILE_BASE_URL, $params);
    }

    public function action() {
        $this->get_page()->set_url(
            $this->set_url(self::PROFILE_BASE_URL, ['user_id' => $this->user->id])
                ->url
        );

        // Add breadcrumbs.
        $this->add_navigation();

        $props = [
            'self-assignment-url' => (string) $this->get_user_assignment_url(),
            'user-id' => (int) $this->user->id,
            'is-mine' => $this->is_for_current_user(),
            'base-url' => (string) $this->get_base_url(),
            'can-assign' => capability_helper::can_assign($this->user->id, $this->context),
            'can-rate-competencies' => user_competencies::can_rate_competencies($this->user->id),
            'toast-message' => user_assignment::get_toast_message_from_url() ?? rate_competencies::get_toast_message_from_url(),
            'has-pending-aggregation' => (new aggregation_users_table())->has_pending_aggregation($this->user->id),
        ];

        return tui_view::create('totara_competency/pages/CompetencyProfile', $props)
            ->set_title(get_string('user_competency_profile', 'totara_competency',  $this->user->fullname));
    }

}
