// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Clean caches after user login in order to prevent cache poisoning.
 * @module      core/storage_validation
 * @copyright   2024 Raquel Ortega <raquel.ortega@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/localstorage', 'core/sessionstorage'], function (LocalStorage, SessionStorage) {
    return {
        /**
         * module initialisation method called by php js_init_call()
         */
        init: function (userCurrentLogin) {
            var sUserLoginTime = Number(LocalStorage.get('sUserLogintime'));

            if (userCurrentLogin !== sUserLoginTime) {
                LocalStorage.clean();
                SessionStorage.clean();
                LocalStorage.set('sUserLogintime', Number(userCurrentLogin).toString());
            }
        }
    };
});