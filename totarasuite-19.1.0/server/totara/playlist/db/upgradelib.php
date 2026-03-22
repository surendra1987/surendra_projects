<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_playlist
 */

/**
 *  Fix the card sort order to help end user keep the same order in
 *  the playlist. it's only used in the upgrade file.
 */
function totara_playlist_upgrade_fix_card_sort_order() {
    global $DB;

    $transaction = $DB->start_delegated_transaction();
    $playlists = $DB->get_records('playlist');
    foreach ($playlists as $playlist) {
        $records = $DB->get_records('playlist_resource', ['playlistid' => $playlist->id], 'sortorder ASC');

        // If no resource in the playlist, we skip the loop and go into next.
        if (empty($records)) {
            continue;
        }
        $last = end($records);
        $max_sort = $last->sortorder;
        foreach ($records as $record) {
            $record->sortorder = $max_sort;
            $DB->update_record('playlist_resource', ['id' => $record->id, 'sortorder' => $record->sortorder]);
            $max_sort--;
        }
    }
    $transaction->allow_commit();
}

/*
 * Removes any duplicate playlist resources that may have ended up due to a course card bug.
 * Will also resync the count on the remaining resource items.
 */
function totara_playlist_remove_duplicate_resources() {
    global $DB;

    // Find the playlist resources that should be deleted
    // This query will return the duplicate entries, leaving the originals behind.
    // Unfortunately mysql5 does not support row_number so we have to pull all back
    $playlist_resources = $DB->get_records_sql('
    SELECT pr.id, pr.playlistid, pr.resourceid
    FROM {playlist_resource} pr
    INNER JOIN (
        SELECT playlistid, resourceid
        FROM {playlist_resource}
        GROUP BY playlistid, resourceid
        HAVING COUNT(resourceid) > 1
    ) dupe ON dupe.playlistid = pr.playlistid
    AND dupe.resourceid = pr.resourceid');


    $resource_ids = [];
    $playlists = [];

    $transaction = $DB->start_delegated_transaction();
    foreach ($playlist_resources as $playlist_resource) {
        // Leave the first found playlist resource behind
        $playlist_id = $playlist_resource->playlistid;
        if (!isset($playlists[$playlist_id])) {
            $playlists[$playlist_id] = 1;
            continue;
        }

        $DB->delete_records('playlist_resource', ['id' => $playlist_resource->id]);
        $resource_ids[$playlist_resource->resourceid] = $playlist_resource->resourceid;
    }
    $transaction->allow_commit();

    // Finally, resync the usage counts of the touched resources back to their correct numbers
    foreach ($resource_ids as $resource_id) {
        $count = $DB->count_records('playlist_resource', ['resourceid' => $resource_id]);
        $DB->update_record('engage_resource', ['id' => $resource_id, 'countusage' => $count]);
    }
}