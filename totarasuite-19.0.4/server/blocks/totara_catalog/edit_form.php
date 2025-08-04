<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package block_totara_catalog
 */

use totara_catalog\cache_handler;
use totara_catalog\catalog_retrieval;
use totara_catalog\local\query_helper;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class block_totara_catalog_edit_form extends block_edit_form {

    /**
     * @inheridDoc
     */
    protected function specific_definition($mform) {
        parent::specific_definition($mform);
        $mform->addElement('header', 'configheader', get_string('customblocksettings', 'block'));

        $mform->addElement('text', 'config_catalogurl', get_string('catalogurl', 'block_totara_catalog'));
        $mform->setType('config_catalogurl', PARAM_TEXT);
        $mform->addRule('config_catalogurl', null, 'required', null, 'client');

        $mform->addElement('text', 'config_collectiontitle', get_string('collectiontitle', 'block_totara_catalog'));
        $mform->setType('config_collectiontitle', PARAM_TEXT);
        $mform->setDefault('config_collectiontitle', get_string('explore_learning', 'block_totara_catalog'));

        $options = range(10, 20);
        $mform->addElement('select', 'config_numberofitems', get_string('numberofitems', 'block_totara_catalog'), $options);
        $mform->setDefault('config_numberofitems', 0);
    }

    /**
     * @inheridDoc
     */
    public function validation($data, $files) {
        global $CFG;

        $errors = parent::validation($data, $files);
        // Alert users with error message.
        if (!empty($data['config_catalogurl'])) {
            if (filter_var($data['config_catalogurl'], FILTER_VALIDATE_URL)) {
                $errors['config_catalogurl'] = get_string('urladdresserror', 'block_totara_catalog');
            }

            $url = new \moodle_url($data['config_catalogurl']);
            if ($CFG->catalogtype === 'explore' && $url->get_path() === '/totara/catalog/explore.php') {
                // nothing and expected
            } else if ($CFG->catalogtype === 'totara' && $url->get_path() === '/totara/catalog/index.php') {
                // nothing and expected
            } else {
                $errors['config_catalogurl'] = get_string('urlvalidationerror', 'block_totara_catalog');
            }

            $url_query = parse_url($url->out(false), PHP_URL_QUERY);
            if (is_null($url_query)) {
                $errors['config_catalogurl'] = get_string('urlnoquerystringerror', 'block_totara_catalog');
            }

            // Reset the catalog static caches.
            cache_handler::reset_all_caches();

            $params = query_helper::parse_input_to_params(['query_string' => $url_query]);
            $er = [];
            query_helper::validate_params($params, $er, new catalog_retrieval());
            if (!empty($er)) {
                $errors['config_catalogurl'] = implode(' ', $er);
            }
        }

        return $errors;
    }
}
