<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package perform_goal
-->

<template>
  <Tabs class="tui-performGoalDetailsExtra">
    <Tab id="tasks" :name="$str('goal_tasks', 'perform_goal')">
      <TaskList
        :goal="goal"
        :permissions="permissions"
        @content-update="$emit('content-update')"
      />
    </Tab>
    <Tab
      id="comments"
      class="tui-performGoalDetailsExtra__tab"
      :name="$str('goal_comments', 'perform_goal')"
    >
      <Comments :goal="goal" @content-update="$emit('content-update')" />
    </Tab>
  </Tabs>
</template>

<script>
import Comments from 'perform_goal/components/comments/PerformGoalComments';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import TaskList from 'perform_goal/components/tasks/PerformGoalTaskList';

export default {
  components: {
    Comments,
    Tab,
    Tabs,
    TaskList,
  },

  props: {
    // Goal data
    goal: { type: Object, required: true },
    // Goal permissions
    permissions: { type: Object, required: true },
  },

  emits: ['content-update'],
};
</script>

<style lang="scss">
.tui-performGoalDetailsExtra {
  display: flex;
  flex-direction: column;
  height: 100%;

  .tui-tabs__panels {
    height: 100%;
  }

  &__tab {
    height: 100%;
  }
}
</style>
