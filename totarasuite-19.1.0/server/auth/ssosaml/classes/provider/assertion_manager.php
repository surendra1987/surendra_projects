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

namespace auth_ssosaml\provider;

use auth_ssosaml\entity\assertion;

/**
 * Assertion manager is used to keep track of IdP-initiated assertions and make sure there is
 * no assertion reuse.s
 */
class assertion_manager {
    /**
     * @var int
     */
    protected int $idp_id;

    /**
     * @param int $idp_id
     */
    public function __construct(int $idp_id) {
        $this->idp_id = $idp_id;
    }

    /**
     * Confirm if the provided assertion is unique or not.
     *
     * @param string $assertion_id
     * @return bool
     */
    public function is_assertion_unique(string $assertion_id): bool {
        $count = assertion::repository()
            ->where('idp_id', $this->idp_id)
            ->where('assertion_id', $assertion_id)
            ->count();

        return $count === 0;
    }

    /**
     * Record a specific assertion has been used.
     *
     * @param string $assertion_id
     * @param int $expiry_date
     * @return void
     */
    public function record_assertion(string $assertion_id, int $expiry_date): void {
        $assertion = new assertion([
            'assertion_id' => $assertion_id,
            'assertion_expiry' => $expiry_date,
            'idp_id' => $this->idp_id,
        ]);

        $assertion->save();
    }

    /**
     * @param int|null $since How long ago we cleanup expired records.
     * @return void
     */
    public static function cleanup_expired(?int $since = null): void {
        assertion::repository()
            ->where('assertion_expiry', '<=', $since ?? time())
            ->delete();
    }
}
