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

namespace auth_ssosaml\provider\process;

use auth_ssosaml\model\idp;
use auth_ssosaml\provider\factory;
use auth_ssosaml\provider\saml_contract;

/**
 * Process class for SAML login requests
 */
trait with_provider {
    /**
     * @var saml_contract
     */
    protected saml_contract $provider;

    /**
     * @param idp $idp
     * @return saml_contract
     */
    protected function get_provider(idp $idp): saml_contract {
        if (!isset($this->provider)) {
            $this->provider = factory::get_provider($idp);
        }

        return $this->provider;
    }

    /**
     * @param saml_contract|null $provider
     * @return $this
     */
    public function set_provider(?saml_contract $provider = null): self {
        $this->provider = $provider;
        return $this;
    }
}
