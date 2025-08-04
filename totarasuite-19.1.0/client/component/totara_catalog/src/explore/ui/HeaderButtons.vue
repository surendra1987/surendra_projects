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

  @author Simon Chester <simon.chester@totara.com>
  @module tui
-->

<script setup>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import MoreIcon from 'tui/components/icons/More';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';

defineProps({
  buttons: Object,
});
</script>

<template>
  <div class="tui-totara_catalog-exploreHeaderButtons">
    <Dropdown
      v-if="Object.keys(buttons.extra).length"
      position="bottom-right"
      :separator="false"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <ButtonIcon
          variant="stealth"
          :aria-label="$str('more', 'core')"
          :aria-expanded="isOpen"
          @click="toggle"
        >
          <MoreIcon />
        </ButtonIcon>
      </template>
      <DropdownItem
        v-for="(button, i) in buttons.extra"
        :key="i"
        :href="button.url"
      >
        {{ button.label }}
      </DropdownItem>
    </Dropdown>

    <Dropdown
      v-if="Object.keys(buttons.create).length"
      position="bottom-right"
      :separator="false"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <Button
          :text="$str('createnew', 'core')"
          caret
          :aria-expanded="isOpen"
          @click="toggle"
        />
      </template>
      <DropdownItem
        v-for="(button, i) in buttons.create"
        :key="i"
        :href="button.url"
      >
        {{ button.label }}
      </DropdownItem>
    </Dropdown>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-exploreHeaderButtons {
  display: flex;
  gap: var(--gap-1);
}
</style>
