<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\controller;

use totara_core\advanced_feature;
use totara_mvc\tui_view;
use context_system;
use totara_mvc\admin_controller;

/**
* Admin controller for Webhook index page.
*/
class totara_webhook_index extends admin_controller {
    /**
    * @var string
    */
    protected $admin_external_page_name = 'totara_webhook_totara_webhook';

    /**
     * @inheritDoc
     */
    protected function setup_context(): \context {
        return context_system::instance();
    }

    /**
     * @return tui_view
     */
    public function action(): tui_view {
        advanced_feature::require('totara_webhook');
        return tui_view::create(
            'totara_webhook/pages/Index',
            [
                'canManage' => has_capability('totara/webhook:managetotara_webhooks', $this->get_context()),
            ]
        );
    }
}