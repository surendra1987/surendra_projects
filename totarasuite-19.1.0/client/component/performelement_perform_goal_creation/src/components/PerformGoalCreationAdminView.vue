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

  @author Matthias Bonk <matthias.bonk@totara.com>
  @module performelement_perform_goal_creation
-->

<template>
  <div class="tui-performGoalCreationAdminView">
    <div>
      {{ $str('admin_view_message', 'performelement_perform_goal_creation') }}
    </div>

    <ComponentLoading v-if="$apollo.loading" />
    <Card
      v-else
      class="tui-performGoalCreationAdminView__item"
      :no-border="true"
    >
      <div class="tui-performGoalCreationAdminView__itemHeading">
        <h3 class="tui-performGoalCreationAdminView__itemHeading-title">
          {{
            $str(
              'admin_view_example_name',
              'performelement_perform_goal_creation'
            )
          }}
        </h3>
      </div>

      <div class="tui-performGoalCreationAdminView__itemContent">
        <div class="tui-performGoalCreationAdminView__itemContent-date">
          {{
            $str(
              'perform_goal_updated_on',
              'performelement_perform_goal_creation',
              dateToday
            )
          }}
        </div>

        <div class="tui-performGoalCreationAdminView__itemContent-box">
          <div class="tui-performGoalCreationAdminView__bar">
            <!-- Start date -->
            <div class="tui-performGoalCreationAdminView__bar-item">
              <h4 class="tui-performGoalCreationAdminView__bar-heading">
                {{ $str('goal_start_date', 'perform_goal') }}
              </h4>

              {{ dateToday }}
            </div>

            <!-- Due date -->
            <div class="tui-performGoalCreationAdminView__bar-item">
              <h4 class="tui-performGoalCreationAdminView__bar-heading">
                {{ $str('goal_due_date', 'perform_goal') }}
              </h4>

              {{ dateToday }}
            </div>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
import Card from 'tui/components/card/Card';
import ComponentLoading from 'tui/components/loading/ComponentLoading';

// GraphQL
import dateTodayQuery from 'totara_webapi/graphql/status';

export default {
  components: {
    Card,
    ComponentLoading,
  },

  data() {
    return {
      dateToday: '',
    };
  },

  apollo: {
    dateToday: {
      query: dateTodayQuery,
      update({ totara_webapi_status }) {
        return totara_webapi_status.date;
      },
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalCreationAdminView {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4) 0;

  &__item {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    gap: var(--gap-2) 0;
    padding: var(--gap-4);
    background: var(--color-neutral-3);
  }

  &__itemHeading {
    @include font(h4);
    display: flex;

    &-title {
      @include font(h4);
      margin: 0;
    }
  }

  &__itemContent {
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);

    &-date {
      @include font(body-sm);
    }

    &-box {
      display: flex;
      padding: var(--gap-4);
      background: var(--color-neutral-1);
      border: var(--border-width-thin) solid var(--color-neutral-5);
    }
  }

  &__bar {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
    @include font(body-sm);

    &-item {
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
  .tui-performGoalCreationAdminView {
    &__bar {
      flex-direction: row;
      gap: var(--gap-6);
    }
  }
}
</style>
