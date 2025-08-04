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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */
namespace container_workspace;

use container_workspace\enrol\manager;
use container_workspace\entity\workspace as workspace_entity;
use container_workspace\event\user_role_changed;
use container_workspace\event\workspace_created;
use container_workspace\event\workspace_updated;
use container_workspace\exception\workspace_exception;
use container_workspace\member\evaluation_result;
use container_workspace\interactor\workspace\interactor;
use container_workspace\member\member;
use container_workspace\member\owner_addition_conditions;
use container_workspace\member\owner_removal_conditions;
use container_workspace\member\member_removal_conditions;
use container_workspace\theme\file\workspace_image;
use core\collection;
use core\entity\role;
use core\entity\user;
use core\files\file_helper;
use core\orm\query\builder;
use core_container\container;
use core_container\container_helper;
use core_container\facade\category_name_provider;
use moodle_exception;
use theme_config;
use core_text;
use totara_topic\exception\topic_exception;
use totara_topic\provider\topic_provider;
use totara_topic\resolver\resolver_factory;
use totara_topic\topic_helper;

/**
 * Class workspace
 *
 * NB: this model class and its corresponding entity class (container_workspace
 * \entity\workspace) may cause confusion because they differ from other model/
 * entity abstractions in Totara:
 * - Due to the way workspaces were designed, a workspace is a course. But it
 *   also has its own database record in the workspace table.
 * - The entity class deals with the workspace table record => entity::id is a
 *   _record id int the workspace table_.
 * - The model::id field is actually the _associated course id_ => model::id is
 *   a _record id in the course table_.
 * - In other words, unlike other models/entities in Totara:
 *      model::id !== entity::id
 *   instead
 *      model::id === entity::course_id
 *
 * @package container_workspace
 */
final class workspace extends container implements category_name_provider {

    private const MAX_NAME_LENGTH = 75 ;

    /**
     * @var string
     */
    public const IMAGE_AREA = 'image';

    /**
     * @var string
     */
    public const DESCRIPTION_AREA = 'description';

    /**
     * @var workspace_entity|null
     */
    private $entity;

    /**
     * @param int $id
     * @return workspace
     */
    public static function from_id(int $id): container {
        global $DB;

        $sql = '
            SELECT
                c.*,
                wo.user_id,
                wo.id AS w_id,
                wo.private AS workspace_private,
                wo.timestamp AS timestamp,
                wo.to_be_deleted AS to_be_deleted
            FROM "ttr_course" c
            INNER JOIN "ttr_workspace" wo ON wo.course_id = c.id
            WHERE c.id = :course_id
        ';

        $params = ['course_id' => $id];
        $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        $workspace = new workspace();
        $workspace->map_record($record);

        return $workspace;
    }

    /**
     * @param \stdClass $record
     * @return workspace
     */
    public static function from_record(\stdClass $record): container {
        global $DB;

        if (!isset($record->id)) {
            throw new \coding_exception("No id was specified");
        }

        $workspace_fields = [
            'user_id',
            'w_id',
            'workspace_private',
            'to_be_deleted',
            'timestamp'
        ];

        $fetch_record = false;
        foreach ($workspace_fields as $field) {
            if (!property_exists($record, $field)) {
                $fetch_record = true;
                break;
            }
        }

        if ($fetch_record) {
            // We will have to update the workspace extra records, as the argument $record does not
            // have any of them.
            $workspace_record = $DB->get_record(
                'workspace',
                ['course_id' => $record->id],
                '*',
                MUST_EXIST
            );

            // Cloning the record so that we don't modify the original
            $record = clone $record;
            $record->w_id = $workspace_record->id;
            $record->user_id = $workspace_record->user_id;
            $record->workspace_private = $workspace_record->private;
            $record->to_be_deleted = $workspace_record->to_be_deleted;
            $record->timestamp = $workspace_record->timestamp;
        }

        $workspace = new static();
        $workspace->map_record($record);

        return $workspace;
    }

    /**
     * @param \stdClass $data
     * @return void
     */
    protected static function pre_create(\stdClass $data): void {
        parent::pre_create($data);

        // Validate the type and visibility. Hidden can only be existing if the workspace is private.
        if (isset($data->workspace_private) && isset($data->visible)) {
            if (!$data->workspace_private && !$data->visible) {
                // The workspace is not a private one, and also not visible - throw exception.
                // As hidden workspace can only be existing if the workspace is private.
                throw new \coding_exception(
                    "The settings for workspace's access and visibility are miss matched, " .
                    "the visibility should be set to zero when workspace is a hidden type"
                );
            }
        }

        if (core_text::strlen($data->fullname) > self::MAX_NAME_LENGTH) {
            throw new \coding_exception('workspace name exceeds the maximum length');
        }
    }

    /**
     * @param \stdClass $data
     * @return container
     */
    protected static function do_create(\stdClass $data): container {
        global $DB, $USER;

        // Checking for the existing of category
        if (!$DB->record_exists('course_categories', ['id' => $data->category])) {
            throw new \coding_exception("Category for id '{$data->category}' does not exist");
        }

        // Retrieving the user's id of whoever create this.
        $user_id = $USER->id;

        if (isset($data->user_id)) {
            $user_id = $data->user_id;
            unset($data->user_id);
        } else if (isset($data->userid)) {
            $user_id = $data->userid;
            unset($data->userid);
        }

        $new_id = $DB->insert_record('course', $data);

        $entity = new workspace_entity();
        $entity->user_id = $user_id;
        $entity->course_id = $new_id;
        $entity->private = 0;
        $entity->timestamp = time();
        $entity->to_be_deleted = false;

        if (isset($data->workspace_private) && $data->workspace_private) {
            $entity->private = 1;
        }

        $entity->save();
        return static::from_id($new_id);
    }

    /**
     * @param container|workspace $container
     * @param \stdClass $data
     */
    protected static function post_create(container $container, \stdClass $data): void {
        parent::post_create($container, $data);

        $event = workspace_created::from_workspace($container);
        $event->trigger();
    }

    /**
     * Providing a name for category, of which this workspace is living within.
     *
     * @return string
     */
    public static function get_container_category_name(): string {
        return get_string('category_name', 'container_workspace');
    }

    /**
     * @return \moodle_url
     */
    public function get_view_url(): \moodle_url {
        return $this->get_workspace_url();
    }

    /**
     * @param \stdClass $data
     * @return \stdClass
     */
    protected static function normalise_data_on_create(\stdClass $data): \stdClass {
        $record = parent::normalise_data_on_create($data);

        // Default format for this container - it must always be `NONE` as we are not planning
        // to re-use course format.
        $record->format = 'none';

        if (!isset($record->category)) {
            $record->category = static::get_default_category_id();
        }

        if (!property_exists($record, 'timecreated')) {
            $record->timecreated = time();
        }

        if (!property_exists($record, 'timemodified')) {
            $record->timemodified = time();
        }

        if (!property_exists($record, 'summary')) {
            $record->summary = null;
        }

        if (!property_exists($record, 'summaryformat')) {
            // Just use plain format for the summary of workspace.
            $record->summaryformat = FORMAT_PLAIN;
        }

        if (!property_exists($record, 'visible')) {
            // Make it as default.
            $record->visible = 1;
        }

        if (!property_exists($record, 'workspace_private')) {
            // By default every workspace will be set to public.
            $record->workspace_private = 0;
        }

        if (!property_exists($record, 'shortname')) {
            // Generate random shortname for now.
            $fullname = clean_param($data->fullname, PARAM_TEXT);
            $short_name = strtolower($fullname);

            $counter = 1;
            while (true) {
                $result = container_helper::is_container_existing_with_field('shortname', $short_name);
                if (!$result) {
                    break;
                }

                $short_name .= " - {$counter}";
                $counter += 1;
            }

            $record->shortname = $short_name;
        }

        // By default, workspace does not enable any completion for now.
        $record->enablecompletion = 0;
        $record->completionstartonenrol = 0;
        $record->completionprogressonview = 0;
        $record->completionnotify = 0;

        $record->visibleold = $record->visible;
        return $record;
    }

    /**
     * @param \stdClass $record
     * @return void
     */
    protected function map_record(\stdClass $record): void {
        if (!isset($record->id)) {
            throw new \coding_exception("No id found");
        }

        // Making sure that we do not change any properties at the parent level.
        $record = clone $record;

        if (!isset($this->entity)) {
            $this->entity = new workspace_entity();
        }

        if (property_exists($record, 'w_id')) {
            $this->entity->id = $record->w_id;
            unset($record->w_id);
        }

        if (property_exists($record, 'user_id')) {
            $this->entity->user_id = $record->user_id;
            unset($record->user_id);
        }

        if (property_exists($record, 'workspace_private')) {
            // Convert it to boolean - so that we can make sure all the invalid number will be stripped out.
            $this->entity->private = clean_param($record->workspace_private, PARAM_BOOL);
            unset($record->workspace_private);
        }

        if (property_exists($record, 'timestamp')) {
            $this->entity->timestamp = $record->timestamp;
            unset($record->timestamp);
        }

        if (property_exists($record, 'to_be_deleted')) {
            $this->entity->to_be_deleted = (bool) $record->to_be_deleted;
            unset($record->to_be_deleted);
        }

        parent::map_record($record);
    }

    /**
     * @deprecated since Totara 19.0
     *
     * Returning the user record id of workspace's owner.
     * @return int|null
     */
    public function get_user_id(): ?int {
        debugging(
            __METHOD__ .
            ' has been deprecated; please use owners()->first()?->id instead',
            DEBUG_DEVELOPER
        );

        return $this->entity->user_id;
    }

    /**
     * @param \stdClass $data
     * @return void
     */
    protected function pre_update(\stdClass $data): void {
        parent::pre_update($data);
        if (property_exists($data, 'visible')) {
            $visible = $data->visible;
            $old_visible = $this->visible;

            if ($old_visible && !$visible) {
                // This is not do-able.
                throw new \coding_exception("Cannot move down to hidden workspace");
            }
        }

        if (property_exists($data, 'workspace_private')) {
            $new_private = $data->workspace_private;
            $old_private = $this->entity->private;

            if ($new_private != $old_private) {
                throw new \coding_exception("The workspace cannot move between visibility");
            }
        }

        if (core_text::strlen($data->fullname) > self::MAX_NAME_LENGTH) {
            throw new \coding_exception('The workspace name exceeds the maximum length');
        }
    }

    /**
     * @param \stdClass $data
     * @return bool
     */
    protected function do_update(\stdClass $data): bool {
        if (!isset($data->timemodified)) {
            $data->timemodified = time();
        }

        if (isset($data->category) && $this->category != $data->category) {
            // We need to prevent the move of category between workspace.
            throw workspace_exception::on_update();
        }

        if (!property_exists($data, 'id')) {
            // Set field id for $data.
            $data->id = $this->get_id();
        }

        return parent::do_update($data);
    }

    /**
     * @param \stdClass $data
     * @return void
     */
    protected function post_update(\stdClass $data): void {
        $this->reload();

        // Make sure the role capabilities are set correctly
        $this->get_enrolment_manager()->toggle_viewhidden_capability($this->is_hidden());

        // Trigger event for updating
        $event = workspace_updated::from_workspace($this);
        $event->trigger();
    }

    /**
     * @return void
     */
    public function reload(): void {
        parent::reload();
        $this->entity->refresh();
    }

    /**
     * @return manager
     */
    public function get_enrolment_manager(): manager {
        return manager::from_workspace($this);
    }

    /**
     * @return void
     */
    public function delete(): void {
        global $DB;

        // Delete any sections in here. If there are any.
        $sections = $this->get_sections();

        foreach ($sections as $section) {
            $section->delete();
        }

        // Delete the workspace owner first, then delete the course table after ward.
        $this->entity->delete();
        $workspace_id = $this->get_id();

        if (!$this->entity->deleted()) {
            return;
        }

        // Deleting the context, then start deleting the record.
        \context_helper::delete_instance(CONTEXT_COURSE, $workspace_id);

        // Start deleting the course record.
        $DB->delete_records('course', ['id' => $workspace_id]);
    }

    /**
     * Moving itself default image into its own area. Note that this function does not check
     * for the existing of the draft_id.
     *
     * @param int $draft_id
     * @param int|null $user_id     The current actor.
     * @return void
     */
    public function save_image(int $draft_id, ?int $user_id = null): void {
        global $USER;
        if (null === $user_id || 0 === $user_id) {
            $user_id = $USER->id;
        }
        $file_helper = new file_helper(self::get_type(), self::IMAGE_AREA, $this->get_context());
        $file_helper->save_files($draft_id, $user_id, ['maxfiles' => 1]);
    }

    /**
     * @since Totara 13.6 added parameter $theme_config
     *
     * @param theme_config|null $theme_config
     * @return \moodle_url
     */
    public function get_image(?theme_config $theme_config = null): \moodle_url {
        global $USER;

        $file_helper = new file_helper(self::get_type(), self::IMAGE_AREA, $this->get_context());
        $url = $file_helper->get_file_url();
        if (empty($url)) {
            $workspace_image = new workspace_image($theme_config);
            $workspace_image->set_tenant_id(!empty($USER->tenantid) ? $USER->tenantid : 0);
            $url = $workspace_image->get_current_or_default_url();
        }
        return $url;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->fullname;
    }

    /**
     * Create a valid URL for workspace page.
     *
     * @param int $workspace_id
     * @param string|null $tab
     *
     * @return \moodle_url
     */
    public static function create_url(int $workspace_id, ?string $tab = null): \moodle_url {
        $params = ['id' => $workspace_id];
        if (null !== $tab) {
            $params['tab'] = $tab;
        }

        return new \moodle_url("/container/type/workspace/workspace.php", $params);
    }

    /**
     * @param string|null $tab
     * @return \moodle_url
     */
    public function get_workspace_url(?string $tab = null): \moodle_url {
        return static::create_url($this->id, $tab);
    }

    /**
     * @return bool
     */
    public function is_public(): bool {
        return $this->visible && !$this->entity->private;
    }

    /**
     * @return bool
     */
    public function is_hidden() {
        return !$this->visible && $this->entity->private;
    }

    /**
     * @return bool
     */
    public function is_private(): bool {
        return $this->visible && $this->entity->private;
    }

    /**
     * Workspace can only move to private when it is a hidden workspace or a private workspace.
     * @return bool
     */
    public function can_move_to_private(): bool {
        return $this->is_hidden() || $this->is_private();
    }

    /**
     * Only if the workspace is hidden - then yes, as nothing will be changed.
     *
     * @return bool
     */
    public function can_move_to_hidden(): bool {
        return $this->is_hidden();
    }

    /**
     * Only if the workspace is public - then yes, as nothing will be changed.
     *
     * @return bool
     */
    public function can_move_to_public(): bool {
        return $this->is_public();
    }

    /**
     * @deprecated since Totara 19.0
     *
     * Remove the primary owner.
     * @return void
     */
    public function remove_user(): void {
        debugging(
            __METHOD__ . ' has been deprecated, use workspace::remove_owners() instead',
            DEBUG_DEVELOPER
        );

        $current_owner = new user($this->entity->user_id);
        if ($this->is_owner($current_owner)) {
            member::from_user($this->entity->user_id, $this->id)
                ->demote_from_owner();
        }

        $this->entity->user_id = null;
        $this->entity->update();
    }

    /**
     * Update the timestamp of itself.
     *
     * @param int|null $now
     * @return void
     */
    public function touch(?int $now = null): void {
        if (empty($now)) {
            $now = time();
        }

        $this->entity->timestamp = $now;
        $this->entity->update();
    }

    /**
     * @return int
     */
    public function get_timestamp(): int {
        return $this->entity->timestamp;
    }

    /**
     * @deprecated since Totara 19.0
     *
     * Update primary owner, note that this function does not check for any capabilities,
     * as it should had been done prior to call this function.
     *
     * @param int $new_user_id
     * @return void
     */
    public function update_user(int $new_user_id): void {
        debugging(
            __METHOD__ . ' has been deprecated, use workspace::add_owners() instead',
            DEBUG_DEVELOPER
        );

        $this->entity->user_id = $new_user_id;
        $this->entity->update();

        $current_owner = new user($new_user_id);
        if (!$this->is_owner($current_owner)) {
            member::from_user($this->entity->user_id, $this->id)
                ->promote_to_owner();
        }
    }

    /**
     * @return bool
     */
    public function is_to_be_deleted(): bool {
        return $this->entity->to_be_deleted;
    }

    /**
     * @param bool $to_be_deleted
     * @return void
     */
    public function mark_to_be_deleted(bool $to_be_deleted = true): void {
        $this->entity->to_be_deleted = $to_be_deleted;
        $this->entity->save();

        $this->reload();

        // Rebuild the factory cache.
        $this->rebuild_cache();
    }

    /**
     * Add the given tags to this workspace.
     *
     * @param int[] $tag_ids
     * @return void
     */
    public function add_tags_by_ids(array $tag_ids): void {
        $workspace_id = $this->id;

        foreach ($tag_ids as $tag_id) {
            topic_helper::add_topic_usage($tag_id, 'container_workspace', 'course', $workspace_id);
        }
    }

    /**
     * Removes all tags from this workspace, except for those specified.
     *
     * @param int[] $exclude_tag_ids
     * @param bool $workspace_deleting - Whether the workspace is currently being deleted
     * @throws topic_exception if a tag cannot be removed
     * @return void
     */
    public function remove_tags_by_ids(array $exclude_tag_ids = [], bool $workspace_deleting = false): void {
        global $USER;

        $workspace_id = $this->id;
        $existing_tags = topic_provider::get_for_item($workspace_id, 'container_workspace', 'course');
        $component = 'container_workspace';
        $resolver = resolver_factory::create_resolver($component);

        foreach ($existing_tags as $existing_tag) {
            if (!in_array($existing_tag->get_id(), $exclude_tag_ids)) {
                if (!$workspace_deleting && !$resolver->can_delete_usage($existing_tag, $workspace_id, 'course', $USER->id)) {
                    $label = get_string('pluginname', $component);
                    throw new topic_exception('unabletodeleteusage', $label);
                }
                topic_helper::delete_topic_usage($existing_tag, $component, 'course', $workspace_id);
            }
        }
    }

    /**
     * Indicates if the specified user is a _normal user_ ie with the member
     * role in this workspace.
     *
     * @param user $user user to check.
     *
     * @return bool true if the user is an member.
     */
    public function is_member(user $user): bool {
        return workspace_entity::repository()::user_has_role(
            $this->id,
            new role(member::get_role_for_members()),
            $user
        );
    }

    /**
     * Indicates if the specified user is an owner ie with the 'workspaceowner'
     * role in this workspace.
     *
     * @param user $user user to check.
     *
     * @return bool true if the user is an owner.
     */
    public function is_owner(user $user): bool {
        return workspace_entity::repository()::user_has_role(
            $this->id,
            new role(member::get_role_for_owners()),
            $user
        );
    }

    /**
     * Returns the owners of the workspace.
     *
     * @return collection<user> workspace owners sorted by the time they became
     *         owners. This is to keep compatibility with the current single
     *         owner behavior.
     */
    public function owners(): collection {
        $role_assign_t = 'ra_t';

        return workspace_entity::repository()::users_by_role(
            workspace_course_id: $this->id,
            role: new role(member::get_role_for_owners()),
            ra_t: $role_assign_t
        )->order_by(
            "$role_assign_t.timemodified"
        )->get();
    }

    /**
     * Returns the count of owner in the workspace.
     *
     * @return int the owner count.
     */
    public function owner_count(): int {
        return workspace_entity::repository()::users_by_role(
            $this->id,
            new role(member::get_role_for_owners())
        )->count();
    }

    /**
     * Find out if the given user meets all conditions to be made an owner of this workspace.
     *
     * @param user $user
     * @return bool
     */
    public function can_be_owner(user $user): bool {
        return owner_addition_conditions::create($this, $user->id)->evaluate()->is_fulfilled;
    }

    /**
     * Bulk add owners to the workspace.
     * If anything goes wrong, nobody is added and a collection of error messages is returned.
     * The adding was successful only if the returned collection is empty.
     *
     * @param collection $users
     * @return collection A collection of error messages.
     */
    public function add_owners(collection $users): collection {
        $error_messages = new collection();

        // Check capability.
        $interactor = interactor::from_workspace_id($this->id);
        if (!$interactor->can_manage()) {
            return $error_messages->append(get_string('missing_permission_to_add_owners', 'container_workspace'));
        }

        // Make sure the users meet all the conditions.
        foreach ($users as $user) {
            $result = owner_addition_conditions::create($this, $user->id)->evaluate();
            if (!$result->is_fulfilled) {
                $error_messages->append($user->id . ':' . $result->error);
            }
        }

        if ($error_messages->count() > 0) {
            return $error_messages;
        }

        // Promote the members to owners.
        builder::get_db()->transaction(function () use ($users) {
            foreach ($users as $user) {
                // We verified above in owner_addition_conditions that the user is a member.
                member::from_user($user->id, $this->id)->promote_to_owner();
            }
        });
        // We trigger the event after "promote the members to owners" to make sure it successful.
        $actor = user::logged_in();
        $owner_role = member::get_role_for_owners();
        $other = [
            'role_id' => $owner_role->id,
            'role_name' => role_get_name($owner_role)
        ];
        foreach ($users as $user) {
            // Trigger event to send a user role changed notification
            user_role_changed::create_from_data($this, $user, $actor, $other)->trigger();
        }

        return new collection();
    }

    /**
     * Check if the logged-in user as owner has capability to remove the other owners
     *
     * @return bool
     */
    public function can_remove_owner(): bool {
        $actor = user::logged_in();
        return owner_removal_conditions::create(
            $this,
            new collection([$actor->id => $actor]),
            $actor
        )
            ->evaluate()
            ->is_fulfilled;
    }

    /**
     * Checks if a given user can be removed from owner users.
     *
     * @param user $user
     * @return bool
     */
    public function can_owner_be_removed(user $user): bool {
        return member_removal_conditions::create($this, $user)->evaluate()->is_fulfilled;
    }

    /**
     * Remove/unassign user/s from 'workspaceowner' role and assign them to a 'student' role and leave them in the workspace.
     *
     * @param array $owner_ids
     * @param user $actor
     * @return collection A collection of error messages.
     * @throws \coding_exception
     */
    public function remove_owners(
        array $owner_ids,
        user $actor
    ): collection {
        $error_messages = new collection();
        $owners = $this->build_owner_list($owner_ids, $actor->id);
        /** @var evaluation_result $result */
        $result = owner_removal_conditions::create($this, $owners, $actor)->evaluate();
        if (!$result->is_fulfilled) {
            return collection::new([$result->error]);
        }

        // Make sure the users meet all the conditions.
        foreach ($owners as $owner) {
            // This 'removal conditions' is included for the future development,
            // PS: currently it's useless and not necessary needed it here.
            $result = member_removal_conditions::create($this, $owner)->evaluate();
            if (!$result->is_fulfilled) {
                $error_messages->append($owner->id . ':' . $result->error);
            }
        }

        if ($error_messages->count() > 0) {
            return $error_messages;
        }

        builder::get_db()->transaction(function () use ($owners, $actor) {
            // We verified above that the owners can be removed.
            $this->do_remove($owners, $actor);
        });

        // We trigger the event after "demote from owner" to make sure it successful.
        $member_role = member::get_role_for_members();
        $other = [
            'role_id' => $member_role->id,
            'role_name' => role_get_name($member_role)
        ];
        foreach ($owners as $user) {
            // Trigger event to send a user role changed notification
            user_role_changed::create_from_data($this, $user, $actor, $other)->trigger();
        }

        return collection::new([]);
    }

    /**
     * For each user with the 'workspaceowner' role assignment, attempt to
     * unassign them from the role for the current workspace".
     *
     * @param collection $owners
     * @param user $actor
     * @return void
     * @throws \coding_exception
     */
    private function do_remove(collection $owners, user $actor): void {
        $workspace_owner = $this->entity->user_id;
        $touched = false;
        foreach ($owners as $owner) {
            $owner_member = member::from_user($owner->id, $this->get_id());
            if ((int)$owner->id === (int)$workspace_owner) {
                $this->entity->user_id = null;
                $this->entity->update();
                $touched = true;
            }

            $owner_member->demote_from_owner($actor->id);
            // This should never have happened, but looks like the other transaction removed the remaining owners.
            if ($this->owner_count() === 0) {
                throw new moodle_exception('too_many_owners_to_remove', 'container_workspace');
            }
        }

        $this->reload();

        if ($touched) {
            // Update the 'user_id' field of workspace table.
            $new_owner = $this->owners()->first()?->id;
            if ($new_owner) {
                $this->entity->user_id = $new_owner;
                $this->entity->update();
                $this->reload();
            }
        }
    }

    /**
     * Rebuild the owner collection to move the actor record to end of the collection.
     * So the actor will be removed as the last item/owner from the owner list of this workspace
     * otherwise if the actor is removed in the middle of process,
     * the code will return exception that the actor does not have a capability
     * to remove the rest of the owners.
     *
     * @param array $owner_ids
     * @param int $actor_id
     * @return collection
     */
    private function build_owner_list(
        array $owner_ids,
        int $actor_id
    ): collection {
        /** @var collection $owners */
        $owners = workspace_entity::repository()::users_by_ids(
            workspace_course_id: $this->get_id(),
            role: new role(member::get_role_for_owners()),
            user_ids: $owner_ids
        )->get();

        $actor = $owners->find(
            fn($owner) => (int)$owner->id === (int)$actor_id
        );
        if ($actor) {
            $owners->rewind();
            unset($owners->$actor_id);
            $owners->append($actor);
            $owners->rewind();
        }

        return $owners;
    }
}
