<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <naveet.singh@totara.com>
 * @package totara_reportbuilder
 */

use core_ai\subsystem;

class rb_source_ai_interaction_log extends rb_base_source {
    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        if ($groupid instanceof rb_global_restriction_set) {
            throw new coding_exception('Wrong parameter orders detected during report source instantiation.');
        }
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        $this->base = '{ai_interaction_log}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_ai_interaction_log');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_ai_interaction_log');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_ai_interaction_log');

        parent::__construct();
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return false;
    }

    /**
     * Define AI interaction log join
     * @return array
     */
    protected function define_joinlist() {
        $joinlist = array();

        $this->add_core_user_tables($joinlist, 'base', 'user_id');

        return $joinlist;
    }

    /**
     * define column options
     * @return array
     */
    protected function define_columnoptions() {
        $columnoptions = array(
            new rb_column_option(
                'base',
                'interaction',
                get_string('interaction', 'rb_source_ai_interaction_log'),
                'base.interaction',
                array(
                    'dbdatatype' => 'text',
                    'outputformat' => 'text',
                    'displayfunc' => 'ai_interaction_name'
                )
            ),
            new rb_column_option(
                'base',
                'request',
                get_string('request', 'rb_source_ai_interaction_log'),
                'base.request',
                array('displayfunc' => 'plaintext')
            ),
            new rb_column_option(
                'base',
                'response',
                get_string('response', 'rb_source_ai_interaction_log'),
                'base.response',
                array('displayfunc' => 'plaintext')
            ),
            new rb_column_option(
                'base',
                'plugin',
                get_string('plugin', 'rb_source_ai_interaction_log'),
                'base.plugin',
                array(
                    'dbdatatype' => 'text',
                    'outputformat' => 'text',
                    'displayfunc' => 'ai_plugin_name'
                )
            ),
            new rb_column_option(
                'base',
                'feature',
                get_string('feature', 'rb_source_ai_interaction_log'),
                'base.feature',
                array(
                    'dbdatatype' => 'text',
                    'outputformat' => 'text',
                    'displayfunc' => 'ai_feature_name'
                )
            ),
            new rb_column_option(
                'base',
                'configuration',
                get_string('configuration', 'rb_source_ai_interaction_log'),
                'base.configuration',
                array('displayfunc' => 'plaintext')
            ),
            new rb_column_option(
                'base',
                'created_at',
                get_string('created_at', 'rb_source_ai_interaction_log'),
                'base.created_at',
                array('displayfunc' => 'nice_datetime')
            ),
        );
        $this->add_core_user_columns($columnoptions);
        return $columnoptions;
    }

    /**
     * define default column options
     * @return array
     */
    protected function define_defaultcolumns() {
        return array(
            array(
                'type' => 'user',
                'value' => 'namelink',
            ),
            array(
                'type' => 'base',
                'value' => 'interaction',
            ),
            array(
                'type' => 'base',
                'value' => 'request',
            ),
            array(
                'type' => 'base',
                'value' => 'response',
            ),
            array(
                'type' => 'base',
                'value' => 'plugin',
            ),
            array(
                'type' => 'base',
                'value' => 'feature',
            ),
            array(
                'type' => 'base',
                'value' => 'configuration',
            ),
            array(
                'type' => 'base',
                'value' => 'created_at',
            )
        );
    }

    /**
     * define filter options
     * @return array
     */
    protected function define_filteroptions() {
        $filteroptions = array();

        $ai_interactions = [];
        $get_ai_interactions = subsystem::get_interactions();
        foreach ($get_ai_interactions as $interaction) {
            $ai_interactions[$interaction] = $interaction::get_name();
        }

        $filteroptions[] = new rb_filter_option(
            'base',
            'interaction',
            get_string('interaction', 'rb_source_ai_interaction_log'),
            'select',
            array(
                'selectchoices' => $ai_interactions,
                'simplemode' => true
            )
        );

        $ai_plugins = [];
        $get_ai_plugins = subsystem::get_ai_plugins();

        foreach ($get_ai_plugins as $plugin) {
            $ai_plugins[$plugin->name] = $plugin->displayname;
        }

        $filteroptions[] = new rb_filter_option(
            'base',
            'plugin',
            get_string('plugin', 'rb_source_ai_interaction_log'),
            'select',
            array(
                'selectchoices' => $ai_plugins,
                'simplemode' => true
            )
        );

        $ai_features = [];
        $get_ai_features = subsystem::get_features();
        foreach ($get_ai_features as $feature) {
            $ai_features[$feature] = $feature::get_name();
        }

        $filteroptions[] = new rb_filter_option(
            'base',
            'feature',
            get_string('feature', 'rb_source_ai_interaction_log'),
            'select',
            array(
                'selectchoices' => $ai_features,
                'simplemode' => true
            )
        );

        $this->add_core_user_filters($filteroptions);

        return $filteroptions;
    }

    /**
     * define default filter options
     * @return array
     */
    protected function define_defaultfilters() {
        return array(
            array(
                'type' => 'base',
                'value' => 'interaction'
            ),
            array(
                'type' => 'base',
                'value' => 'plugin'
            ),
            array(
                'type' => 'base',
                'value' => 'feature'
            ),
        );
    }

    public function phpunit_column_test_expected_count($columnoption) {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        return 0;
    }
}
