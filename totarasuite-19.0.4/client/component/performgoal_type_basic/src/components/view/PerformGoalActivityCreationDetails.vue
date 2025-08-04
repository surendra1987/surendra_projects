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
  @module performgoal_type_basic
-->

<!-- Used in performance activity goal creation element -->
<template>
  <HideShow
    :aria-region-label="$str('a11y_goal_additional_details', 'perform_goal')"
    class="tui-performGoalActivityCreationDetails"
    :initially-expanded="fromPrint"
    :narrow-trigger="true"
  >
    <template v-slot:trigger="{ controls, expanded, toggleContent }">
      <div class="tui-performGoalActivityCreationDetails__bar">
        <div class="tui-performGoalActivityCreationDetails__bar-list">
          <!-- Start date -->
          <div class="tui-performGoalActivityCreationDetails__bar-listItem">
            <h4 class="tui-performGoalActivityCreationDetails__bar-heading">
              {{ $str('goal_start_date', 'perform_goal') }}
            </h4>

            {{ goal.start_date }}
          </div>

          <!-- Due date -->
          <div class="tui-performGoalActivityCreationDetails__bar-listItem">
            <h4 class="tui-performGoalActivityCreationDetails__bar-heading">
              {{ $str('goal_due_date', 'perform_goal') }}
            </h4>

            {{ goal.target_date }}
          </div>
        </div>

        <div class="tui-performGoalActivityCreationDetails__bar-expand">
          <ButtonIcon
            v-if="!fromPrint"
            :aria-controls="controls"
            :aria-label="false"
            :styleclass="{ small: true, transparentNoPadding: true }"
            :text="
              expanded
                ? $str('show_less_details', 'perform_goal')
                : $str('show_more_details', 'perform_goal')
            "
            @click="toggleContent"
          >
            <component
              :is="expanded ? 'CollapseIcon' : 'ExpandIcon'"
              aria-hidden="true"
              size="100"
            />
          </ButtonIcon>
        </div>
      </div>
    </template>

    <template v-slot:content>
      <div class="tui-performGoalActivityCreationDetails__content">
        <!-- Description -->
        <div
          v-if="goal.description"
          class="tui-performGoalActivityCreationDetails__item"
        >
          <h4 class="tui-performGoalActivityCreationDetails__item-heading">
            {{ $str('goal_form_label_description', 'perform_goal') }}
          </h4>

          <div v-html="goal.description" />
        </div>

        <!-- Target value -->
        <div class="tui-performGoalActivityCreationDetails__item">
          <h4 class="tui-performGoalActivityCreationDetails__item-heading">
            {{ $str('goal_form_label_target_value', 'perform_goal') }}
          </h4>

          {{ parseFloat(goal.target_value) }}
        </div>
      </div>
    </template>
  </HideShow>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import CollapseIcon from 'tui/components/icons/Collapse';
import ExpandIcon from 'tui/components/icons/Expand';
import HideShow from 'tui/components/collapsible/HideShow';

export default {
  components: {
    ButtonIcon,
    CollapseIcon,
    ExpandIcon,
    HideShow,
  },

  props: {
    fromPrint: { type: Boolean },
    goal: { type: Object, required: true },
  },
};
</script>

<style lang="scss">
.tui-performGoalActivityCreationDetails {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: var(--gap-6);

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-6);
  }

  &__item {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);

    &-heading {
      margin: 0;
      @include font(h6);
    }
  }

  &__bar {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    gap: var(--gap-4);

    &-list {
      display: flex;
      flex-direction: column;
      gap: var(--gap-2);
    }

    &-listItem {
      display: flex;
      gap: var(--gap-2);
    }

    &-heading {
      margin: 0;
      @include font(h6);
    }
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performGoalActivityCreationDetails {
    &__bar {
      flex-direction: row;
      justify-content: space-between;

      &-list {
        flex-direction: row;
        gap: var(--gap-6);
      }
    }
  }
}
</style>
