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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module perform_goal
-->

<template>
  <div
    v-if="settings.enable_status_change"
    class="tui-performGoalLinkedReviewChangeStatusPreview"
  >
    <h2 class="tui-performGoalLinkedReviewChangeStatusPreview__title">
      <span :id="$id('status_title')">
        {{ $str('goal_progression_update', 'perform_goal') }}
      </span>
      <span
        class="tui-performGoalLinkedReviewChangeStatusPreview__title-required"
      >
        <span aria-hidden="true">*</span>
        <span class="sr-only">{{ $str('required', 'core') }}</span>
      </span>
    </h2>

    <div class="tui-performGoalLinkedReviewChangeStatusPreview__content">
      <div>
        {{ $str('goal_awaiting_progress_update', 'perform_goal') }}
      </div>
      <div
        class="tui-performGoalLinkedReviewChangeStatusPreview__content-progress"
      >
        {{ $str('goal_example_progress_message', 'perform_goal') }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: { settings: Object },
};
</script>

<style lang="scss">
.tui-performGoalLinkedReviewChangeStatusPreview {
  display: flex;
  flex-direction: column;
  gap: var(--gap-3);
  padding: var(--gap-4) 0;
  border-top: var(--border-width-thin) solid var(--color-neutral-7);
  border-bottom: var(--border-width-normal) solid var(--color-neutral-7);

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);

    &-progress {
      @include font(body-sm);
      color: var(--color-neutral-6);
    }
  }

  &__title {
    @include font(h4);
    margin: 0;

    &-required {
      color: var(--color-prompt-alert);
      font-weight: var(--label-weight);
    }
  }
}
</style>
