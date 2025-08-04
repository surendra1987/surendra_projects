<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package totara_playlist
 */
namespace totara_playlist\webapi\resolver\mutation;

use core\json_editor\helper\document_helper;
use core\webapi\execution_context;
use core\webapi\middleware\clean_content_format;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use totara_engage\access\access;
use totara_engage\share\manager as share_manager;
use totara_engage\share\recipient\manager as recipient_manager;
use totara_engage\webapi\middleware\require_valid_recipients;
use totara_playlist\event\playlist_updated;
use totara_playlist\playlist;
use core\webapi\middleware\clean_editor_content;

/**
 * Mutation resolver for totara_playlist_update
 */
class update extends mutation_resolver {
    /**
     * @param array             $args
     * @param execution_context $ec
     *
     * @return playlist
     */
    public static function resolve(array $args, execution_context $ec): playlist {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(\context_user::instance($USER->id));
        }

        $playlist = playlist::from_id($args['id'], true);

        $name = $args['name'] ?? $playlist->get_name(false);
        // 'clean_content_format' middleware always return FORMAT_JSON_EDITOR value if:
        // - 'summary_format' is set to null
        // - 'summary_format' is not set, 'clean_content_format' middleware will set 'summary_format' and set value to FORMAT_JSON_EDITOR
        // if 'summary_format' is set, and it's different to FORMAT_JSON_EDITOR, 'clean_content_format' middleware throws the error.
        // We must convert 'summary' value to FORMAT_JSON_EDITOR format, if it is a plain string.
        $summary = $args['summary'] ?? null;
        if (!is_null($summary) && !document_helper::looks_like_json($summary, true)) {
            $raw_summary = document_helper::create_document_from_text($summary);
            $summary = document_helper::clean_json_document(
                document_helper::json_encode_document($raw_summary)
            );
        }
        // $args['summary_format'] "must" be set by 'clean_content_format' middleware to FORMAT_JSON_EDITOR,
        // but we will not rely on it
        $summary_format = $args['summary_format'] ?? $playlist->get_summaryformat();
        $access = access::get_value($args['access'] ?? $playlist->get_access_code());

        $playlist->update($name, $access, $summary, $summary_format, $USER->id);

        // Add/remove topics.
        if (!empty($args['topics'])) {
            // Remove all the current topics first, excluding those that we will be adding.
            $playlist->remove_topics_by_ids($args['topics']);
            $playlist->add_topics_by_ids($args['topics']);
        }

        // Shares
        if (!empty($args['shares'])) {
            $recipients = recipient_manager::create_from_array($args['shares']);
            share_manager::share($playlist, 'totara_playlist', $recipients);
        }

        // Re-trigger the event so observers can see the topics and shares that changed.
        $event = playlist_updated::from_playlist($playlist, $USER->id);
        $event->trigger();

        return $playlist;
    }

    /**
     * @return array
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('engage_resources'),
            new require_valid_recipients('shares'),
            // summary field is an optional for this operation. Hence, we will not require it.
            new clean_editor_content('summary', 'summary_format', false),

            // We leave the default value to null, because we want to fall back to the current playlist
            // format if the summary format is not set.
            new clean_content_format('summary_format', null, [FORMAT_JSON_EDITOR])
        ];
    }
}