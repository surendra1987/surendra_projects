<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module totara_dashboard
-->

<script setup>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import MoreIcon from 'tui/components/icons/More';
import SettingsIcon from 'tui/components/icons/Settings';

defineProps({
  cloneUrl: String,
  deleteUrl: String,
  editUrl: String,
  moveDownUrl: String,
  moveTopUrl: String,
  moveUpUrl: String,
});

function handleActionClick(url) {
  window.location.href = url;
}
</script>

<template>
  <div class="tui-totara_dashboard-actions">
    <!-- Settings button -->
    <ButtonIcon
      :aria-label="$str('editdashboard', 'totara_dashboard')"
      :styleclass="{ stealth: true }"
      size="sm"
      :href="editUrl"
    >
      <SettingsIcon />
    </ButtonIcon>

    <!-- Actions dropdown -->
    <Dropdown
      context-mode="uncontained"
      position="bottom-right"
      :separator="false"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <ButtonIcon
          :aria-label="$str('actions', 'core')"
          :aria-expanded="isOpen"
          :styleclass="{ stealth: true }"
          size="sm"
          @click.prevent="toggle"
        >
          <MoreIcon />
        </ButtonIcon>
      </template>

      <DropdownButton v-if="cloneUrl" @click="handleActionClick(cloneUrl)">
        {{ $str('manage_action_clone', 'totara_dashboard') }}
      </DropdownButton>
      <DropdownButton v-if="moveUpUrl" @click="handleActionClick(moveUpUrl)">
        {{ $str('manage_action_up', 'totara_dashboard') }}
      </DropdownButton>
      <DropdownButton
        v-if="moveDownUrl"
        @click="handleActionClick(moveDownUrl)"
      >
        {{ $str('manage_action_down', 'totara_dashboard') }}
      </DropdownButton>
      <DropdownButton v-if="moveTopUrl" @click="handleActionClick(moveTopUrl)">
        {{ $str('manage_action_top', 'totara_dashboard') }}
      </DropdownButton>
      <DropdownButton v-if="deleteUrl" @click="handleActionClick(deleteUrl)">
        {{ $str('manage_action_delete', 'totara_dashboard') }}
      </DropdownButton>
    </Dropdown>
  </div>
</template>

<style lang="scss">
.tui-totara_dashboard-actions {
  display: flex;
}
</style>
