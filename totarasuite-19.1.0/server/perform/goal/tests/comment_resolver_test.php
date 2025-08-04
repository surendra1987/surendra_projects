<?php
/**
 * This file is part of Totara Perform
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\totara_comment\comment_resolver;
use totara_comment\comment;
use totara_job\job_assignment;
use totara_comment\entity\comment as comment_entity;

class perform_goal_comment_resolver_test extends testcase {

    public function test_component_and_area(): void {
        $comment_resolver = new comment_resolver();
        self::assertEquals('perform_goal', $comment_resolver->get_component());
        self::assertEquals('goal_comment', comment_resolver::AREA);
    }

    public function test_is_allowed_to_create_for_own_goal(): void {
        [$user, $goal] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_create($goal->id, 'goal_comment', $user->id));
    }

    public function test_is_not_allowed_to_create_with_invalid_area(): void {
        [$user, $goal] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_create($goal->id, 'fantasy_area', $user->id));
    }

    public function test_is_not_allowed_to_create_with_invalid_goal_id(): void {
        [$user, ] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_create( - 111, 'goal_comment', $user->id));
    }

    public function test_is_not_allowed_to_create_for_other_users_goal(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $user2 = self::getDataGenerator()->create_user();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_create($goal->id, 'goal_comment', $user2->id));
    }

    public function test_is_allowed_to_create_for_other_users_goal_with_view_capability(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_create($goal->id, 'goal_comment', $manager->id));

        // Make sure it's the view capability that counts here.
        $manager_role = builder::table('role')->where('shortname', 'staffmanager')->one(true);
        unassign_capability('perform/goal:viewpersonalgoals', $manager_role->id);

        self::assertFalse($comment_resolver->is_allow_to_create($goal->id, 'goal_comment', $manager->id));
    }

    public function test_is_allowed_to_delete_own_comment(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_delete($comment, $user->id));
    }

    public function test_is_not_allowed_to_delete_own_comment_without_goal_view_capability(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $user_role = builder::table('role')->where('shortname', 'user')->one(true);
        unassign_capability('perform/goal:viewownpersonalgoals', $user_role->id);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_delete($comment, $user->id));
    }

    public function test_is_not_allowed_to_delete_other_users_comment(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_delete($comment, $manager->id));
    }

    public function test_site_admin_is_allowed_to_delete_other_users_comment(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_delete($comment, get_admin()->id));
    }

    public function test_is_allowed_to_update_own_comment(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_update($comment, $user->id));
    }

    public function test_is_not_allowed_to_update_own_comment_without_goal_view_capability(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $user_role = builder::table('role')->where('shortname', 'user')->one(true);
        unassign_capability('perform/goal:viewownpersonalgoals', $user_role->id);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_update($comment, $user->id));
    }

    public function test_is_not_allowed_to_update_other_users_comment(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->is_allow_to_update($comment, $manager->id));
    }

    public function test_site_admin_is_allowed_to_update_other_users_comment(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->is_allow_to_update($comment, get_admin()->id));
    }

    public function test_get_context_id(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment_resolver = new comment_resolver();

        self::assertSame($goal->get_context()->id, $comment_resolver->get_context_id($goal->id, 'goal_comment'));
    }
    public function test_get_context_id_with_invalid_area(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment_resolver = new comment_resolver();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Area is invalid');

        $comment_resolver->get_context_id($goal->id, 'fantasy_area');
    }

    public function test_get_owner_id_from_instance(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment_resolver = new comment_resolver();

        self::assertEquals($user->id, $comment_resolver->get_owner_id_from_instance('goal_comment', $goal->id));
    }

    public function test_get_owner_id_from_instance_with_invalid_area(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment_resolver = new comment_resolver();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Area is invalid');

        $comment_resolver->get_owner_id_from_instance('fantasy_area', $goal->id);
    }

    public function test_can_see_for_own_goal(): void {
        [$user, $goal] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->can_see_comments($goal->id, 'goal_comment', $user->id));
    }

    public function test_can_not_see_with_invalid_area(): void {
        [$user, $goal] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_see_comments($goal->id, 'fantasy_area', $user->id));
    }

    public function test_can_not_see_with_invalid_goal_id(): void {
        [$user, ] = $this->create_user_and_goal();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_see_comments( - 111, 'goal_comment', $user->id));
    }

    public function test_can_not_see_for_other_users_goal(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $user2 = self::getDataGenerator()->create_user();

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_see_comments($goal->id, 'goal_comment', $user2->id));
    }

    public function test_can_see_for_other_users_goal_with_view_capability(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->can_see_comments($goal->id, 'goal_comment', $manager->id));

        // Make sure it's the view capability that counts here.
        $manager_role = builder::table('role')->where('shortname', 'staffmanager')->one(true);
        unassign_capability('perform/goal:viewpersonalgoals', $manager_role->id);

        self::assertFalse($comment_resolver->can_see_comments($goal->id, 'goal_comment', $manager->id));
    }

    public function test_can_not_create_reaction_on_own_comment(): void {
        [$user, $goal] = $this->create_user_and_goal();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_create_reaction_on_comment($comment, $user->id));
    }

    public function test_can_create_reaction_on_comment_successful(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);

        $comment_resolver = new comment_resolver();
        self::assertTrue($comment_resolver->can_create_reaction_on_comment($comment, $manager->id));

        // Make sure it's the view capability that counts here.
        $manager_role = builder::table('role')->where('shortname', 'staffmanager')->one(true);
        unassign_capability('perform/goal:viewpersonalgoals', $manager_role->id);

        self::assertFalse($comment_resolver->can_create_reaction_on_comment($comment, $manager->id));
    }

    public function test_can_not_create_reaction_with_bad_area(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id, ['area' => 'fantasy_area']);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_create_reaction_on_comment($comment, $manager->id));
    }

    public function test_can_not_create_reaction_with_bad_component(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id, ['component' => 'mod_approval']);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_create_reaction_on_comment($comment, $manager->id));
    }

    public function test_can_not_create_reaction_with_bad_goal_id(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id, ['instanceid' => - 999]);

        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_create_reaction_on_comment($comment, $manager->id));
    }

    public function test_cannot_report_on_comments(): void {
        [$user, $goal, $manager] = $this->create_user_and_goal_and_manager();
        $comment = $this->goal_generator()->create_goal_comment($user->id, $goal->id);
        $comment_resolver = new comment_resolver();
        self::assertFalse($comment_resolver->can_report_comment($comment, $manager->id));
        self::assertFalse($comment_resolver->can_report_reply($comment, $manager->id));
    }

    /**
     * @return array
     */
    private function create_user_and_goal(): array {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        $data = goal_generator_config::new(['user_id' => $user->id]);
        $goal = goal_generator::instance()->create_goal($data);

        return [$user, $goal];
    }

    /**
     * @return array
     */
    private function create_user_and_goal_and_manager(): array {
        [$user, $goal] = $this->create_user_and_goal();

        $manager = self::getDataGenerator()->create_user();
        $manager_ja = job_assignment::create_default($manager->id);
        job_assignment::create_default($user->id, ['managerjaid' => $manager_ja->id]);

        return [$user, $goal, $manager];
    }
    
    private function goal_generator(): goal_generator {
        return goal_generator::instance();
    }
}
