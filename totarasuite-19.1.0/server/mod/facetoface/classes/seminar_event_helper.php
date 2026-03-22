<?php
/*
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
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package mod_facetoface
 */

namespace mod_facetoface;

defined('MOODLE_INTERNAL') || die();

use mod_facetoface\hook\resources_are_being_updated;
use mod_facetoface\hook\service\seminar_session_resource;
use mod_facetoface\hook\service\seminar_session_resource_dynamic;
use mod_facetoface\hook\sessions_are_being_updated;
use mod_facetoface\internal\session_data;
use mod_facetoface\signup\state\{
    attendance_state,
    booked,
    waitlisted,
    requested,
    requestedrole,
    requestedadmin
};
use mod_facetoface\totara_notification\resolver\facilitator_sessions_details_changed;
use mod_facetoface\totara_notification\seminar_notification_helper;

/**
 * Additional seminar_event functionality.
 */
final class seminar_event_helper {
    /**
     * Merge and purge sessions
     * If dates provided matches any current session then update current session with new dates
     * If dates provided do not match any current sessions then remove unmatched current sessions and insert new dates
     *
     * @param seminar_event $seminarevent
     * @param array $dates Dates used for updating/creating sessions
     * @param boolean $update - Optional, Whether the event is being updated(true) or created(false)
     *                          This is used to control whether faciliator updates are sent.
     * @return array containing data which might be useful later
     */
    public static function merge_sessions(seminar_event $seminarevent, array $dates, bool $update = false): array {
        global $DB;

        $result = [
            'sessions_cancelled' => [],
        ];

        self::validate_ids($dates);

        // Refresh list of current sessions from the database for merging, then clear it again.  This ensures that
        // all other singleton instances down the line will get the updated list when get_sessions() is called.
        $sessionstobedeleted = $seminarevent->get_sessions(true);
        $sessionsindb = iterator_to_array($sessionstobedeleted);
        $seminarevent->clear_sessions();

        // Cloning dates to prevent messing with original data. $dates = unserialize(serialize($dates)) will also work.
        $dates = array_map(function ($date) {
            return clone $date;
        }, $dates);

        // Trigger Centralised Notifications for sessions being changed because of the change in room or asset.
        // Room or assert change notification should be done here since parameters will change down the line.

        // Variable to store sessions that are being send the CN notification because of the change in room.
        // This is to prevent sending the same notification multiple times.
        $cn_triggered_session = [];
        $seminar = $seminarevent->get_seminar();

        // Get current sessions.
        $sessions = array_map(function ($sessiondate) {
            return seminar_session_resource::from_record($sessiondate);
        }, $dates);

        if ($update) { // Only send details changed notifications on update.
            foreach ($sessions as $session) {
                // Get the old asset.
                $old_asset_ids_string = asset_helper::get_session_assetids($session->get_session()->get_id());
                $old_asset_ids = ($old_asset_ids_string !== "") ? explode(",", $old_asset_ids_string) : [];

                // Get current asset.
                $current_asset = $session->get_asset_list()->to_array();
                $current_asset_ids = array_map(function ($asset) {
                    return $asset->get_id();
                }, $current_asset);

                // Get the old rooms.
                $old_room_ids = room_helper::get_room_ids_sorted($session->get_session()->get_id());

                // Get current rooms.
                $current_rooms = $session->get_room_list()->to_array();
                $current_room_ids = array_map(function ($room) {
                    return $room->get_id();
                }, $current_rooms);

                // Check if the asset or room has changed.
                if (array_values($old_asset_ids) != array_values($current_asset_ids) ||
                    array_values($old_room_ids) != array_values($current_room_ids)) {
                    // Add the session to the list of sessions that are being send the CN notification.
                    $cn_triggered_session[] = $session;

                    // Get the facilitators for the session.
                    $facilitators = self::get_recipients($seminarevent, [$session]);
                    foreach ($facilitators as $recipient => $seminareventfiltered) {
                        $data = [
                            'seminar_event_id' => $seminarevent->get_id(),
                            'facilitator_user_id' => $recipient,
                            'seminar_id' => $seminar->get_id(),
                            'module_id' => $seminar->get_coursemodule()->id,
                            'course_id' => $seminar->get_course(),
                        ];
                        seminar_notification_helper::create_seminar_notifiable_event_queue(
                            $seminar,
                            new facilitator_sessions_details_changed($data)
                        );
                    }
                }
            }
        } // End of CN trigger.

        // Get a list of sessions that should be updated/inserted.
        $dates = self::filter_sessions($seminarevent, $dates, $sessionstobedeleted);

        // Notify watchers only if sessions are being inserted or deleted, or session time is updated.
        $datesinserted = array_filter($dates, function ($date) {
            return empty($date->id);
        });
        $datesupdated = array_filter($dates, function ($date) {
            return !empty($date->id);
        });

        if (!empty($datesinserted) || !empty($datesupdated) || !$sessionstobedeleted->is_empty()) {
            $session_are_being_updated = new sessions_are_being_updated($seminarevent, $datesinserted, $datesupdated, $sessionstobedeleted);
            $session_are_being_updated->execute();

            // Trigger Centralised Notifications for sessions being changed because of the change in date.
            // Time changes should be done here since parameters will change down the line.

            $sessionstobeupdated = $session_are_being_updated->sessionstobeupdated;

            // Remove sessions that are already triggered CN
            foreach ($cn_triggered_session as $key => $session_triggered) {
                foreach ($sessionstobeupdated as $key2 => $session) {
                    if (!isset($session_triggered->sessionid) || $session_triggered->get_session_id() == $session->get_session_id()) {
                        unset($sessionstobeupdated[$key2]);
                    }
                }
            }

            $recipients = self::get_recipients($seminarevent, $sessionstobeupdated);
            foreach ($recipients as $recipient => $seminareventfiltered) {
                $data = [
                    'seminar_event_id' => $seminarevent->get_id(),
                    'facilitator_user_id' => $recipient,
                    'seminar_id' => $seminar->get_id(),
                    'module_id' => $seminar->get_coursemodule()->id,
                    'course_id' => $seminar->get_course(),
                ];
                seminar_notification_helper::create_seminar_notifiable_event_queue(
                    $seminar,
                    new facilitator_sessions_details_changed($data)
                );
            }
            // End of CN trigger.
        }


        if (!empty(!$sessionstobedeleted->is_empty())) {
            $result['sessions_cancelled'] = array_map(function ($sessiontobedeleted) {
                return seminar_session_resource_dynamic::from_session($sessiontobedeleted);
            }, iterator_to_array($sessionstobedeleted, false));
        }

        // Move out conflict dates.
        /** @var seminar_session[] $sessionsindb */
        $uniquetime = 0;
        $get_unique_time = function () use (&$uniquetime, &$sessionsindb) {
            while (++$uniquetime) {
                foreach ($sessionsindb as $session) {
                    if ($session->get_timestart() == $uniquetime || $session->get_timefinish() == $uniquetime) {
                        continue 2;
                    }
                }
                return $uniquetime;
            }
        };

        if (!empty($dates)) {
            foreach ($dates as $date) {
                foreach ($sessionsindb as &$sessiondb) {
                    /** @var seminar_session $sessiondb */
                    if ((int)$date->id === $sessiondb->get_id()) {
                        continue;
                    }
                    $update = false;
                    if ((int)$date->timestart === $sessiondb->get_timestart()) {
                        $sessiondb->set_timestart($get_unique_time());
                        $update = true;
                    }
                    if ((int)$date->timefinish === $sessiondb->get_timefinish()) {
                        $sessiondb->set_timefinish($get_unique_time());
                        $update = true;
                    }
                    if ($update) {
                        $sessiondb->save();
                    }
                }
            }
        }

        // Delete the current sessions that were not filtered out. These sessions did not match any input date provided
        // so we assume that they should be deleted.
        $sessionstobedeleted->delete();

        // Update or create sessions with their associated assets.
        $datesupdated = [];
        foreach ($dates as $date) {
            $assets = isset($date->assetids) ? $date->assetids : [];
            unset($date->assetids);

            $rooms = isset($date->roomids) ? $date->roomids : [];
            unset($date->roomids);

            $facilitators = isset($date->facilitatorids) ? $date->facilitatorids : [];
            unset($date->facilitatorids);

            if ($date->id > 0) {
                $DB->update_record('facetoface_sessions_dates', $date);
            } else {
                $date->sessionid = $seminarevent->get_id();
                $date->id = $DB->insert_record('facetoface_sessions_dates', $date);
            }

            // Notify watchers.
            $datecloned = clone $date;
            $datecloned->assetids = $assets;
            $datecloned->roomids = $rooms;
            $datecloned->facilitatorids = $facilitators;
            $datesupdated[] = $datecloned;
        }

        // Notify watchers.
        if (!empty($datesupdated)) {
            (new resources_are_being_updated($seminarevent, $datesupdated))->execute();
        }

        foreach ($datesupdated as $date) {
            room_helper::sync($date->id, array_unique($date->roomids));
            asset_helper::sync($date->id, array_unique($date->assetids));
            facilitator_helper::sync($date->id, array_unique($date->facilitatorids));
        }

        return $result;
    }

    /**
     * @param array $dates
     */
    private static function validate_ids(array $dates): void {
        $validate = function ($what, $i, $ids) {
            foreach ($ids as $j => $id) {
                if (!is_number($id)) {
                    debugging("the {$what}ids array contains a non-number value at #{$j} of date #{$i}.", DEBUG_DEVELOPER);
                }
            }
        };
        foreach ($dates as $i => $date) {
            $validate('room', $i, (isset($date->roomids) && is_array($date->roomids)) ? $date->roomids : []);
            $validate('asset', $i, (isset($date->assetids) && is_array($date->assetids)) ? $date->assetids : []);
            $validate('facilitator', $i, (isset($date->facilitatorids) && is_array($date->facilitatorids)) ? $date->facilitatorids : []);
        }
    }

    /**
     * Filtering dates: throwing out dates that haven't changed and
     * throwing out old dates which present in the new dates array therefore
     * leaving a list of dates to safely remove from the database.
     * Also it is important to note that we have to unset all the dates
     * from a new dates array with the ID which is not in the old dates array
     * and != 0 (not a new date) to prevent users from messing with the input
     * and other seminar dates since we rely on the date id came from a form.
     *
     * @param seminar_event $seminarevent
     * @param array $dates
     * @param seminar_session_list $sessions
     * @return array $dates
     */
    private static function filter_sessions(seminar_event $seminarevent, array $dates, seminar_session_list $sessions): array {
        $datesincluded = [];
        $datesexcluded = [];
        $datesexcluded_but_sync = [];
        foreach ($dates as $date) {
            $date->id = isset($date->id) ? $date->id : 0;
            if ($sessions->contains($date->id)) {
                /** @var seminar_session $session */
                $session = $sessions->get($date->id);
                $sessions->remove($date->id);
                if ($session->get_sessiontimezone() == $date->sessiontimezone
                    && $session->get_timestart() == $date->timestart
                    && $session->get_timefinish() == $date->timefinish)
                {
                    $date->roomids = (isset($date->roomids) && is_array($date->roomids)) ? $date->roomids : [];
                    $date->assetids = (isset($date->assetids) && is_array($date->assetids)) ? $date->assetids : [];
                    $date->facilitatorids = (isset($date->facilitatorids) && is_array($date->facilitatorids)) ? $date->facilitatorids : [];
                    $datesexcluded_but_sync[] = $date;
                    continue;
                }
            } else if ($date->id != 0) {
                $datesexcluded[] = $date;
                continue;
            }
            $datesincluded[] = $date;
        }

        // Notify watchers.
        if (!empty($datesexcluded) || !empty($datesexcluded_but_sync)) {
            (new resources_are_being_updated($seminarevent, array_merge($datesexcluded, $datesexcluded_but_sync)))->execute();
        }

        foreach ($datesexcluded_but_sync as $date) {
            room_helper::sync($date->id, array_unique($date->roomids));
            asset_helper::sync($date->id, array_unique($date->assetids));
            facilitator_helper::sync($date->id, array_unique($date->facilitatorids));
        }
        return $datesincluded;
    }

    /**
     * @deprecated since Totara 13.0
     *
     * Keep this in mind that $seminarevent will mutate itself after deleting. Its own properties will be reset to
     * default values after deleting is complete.
     *
     * This function will try to cancel the event first before start deleting it. When an event is being cancelled, it
     * will try to send emails/notifications out to users/admins that are involving with this seminar event. And unlink
     * the rooms/assets that are being used by session dates. Therefore, it needs to cache the rooms/assets before any
     * kind of this action happens.
     *
     * The steps that this function will be doing:
     * + cache custom rooms
     * + cache custom assets
     * + cancel the seminar event
     * + delete seminar event itself.
     * + deletet hose custom assets that would become orphan
     * + delete those custom rooms that would become orphan
     *
     * @param seminar_event $seminarevent
     * @return bool
     */
    public static function delete_seminarevent(seminar_event $seminarevent): bool {

        debugging('seminar_event_helper::delete_seminarevent() function has been deprecated, this functionality is moved to seminar_event::delete()',
            DEBUG_DEVELOPER);

        if (!$seminarevent->exists()) {
            return false;
        }

        $seminarevent->delete();

        return true;
    }

    /**
     * Collect event session and sign-up data to get event booking status/event session status/etc.
     * @param seminar_event $seminarevent
     * @param signup|null $signup
     * @param boolean $sortbyascending Set true to sort sessions by past first
     * @param integer $timenow Current timestamp
     * @return session_data
     */
    public static function get_sessiondata(seminar_event $seminarevent, ?signup $signup, bool $sortbyascending = true, int $timenow = 0): session_data {

        if ($timenow <= 0) {
            $timenow = time();
        }
        $statuscodes = attendance_state::get_all_attendance_code_with([
            requested::class,
            requestedrole::class,
            requestedadmin::class,
            waitlisted::class,
            booked::class,
        ]);

        $sessions = $seminarevent->get_sessions();
        $sessions->sort('timestart', $sortbyascending ? seminar_session_list::SORT_ASC : seminar_session_list::SORT_DESC);

        $sessiondata = new session_data();
        foreach ((array)$seminarevent->to_record() as $prop => $val) {
            $sessiondata->{$prop} = $val;
        }
        $sessiondata->mintimestart = $seminarevent->get_mintimestart();
        $sessiondata->maxtimefinish = $seminarevent->get_maxtimefinish();
        $sessiondata->sessiondates = $sessions->to_records();
        $sessiondata->isstarted = $seminarevent->is_first_started($timenow);
        $sessiondata->isprogress = $seminarevent->is_progress($timenow);
        $sessiondata->cntdates = count($sessiondata->sessiondates);

        $bookedsession = null;
        if ($signup !== null && $signup->exists() && in_array($signup->get_state()::get_code(), $statuscodes)) {
            // The signup is only counted if the state is within the requested state up to fully attended state.
            // Work arround to build booked session record object.
            $bookedsession = $signup->to_record();
            $bookedsession->facetoface = $seminarevent->get_facetoface();
            $bookedsession->cancelledstatus = $seminarevent->get_cancelledstatus();
            $bookedsession->timemodified = $seminarevent->get_timemodified();

            $signupstatus = $signup->get_signup_status();
            if (null === $signupstatus) {
                debugging("No signup status found for signup: '{$signup->get_id()}", DEBUG_DEVELOPER);
            } else {
                $bookedsession->timecreated = $signupstatus->get_timecreated();
                $bookedsession->timegraded = $bookedsession->timecreated;
                $bookedsession->statuscode = $signup->get_state()::get_code();
                $bookedsession->timecancelled = 0;
                $bookedsession->mailedconfirmation = 0;
            }
        }
        $sessiondata->bookedsession = $bookedsession;
        return $sessiondata;
    }

    /**
     * Check the availability of the course module.
     *
     * @param seminar_event $seminarevent
     * @param integer $userid
     * @return boolean
     */
    public static function is_available(seminar_event $seminarevent, int $userid = 0): bool {
        global $CFG;

        if ($CFG->enableavailability) {
            $cm = get_coursemodule_from_instance('facetoface', $seminarevent->get_facetoface());
            if (!get_fast_modinfo($cm->course, $userid)->get_cm($cm->id)->available) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return Event booking status string.
     * @param stdClass $session
     * @param integer $signupcount
     * @return string
     */
    public static function booking_status(\stdClass $session, int $signupcount): string {
        global $CFG;

        $isbookedsession = (!empty($session->bookedsession) && ($session->id == $session->bookedsession->sessionid));
        $timenow = time();
        $seminarevent = (new seminar_event())->from_record_with_dates($session, false);

        $status = get_string('bookingopen', 'mod_facetoface');
        if ($seminarevent->get_cancelledstatus()) {
            $status = get_string('bookingsessioncancelled', 'mod_facetoface');
        } else if ($seminarevent->is_first_started($timenow) && $seminarevent->is_progress($timenow)) {
            $status = get_string('sessioninprogress', 'mod_facetoface');
        } else if ($seminarevent->is_first_started($timenow)) {
            $status = get_string('sessionover', 'mod_facetoface');
        } else if ($isbookedsession) {
            $state = \mod_facetoface\signup\state\state::from_code($session->bookedsession->statuscode);
            $status = $state::get_string();
        } else if ($signupcount >= $seminarevent->get_capacity()) {
            $status = get_string('bookingfull', 'mod_facetoface');
        } else if (!empty($seminarevent->get_registrationtimestart()) && $seminarevent->get_registrationtimestart() > $timenow) {
            $status = get_string('registrationnotopen', 'mod_facetoface');
        } else if (!empty($seminarevent->get_registrationtimefinish()) && $timenow > $seminarevent->get_registrationtimefinish()) {
            $status = get_string('registrationclosed', 'mod_facetoface');
        }

        if ($CFG->enableavailability) {
            $cm = get_coursemodule_from_instance('facetoface', $seminarevent->get_facetoface());
            if (!get_fast_modinfo($cm->course)->get_cm($cm->id)->available) {
                $status = get_string('bookingrestricted', 'mod_facetoface');
            }
        }
        return $status;
    }

    /**
     * Return event status, event booking status and user booking status strings.
     * @param \stdClass $session
     * @param integer $signupcount
     * @param boolean $attendancestatus Set false to hide attendance status from a user
     * @return array packs [ event_status, event_booking_status, user_booking_status ]
     */
    public static function event_status(\stdClass $session, int $signupcount, bool $attendancestatus = true): array {
        global $CFG;

        $isbookedsession = (!empty($session->bookedsession) && ($session->id == $session->bookedsession->sessionid));
        $timenow = time();
        $seminarevent = (new seminar_event())->from_record_with_dates($session, false);
        $sessionover = $seminarevent->is_over();
        $cancelled = (bool)$seminarevent->get_cancelledstatus();

        if ($cancelled) {
            $event_status = get_string('sessioncancelled', 'mod_facetoface');
        } else if (!$seminarevent->is_sessions()) {
            $event_status = get_string('wait-listed', 'mod_facetoface');
        } else if ($seminarevent->is_over($timenow)) {
            $event_status = get_string('sessionover', 'mod_facetoface');
        } else if ($seminarevent->is_progress($timenow)) {
            $event_status = get_string('sessioninprogress', 'mod_facetoface');
        } else {
            $event_status = get_string('sessionupcoming', 'mod_facetoface');
        }

        if ($cancelled) {
            // Don't display event booking status on a cancelled event.
            $event_booking_status = '';
        } else {
            if ($seminarevent->is_first_started($timenow)) {
                $event_booking_status = '';
            } else {
                $event_booking_status = get_string('bookingopen', 'mod_facetoface');
            }
            if ($signupcount >= $seminarevent->get_capacity()) {
                $event_booking_status = get_string('bookingfull', 'mod_facetoface');
            } else if (!empty($seminarevent->get_registrationtimestart()) && $seminarevent->get_registrationtimestart() > $timenow) {
                $event_booking_status = get_string('registrationnotopen', 'mod_facetoface');
            } else if (!empty($seminarevent->get_registrationtimefinish()) && $timenow > $seminarevent->get_registrationtimefinish()) {
                $event_booking_status = get_string('registrationclosed', 'mod_facetoface');
            }
            if ($CFG->enableavailability) {
                $cm = get_coursemodule_from_instance('facetoface', $seminarevent->get_facetoface());
                if (!get_fast_modinfo($cm->course)->get_cm($cm->id)->available) {
                    $event_booking_status = get_string('bookingrestricted', 'mod_facetoface');
                }
            }
        }

        $user_booking_status = '';
        if (!$sessionover && !$cancelled && $isbookedsession) {
            // Display user booking status only on an upcoming and not-cancelled event.
            $user_booking_status = signup_helper::get_user_booking_status($session->bookedsession->statuscode, $attendancestatus);
        }
        return [ $event_status, $event_booking_status, $user_booking_status ];
    }

    /**
     * Get an array of facilitators attached to the sessions.
     *
     * @param seminar_event $seminarevent
     * @param seminar_session_resource[] $sessions
     * @return seminar_event[] facilitator_userid => seminar_event_with_filtered_sessions
     */
    private static function get_recipients(seminar_event $seminarevent, array $sessions): array {
        $f2fevent = $seminarevent->to_record();
        $time = time();
        $recipients = [];
        foreach ($sessions as $sess) {
            if ($sess->get_session()->is_over($time)) {
                continue;
            }
            if ($sess->has_facilitators()) {
                $date = $sess->get_session()->to_record();
                $facs = $sess->get_facilitator_list(true);
                foreach ($facs as $fac) {
                    /** @var facilitator_user $fac */
                    if (!isset($recipients[$fac->get_userid()])) {
                        $recipients[$fac->get_userid()] = [];
                    }
                    $recipients[$fac->get_userid()][] = $date;
                }
            }
        }
        return array_map(function ($dates) use ($f2fevent) {
            $f2fevent->sessiondates = $dates;
            $event = new seminar_event();
            $event->from_record_with_dates($f2fevent);
            return $event;
        }, $recipients);
    }
}
