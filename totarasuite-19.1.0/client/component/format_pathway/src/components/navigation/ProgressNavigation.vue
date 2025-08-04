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

  @author Aaron Machin <aaron.machin@totara.com>
  @author Jack Humphrey <jack.humphrey@totara.com>
  @module format_pathway
-->

<template>
  <div
    class="tui-format_pathway-progressNavigation"
    :class="{
      'tui-format_pathway-progressNavigation--limitHeight': limitHeight,
    }"
  >
    <h2 class="sr-only">{{ $str('navigation', 'format_pathway') }}</h2>
    <div v-for="section in sections" :key="section.id">
      <Collapsible
        v-if="sectionHasActivities(section.id)"
        :initial-state="true"
        label=""
        variant="minimal"
        indent-contents
      >
        <template v-slot:label-extra>
          <div
            class="tui-format_pathway-progressNavigation__sectionTitle"
            :class="{
              'tui-format_pathway-progressNavigation__sectionTitle--hidden': !section.visible,
            }"
            :title="limitHeight ? section.title : ''"
          >
            {{ section.title }}
          </div>
        </template>
        <template v-if="!section.visible" v-slot:collapsible-side-content>
          <HiddenIcon
            :alt="$str('completionstatus_hidden', 'totara_tui')"
            :size="300"
            class="tui-format_pathway-progressNavigation__sectionTitleIcon"
          />
        </template>
        <div class="tui-format_pathway-progressNavigation__progressTrackerNav">
          <ProgressTrackerNav
            :force-vertical="true"
            :item-content-full-width="true"
            :items="activities[section.id]"
            :popover-trigger-type="['click']"
            :hide-value="true"
            gap="small"
            marker-mode="workflow"
          >
            <template v-slot="{ entry }">
              <ProgressTrackerItem
                class="tui-format_pathway-progressNavigation__activityLink"
                text=""
                :selected="entry.id == activeCourseModuleId"
                :hidden="entry.states.includes('hidden')"
                :available="entry.available || canViewHidden"
                :href="entry.viewurl"
              >
                <template v-slot:text-extra>
                  <div
                    class="tui-format_pathway-progressNavigation__activityName"
                    :title="limitHeight ? entry.name : ''"
                  >
                    {{ entry.name }}
                  </div>
                </template>
              </ProgressTrackerItem>
            </template>
            <template v-slot:custom-popover-content="{ description }">
              <div v-for="(d, index) in description" :key="index" v-html="d" />
            </template>
          </ProgressTrackerNav>
        </div>
      </Collapsible>
    </div>
  </div>
</template>

<script>
import ProgressTrackerItem from 'format_pathway/components/navigation/ProgressTrackerItem';
import ProgressTrackerNav from 'tui/components/progresstracker/ProgressTrackerNav';
import Collapsible from 'tui/components/collapsible/Collapsible';
import HiddenIcon from 'tui/components/icons/Hidden';

import { Completion } from 'format_pathway/activityCompletion';

export default {
  name: 'ProgressNavigation',

  components: {
    ProgressTrackerItem,
    ProgressTrackerNav,
    Collapsible,
    HiddenIcon,
  },

  props: {
    activeCourseModuleId: Number,
    canViewHidden: Boolean,
    navigationData: {
      type: Object,
      required: true,
    },
    completionData: Array,
  },

  data() {
    return {
      limitHeight: window.matchMedia('(hover: hover)').matches,
    };
  },

  computed: {
    completions() {
      if (!this.completionData) {
        return {};
      }

      return this.completionData.reduce(
        (result, completionItem) =>
          Object.assign(result, { [completionItem.cmid]: completionItem }),
        {}
      );
    },

    activities() {
      if (!this.navigationData || !this.navigationData.sections) {
        return {};
      }

      let activities = {};
      this.navigationData.sections.forEach(section => {
        let items = [];
        section.modules.forEach(activity => {
          let states = [];
          let description = [];

          if (!(section.visible && activity.visible)) {
            states.push('hidden');
          } else if (activity.available) {
            states.push('ready');
          } else {
            states.push('locked');
          }

          const activityCompletionData = this.getCompletionData(activity.cmid);

          if (
            activityCompletionData.activitycompletiontracking ===
            Completion.NONE
          ) {
            states.push('optional');
          }

          if (activityCompletionData.completionstatus) {
            states.push('done');
          }

          if (activity.cmid == this.activeCourseModuleId) {
            states.push('selected');
          }

          if (activity.blacklisted) {
            states.push('invalid');
          }

          if (activityCompletionData.completionstatus) {
            if (activityCompletionData.completion_status_description) {
              description = [
                activityCompletionData.completion_status_description,
              ];
            } else {
              description = [this.$str('completed', 'core_completion')];
            }
          } else if (activity.availablereason.length > 0) {
            description = activity.availablereason;
          } else if (
            activityCompletionData.activitycompletiontracking == Completion.NONE
          ) {
            description = [this.$str('statusnottracked', 'core_completion')];
          } else {
            description = [this.$str('notcompleted', 'core_completion')];
          }

          items.push({
            id: activity.cmid,
            name: activity.name,
            description,
            modtype: activity.modtype,
            available: activity.available,
            states,
            viewurl: activity.viewurl,
          });
        });
        activities[section.id] = items;
      });

      return activities;
    },

    sections() {
      if (!this.navigationData || !this.navigationData.sections) {
        return [];
      }

      // Deep clone navigationData.sections
      const sections = JSON.parse(JSON.stringify(this.navigationData.sections));

      // First topic has special handling: requires title to be set to default if no title given
      if (sections.length > 0 && sections[0].title === '') {
        sections[0].title = this.$str('section0name', 'format_pathway');
      }

      return sections;
    },
  },

  methods: {
    sectionHasActivities(id) {
      return this.activities[id].length > 0;
    },

    getCompletionData(cmid) {
      if (!this.completions || !this.completions[cmid]) {
        return {};
      }

      return this.completions[cmid];
    },
  },
};
</script>

<style lang="scss">
.tui-format_pathway-progressNavigation {
  &__progressTrackerNav {
    padding-top: var(--gap-2);
  }

  &__sectionTitle {
    &--hidden {
      color: var(--progresstracker-color-hidden);
    }
  }

  &__sectionTitleIcon {
    color: var(--progresstracker-color-hidden);
  }

  &--limitHeight &__sectionTitle {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
  }

  &--limitHeight &__activityName {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
  }
}
</style>
