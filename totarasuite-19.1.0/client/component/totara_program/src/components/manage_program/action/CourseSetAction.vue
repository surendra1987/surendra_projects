<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD’s customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Gihan Hewaralalage <gihan.hewaralalage@totara.com>
  @module totara_program
-->
<template>
  <Dropdown class="tui-totara_program-courseSetAction">
    <template v-slot:trigger="{ toggle, isOpen }">
      <MoreIcon
        :aria-expanded="isOpen ? 'true' : 'false'"
        :aria-label="$str('actions_for', 'totara_program', courseSetTitle)"
        :styleclass="{ small: true, transparentNoPadding: true }"
        :size="300"
        @click="toggle"
      />
    </template>

    <DropdownItem
      :title="$str('edit_course_set_name', 'totara_program', courseSetTitle)"
      :aria-label="
        $str('edit_course_set_name', 'totara_program', courseSetTitle)
      "
      @click="$emit('edit-course-set')"
    >
      <!-- Edit the notification -->
      {{ $str('edit', 'core') }}
    </DropdownItem>

    <DropdownItem
      :disabled="!canCourseSetMoveUp"
      :title="$str('move_course_set_up', 'totara_program', courseSetTitle)"
      :aria-label="$str('move_course_set_up', 'totara_program', courseSetTitle)"
      @click="$emit('move-course-set-up')"
    >
      <!-- Edit the notification -->
      {{ $str('moveup', 'totara_program') }}
    </DropdownItem>

    <DropdownItem
      :disabled="!canCourseSetMoveDown"
      :title="$str('move_course_set_down', 'totara_program', courseSetTitle)"
      :aria-label="
        $str('move_course_set_down', 'totara_program', courseSetTitle)
      "
      @click="$emit('move-course-set-down')"
    >
      <!-- Edit the notification -->
      {{ $str('movedown', 'totara_program') }}
    </DropdownItem>

    <DropdownItem
      v-if="isDeletable"
      :title="$str('delete_course_set_name', 'totara_program', courseSetTitle)"
      :aria-label="
        $str('delete_course_set_name', 'totara_program', courseSetTitle)
      "
      @click="$emit('delete-course-set')"
    >
      <!-- Edit the notification -->
      {{ $str('delete', 'core') }}
    </DropdownItem>
  </Dropdown>
</template>

<script>
import Dropdown from 'tui/components/dropdown/Dropdown';
import MoreIcon from 'tui/components/buttons/MoreIcon';
import DropdownItem from 'tui/components/dropdown/DropdownItem';

export default {
  components: {
    Dropdown,
    MoreIcon,
    DropdownItem,
  },

  props: {
    courseSetTitle: {
      type: String,
      required: false,
      default: '',
    },
    isDeletable: {
      type: Boolean,
      required: true,
      default: true,
    },
    canCourseSetMoveUp: {
      type: Boolean,
      required: true,
      default: true,
    },
    canCourseSetMoveDown: {
      type: Boolean,
      required: true,
      default: true,
    },
  },

  emits: [
    'edit-course-set',
    'move-course-set-up',
    'move-course-set-down',
    'delete-course-set',
  ],
};
</script>
