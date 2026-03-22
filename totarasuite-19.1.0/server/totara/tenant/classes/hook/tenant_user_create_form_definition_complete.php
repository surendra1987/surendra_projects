<?php
/**
 * This file is part of Totara Learn
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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package totara_tenant
 */

namespace totara_tenant\hook;

use totara_tenant\form\user_create;

/**
 * Tenant user create form definition complete hook.
 *
 * This hook is called at the end of the tenant user create form definition, prior to data being set.
 *
 * @package totara_tenant
 */
class tenant_user_create_form_definition_complete extends \totara_core\hook\base {
    /**
     * The Tenant user create form instance.
     * @var user_create
     */
    public $form;

    /**
     * Custom data belonging to the form.
     * This is protected on the form thus needs to be provided.
     * @var mixed[]
     */
    public $customdata;

    /**
     * The create_user_definition_complete constructor.
     *
     * @param user_create $form
     * @param mixed[] $customdata
     */
    public function __construct(user_create $form, array $customdata) {
        $this->form = $form;
        $this->customdata = $customdata;
    }
}