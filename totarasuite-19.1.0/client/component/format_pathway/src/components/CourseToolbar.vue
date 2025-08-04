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

  @author Brian Barnes <brian.barnes@totara.com>
  @module format_pathway
-->
<template>
  <div class="tui-format_pathway-courseToolbar">
    <ButtonIcon
      v-if="expanded"
      ref="collapseButton"
      :aria-label="$str('navigation', 'format_pathway')"
      aria-expanded="true"
      :aria-controls="sidePanelId"
      :styleclass="{
        stealth: true,
      }"
      @click.prevent="$emit('collapse-request')"
    >
      <LayoutSidebar />
    </ButtonIcon>

    <Dropdown
      v-if="courseSettings.length > 0 && expanded && showCourseSettings"
      :separator="false"
      :close-on-click="false"
      :fixed-width="hasChildren"
      class="tui-format_pathway-courseToolbar__settings"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <ButtonIcon
          :styleclass="{
            stealth: true,
          }"
          :aria-label="$str('showcourseadministration', 'format_pathway')"
          :aria-expanded="isOpen"
          @click="toggle"
        >
          <MoreIcon />
        </ButtonIcon>
      </template>
      <label class="tui-format_pathway-courseToolbar__header">
        {{ $str('courseadministration', 'format_pathway') }}
      </label>
      <SettingsTree
        v-model:value="openTreeBranches"
        no-padding
        :tree-data="courseSettings"
        is-dropdown
      />
    </Dropdown>
  </div>
</template>
<script>
import Dropdown from 'tui/components/dropdown/Dropdown';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import MoreIcon from 'tui/components/icons/More';
import SettingsTree from 'tui/components/settings_navigation/SettingsNavigationTree';
import LayoutSidebar from 'tui/components/icons/LayoutSidebar';

export default {
  components: {
    Dropdown,
    ButtonIcon,
    MoreIcon,
    SettingsTree,
    LayoutSidebar,
  },

  props: {
    expanded: Boolean,
    showCourseSettings: Boolean,
    courseSettings: Array,
    sidePanelId: String,
  },

  emits: ['collapse-request'],

  data() {
    return {
      openTreeBranches: [],
    };
  },

  computed: {
    hasChildren() {
      return Boolean(
        this.courseSettings.find(({ children }) => children.length > 0)
      );
    },
  },

  watch: {
    async expanded(newVal, oldVal) {
      // We don't want focus on load (which is when oldVal is undefined)
      if (newVal && oldVal !== undefined) {
        await this.$nextTick();
        this.$refs.collapseButton.$el.focus();
      }
    },
  },
};
</script>
<style lang="scss">
.tui-format_pathway-courseToolbar {
  display: flex;
  justify-content: space-between;
  // Prevents jumping when the settings menu loads
  min-height: rem-px(36);

  &__header {
    @include font(h4);
    margin: 0;
    padding: var(--gap-2) var(--gap-4);
    color: var(--color-neutral-6);
  }
}
</style>
