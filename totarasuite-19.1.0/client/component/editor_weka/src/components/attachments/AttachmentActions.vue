<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Cody Finegan <cody.finegan@totara.com>
  @module editor_weka
-->

<template>
  <div
    v-weka-node-ui
    class="tui-editor_weka-attachmentActions"
    :contenteditable="false"
  >
    <ActionDropdown
      position="bottom-right"
      :actions="actions"
      context-mode="uncontained"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <div class="tui-editor_weka-attachmentActions__actionsWrap">
          <ButtonIcon
            :styleclass="{ small: true, stealth: true }"
            :aria-expanded="isOpen.toString()"
            :aria-label="$str('actions_menu_for', 'editor_weka', filename)"
            :data-weka-more="true"
            @click="toggle"
          >
            <More />
          </ButtonIcon>
        </div>
      </template>
    </ActionDropdown>
  </div>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import More from 'tui/components/icons/More';
import ActionDropdown from 'editor_weka/components/editing/ActionDropdown';
import { wekaNodeUI } from '../directives';

export default {
  directives: {
    'weka-node-ui': wekaNodeUI,
  },

  components: {
    ActionDropdown,
    ButtonIcon,
    More,
  },

  props: {
    filename: {
      type: String,
      required: true,
    },

    actions: {
      type: Array,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-editor_weka-attachmentActions {
  &__actionsWrap {
    display: flex;
  }
}
</style>
