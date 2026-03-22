<?php
/**
* This file is part of Totara Learn
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
* @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
* @package contentmarketplace_linkedin
*/

namespace contentmarketplace_linkedin\workflow\mod_contentmarketplace\create_marketplace_activity;

use contentmarketplace_linkedin\config;
use core\orm\query\builder;
use moodle_url;
use totara_contentmarketplace\explorer;
use totara_contentmarketplace\workflow\marketplace_workflow;

class linkedin extends marketplace_workflow {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('workflow:name', 'mod_contentmarketplace');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        return get_string('workflow:description', 'mod_contentmarketplace');
    }

    /**
     * @inheritDoc
     */
    public function can_access(): bool {
        $db = builder::get_db();
        $records = $db->get_records('marketplace_linkedin_learning_object',null,'','*', 0, 1);

        return !empty(config::client_id())
            && !empty(config::client_secret())
            && !empty($records)
            && parent::can_access();
    }

    /**
     * @inheritDoc
     */
    protected function get_workflow_url(): moodle_url {
        $url = parent::get_workflow_url();
        $url->params(array_merge(
            $this->manager->get_params(),
            [
                'mode' => explorer::MODE_ADD_ACTIVITY,
            ]
        ));
        return $url;
    }

}