<?php
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
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



use bi_intellidata\helpers\StorageHelper;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use core_phpunit\testcase;

global $CFG;
require_once($CFG->dirroot . '/mod/forum/externallib.php');

/**
 * User migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_forumpost_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\forums\forumpost
     * @covers \bi_intellidata\entities\forums\postsmigration
     * @covers \bi_intellidata\entities\forums\observer::post_created
     */
    public function test_create() {
        $gen = self::getDataGenerator();
        $userdata = [
            'firstname' => 'ibforumuserforumpost1',
            'username' => 'ibforumuserforumpost1',
            'password' => 'Ibforumuserforumpost1!',
        ];
        $user = $gen->create_user($userdata);

        $coursedata = [
            'fullname' => 'ibcourseforumpost1',
            'idnumber' => '3333333',
        ];
        $course = $gen->create_course($coursedata);

        $forumdata = [
            'course' => $course->id
        ];
        $forum = $gen->create_module('forum', $forumdata);

        // Add a discussion.
        $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $gen->get_plugin_generator('mod_forum')->create_discussion($record);

        // Add a post.
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $gen->get_plugin_generator('mod_forum')->create_post($record);

        $context = forum_get_context($forum->id);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => [
                'discussionid' => $discussion->id,
                'forumid' => $forum->id,
                'forumtype' => $forum->type
            ]
        );

        // Create the event.
        $event = \mod_forum\event\post_created::create($params);
        $event->trigger();

        $data = [
            'id' => $post->id,
            'userid' => $user->id,
            'forum' => $forum->id,
            'discussion' => $discussion->id,
        ];

        $entity = new \bi_intellidata\entities\forums\forumpost((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'forumposts']);

        $datarecord = $storage->get_log_entity_data('c', $data);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\forums\forumpost
     * @covers \bi_intellidata\entities\forums\postsmigration
     * @covers \bi_intellidata\entities\forums\observer::post_updated
     */
    public function test_update() {
        global $DB;
        $gen = self::getDataGenerator();

        $this->test_create();

        $userdata = [
            'username' => 'ibforumuserforumpost1',
        ];
        $user = $DB->get_record('user', $userdata);

        $coursedata = [
            'fullname' => 'ibcourseforumpost1',
            'idnumber' => '3333333',
        ];
        $course = $DB->get_record('course', $coursedata);

        $forumdata = [
            'course' => $course->id
        ];
        $forum = $DB->get_record('forum', $forumdata);

        // Add a discussion.
        $record = [];
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $gen->get_plugin_generator('mod_forum')->create_discussion($record);

        // Add a post.
        $record = [];
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $gen->get_plugin_generator('mod_forum')->create_post($record);

        $context = forum_get_context($forum->id);

        $params = [
            'context' => $context,
            'objectid' => $post->id,
            'other' => [
                'discussionid' => $discussion->id,
                'forumid' => $forum->id,
                'forumtype' => $forum->type
            ]
        ];

        $event = \mod_forum\event\post_updated::create($params);
        $event->trigger();

        $data = [
            'id' => $post->id,
            'userid' => $user->id,
            'forum' => $forum->id,
            'discussion' => $discussion->id,
        ];

        $entity = new \bi_intellidata\entities\forums\forumpost((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'forumposts']);

        $datarecord = $storage->get_log_entity_data('u', $data);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\forums\forumpost
     * @covers \bi_intellidata\entities\forums\postsmigration
     * @covers \bi_intellidata\entities\forums\observer::post_deleted
     */
    public function test_delete() {
        global $DB;

        $this->test_create();

        $userdata = [
            'username' => 'ibforumuserforumpost1',
        ];
        $user = $DB->get_record('user', $userdata);

        $coursedata = [
            'fullname' => 'ibcourseforumpost1',
            'idnumber' => '3333333',
        ];
        $course = $DB->get_record('course', $coursedata);

        $forumdata = [
            'course' => $course->id
        ];
        $forum = $DB->get_record('forum', $forumdata);

        $cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course);

        $gen = self::getDataGenerator();
        // Add a discussion.
        $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $gen->get_plugin_generator('mod_forum')->create_discussion($record);

        // When creating a discussion we also create a post, so get the post.
        $discussionpost = $DB->get_records('forum_posts');
        // Will only be one here.
        $discussionpost = reset($discussionpost);

        // Add a few posts.
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $posts = array();
        $posts[$discussionpost->id] = $discussionpost;
        for ($i = 0; $i < 3; $i++) {
            $post = $gen->get_plugin_generator('mod_forum')->create_post($record);
            $posts[$post->id] = $post;
        }

        // Delete the last post and capture the event.
        $lastpost = end($posts);
        forum_delete_post($lastpost, true, $course, $cm, $forum);

        $data = [
            'id' => $lastpost->id,
        ];

        $entity = new \bi_intellidata\entities\forums\forumpost((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'forumposts']);
        $datarecord = $storage->get_log_entity_data('d', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }
}