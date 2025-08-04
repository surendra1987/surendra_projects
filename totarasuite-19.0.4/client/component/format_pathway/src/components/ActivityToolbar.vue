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
  <div class="tui-format_pathway-activityToolbar">
    <ButtonIcon
      v-if="showExpandButton"
      ref="expandButton"
      class="tui-format_pathway-activityToolbar__showCourseInformation"
      :class="{
        'tui-format_pathway-activityToolbar__showCourseInformation--noBackLink': !showBackLink,
      }"
      :aria-label="$str('navigation', 'format_pathway')"
      aria-expanded="false"
      :aria-controls="sidePanelId"
      :styleclass="{
        stealth: true,
      }"
      @click.prevent="$emit('expand-request')"
    >
      <LayoutSidebar />
    </ButtonIcon>
    <PageBackLink
      v-if="!$apollo.loading && showBackLink"
      class="tui-format_pathway-activityToolbar__backLink"
      :class="{
        'tui-format_pathway-activityToolbar__backLink--collapse': showExpandButton,
      }"
      :link="backLinkUrl"
      :text="backLinkText"
    />
    <Dropdown
      v-if="activitySettings.length > 0"
      :separator="false"
      :close-on-click="false"
      :fixed-width="hasChildren"
    >
      <template v-slot:trigger="{ toggle, isOpen }">
        <ButtonIcon
          :styleclass="{
            stealth: true,
          }"
          :aria-label="$str('showactivityadministration', 'format_pathway')"
          :aria-expanded="isOpen"
          @click="toggle"
        >
          <MoreIcon />
        </ButtonIcon>
      </template>
      <label class="tui-format_pathway-courseToolbar__header">
        {{ $str('activityadministration', 'format_pathway') }}
      </label>
      <SettingsTree
        v-model:value="openTreeBranches"
        no-padding
        :tree-data="activitySettings"
        is-dropdown
      />
    </Dropdown>
    <component
      :is="previousActivityUrl === null ? 'span' : 'a'"
      v-if="showNavigationButtons"
      :aria-disabled="previousActivityUrl === null ? true : null"
      :href="previousActivityUrl"
      class="tui-format_pathway-activityToolbar__link"
      :class="{
        'tui-format_pathway-activityToolbar__link--disabled': !previousActivityUrl,
      }"
      :title="$str('previousactivity', 'format_pathway')"
    >
      <BackArrow :alt="$str('previousactivity', 'format_pathway')" />
    </component>
    <component
      :is="nextActivityUrl === null ? 'span' : 'a'"
      v-if="showNavigationButtons"
      :href="nextActivityUrl"
      class="tui-format_pathway-activityToolbar__link"
      :class="{
        'tui-format_pathway-activityToolbar__link--disabled': !nextActivityUrl,
      }"
      :title="$str('nextactivity', 'format_pathway')"
    >
      <ForwardArrow :alt="$str('nextactivity', 'format_pathway')" />
    </component>
  </div>
</template>
<script>
import Dropdown from 'tui/components/dropdown/Dropdown';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import MoreIcon from 'tui/components/icons/More';
import SettingsTree from 'tui/components/settings_navigation/SettingsNavigationTree';
import ForwardArrow from 'tui/components/icons/ForwardArrow';
import BackArrow from 'tui/components/icons/BackArrow';
import LayoutSidebar from 'tui/components/icons/LayoutSidebar';
import { config } from 'tui/config';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import activitySettings from 'course/graphql/course_module_settings_navigation_tree';

export default {
  components: {
    Dropdown,
    ButtonIcon,
    MoreIcon,
    SettingsTree,
    ForwardArrow,
    BackArrow,
    LayoutSidebar,
    PageBackLink,
  },

  props: {
    showExpandButton: Boolean,
    previousActivityUrl: String,
    nextActivityUrl: String,
    sidePanelId: String,
    backLinkUrl: String,
    backLinkText: String,
    onActivityPage: Boolean,
    showBackLink: Boolean,
  },

  emits: ['expand-request'],

  data() {
    return {
      activitySettings: [],
      openTreeBranches: [],
    };
  },

  computed: {
    hasChildren() {
      return Boolean(
        this.activitySettings.find(({ children }) => children.length > 0)
      );
    },

    showNavigationButtons() {
      return (
        this.onActivityPage &&
        (this.previousActivityUrl !== null || this.nextActivityUrl !== null)
      );
    },
  },

  watch: {
    async showExpandButton(newVal, oldVal) {
      // We don't want focus on load (which is when oldVal is undefined)
      if (newVal && oldVal !== undefined) {
        await this.$nextTick();
        this.$refs.expandButton.$el.focus();
      }
    },
  },

  apollo: {
    activitySettings: {
      query: activitySettings,
      variables() {
        return {
          context_id: config.context.id,
          page_url: window.location.href,
        };
      },
      update({ data }) {
        return data.trees;
      },
    },
  },
};
</script>
<style lang="scss">
.tui-format_pathway-activityToolbar {
  display: flex;
  justify-content: flex-end;
  // Prevents jumping when the settings menu loads
  min-height: rem-px(36);

  &__showCourseInformation {
    &--noBackLink {
      margin-right: auto;
    }
  }

  &__link {
    padding: var(--gap-2);

    &--disabled {
      color: var(--color-text-disabled);
    }
  }

  &__header {
    @include font(h4);
    margin: 0;
    padding: var(--gap-2) var(--gap-4);
    color: var(--color-neutral-6);
  }

  &__backLink {
    margin-right: auto;
    &--collapse {
      margin-left: var(--gap-6);
    }
  }
}
</style>
