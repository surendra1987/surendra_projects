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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

define([], function() {
    /**
     * Password unmasker.
     *
     * @param {string} id ID of element
     */
    const PasswordUnmask = function(id) {
        const input = document.getElementById(id);
        const show = document.getElementById(id + 'unmask');
        show.addEventListener('change', function(e) {
            e.stopPropagation();
            input.setAttribute('type', show.checked ? 'text' : 'password');
        });
    };

    return PasswordUnmask;
});
