<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module totara_engage
-->

<template>
  <Dropdown position="bottom-right" :separator="false">
    <template v-slot:trigger="{ toggle, isOpen }">
      <slot name="trigger" :toggle="toggle">
        <Button
          :aria-expanded="isOpen ? 'true' : 'false'"
          :aria-label="$str('createnew', 'totara_engage')"
          :caret="true"
          :text="$str('createnew', 'totara_engage')"
          @click="toggle"
        />
      </slot>
    </template>

    <slot name="dropdown-items">
      <DropdownItem
        v-if="showCreateResource"
        @click="$emit('create-resource')"
        >{{ $str('resource', 'totara_engage') }}</DropdownItem
      >
      <DropdownItem
        v-if="showCreatePlaylist"
        @click="$emit('create-playlist')"
        >{{ $str('playlist', 'totara_engage') }}</DropdownItem
      >
    </slot>
  </Dropdown>
</template>

<script>
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Button from 'tui/components/buttons/Button';

export default {
  components: {
    Dropdown,
    DropdownItem,
    Button,
  },

  props: {
    showCreateResource: {
      type: Boolean,
    },
    showCreatePlaylist: {
      type: Boolean,
    },
  },

  emits: ['create-resource', 'create-playlist'],
};
</script>
