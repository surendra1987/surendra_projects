<?php
/**
 * This file is part of Totara Learn
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
 * @author Arshad Anwer <arshad.anwer@totara.com>
 * @package core_form
 */

global $CFG;
require_once($CFG->libdir . '/form/tags.php');

class MoodleQuickForm_tagsaiautocomplete extends MoodleQuickForm_tags {
    /**
     * Returns HTML for select form element.
     *
     * @return string
     */
    public function toHtml() {
        global $PAGE;
        global $OUTPUT;

        $manage_link = '';
        if (has_capability('moodle/tag:manage', context_system::instance()) && $this->showstandard) {
            $url = new moodle_url('/tag/manage.php', array('tc' => $this->get_tag_collection()));
            $manage_link = ' ' . $OUTPUT->action_link($url, get_string('managetags', 'tag'));
        }
        $this->_generateId();
        $id = $this->getAttribute('id');

        $options = new stdClass();
        $options->ai_suggested_tags_string = get_string('ai_suggested_tags', 'tag');
        $options->ai_tags_experimental_stirng = get_string('ai_tags_experimental', 'tag');
        $options->generate_tags_string = get_string('generate_tags', 'tag');
        $options->re_generate_tags_string = get_string('regenerate_tags', 'tag');
        $options->footer_string = get_string('ai_tag_footer_label', 'tag');
        $options->flex_icon_loading = $OUTPUT->flex_icon('show', array('alt' => 'tags loading'));
        $options->manage_link = $manage_link;

        if (!$this->isFrozen()) {
            $PAGE->requires->js_call_amd(
                'core/form_tags_ai_autocomplete',
                'enhance',
                [
                    '#' . $id,
                    $this->tags,
                    $this->ajax,
                    $this->placeholder, $this->casesensitive, $this->showsuggestions, $options]
            );
        }

        return MoodleQuickForm_select::toHtml();
    }
}
