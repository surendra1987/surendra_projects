<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

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
  <Dropdown class="tui-performGoalsActionsMenu" position="bottom-right">
    <template v-slot:trigger="{ toggle, isOpen }">
      <ButtonIcon
        :aria-label="$str('goal_manage_actions', 'perform_goal')"
        :styleclass="{
          small: true,
          transparent: true,
        }"
        :aria-expanded="isOpen"
        @click="toggle"
      >
        <MoreIcon />
      </ButtonIcon>
    </template>

    <DropdownItem v-if="canUpdateStatus" @click="goalUpdate">
      {{ $str('goal_update_progress', 'perform_goal') }}
    </DropdownItem>
    <DropdownItem @click="goalEdit">
      {{ $str('goal_manage_edit', 'perform_goal') }}
    </DropdownItem>
    <DropdownItem @click="goalDelete">
      {{ $str('goal_manage_delete', 'perform_goal') }}
    </DropdownItem>
  </Dropdown>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MoreIcon from 'tui/components/icons/More';

export default {
  components: {
    ButtonIcon,
    Dropdown,
    DropdownItem,
    MoreIcon,
  },

  props: {
    // Should we include the update status button
    canUpdateStatus: { type: Boolean },
    // Current goal ID
    goalId: { type: [String, Number], required: true },
  },

  emits: ['goalDelete', 'goalEdit', 'goalUpdate'],

  methods: {
    /**
     * Goal delete clicked
     *
     */
    goalDelete() {
      this.$emit('goalDelete', this.goalId);
    },

    /**
     * Goal edit clicked
     *
     */
    goalEdit() {
      this.$emit('goalEdit', this.goalId);
    },

    /**
     * Goal update clicked
     *
     */
    goalUpdate() {
      this.$emit('goalUpdate', this.goalId);
    },
  },
};
</script>
