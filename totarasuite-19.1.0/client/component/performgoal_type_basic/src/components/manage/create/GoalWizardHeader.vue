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

  @module performgoal_type_basic
-->

<script setup>
import GoalIcon from 'tui/components/icons/Goal';

defineProps({
  title: {
    type: String,
    required: true,
  },
  titleId: String,
  subtitle: {
    type: String,
    required: true,
  },
});

defineEmits('close');
</script>

<template>
  <div class="tui-performGoalTypeBasic-goalWizardHeader">
    <div>
      <GoalIcon
        class="tui-performGoalTypeBasic-goalWizardHeader__icon"
        :size="null"
      />
    </div>
    <h3 :id="titleId" class="tui-performGoalTypeBasic-goalWizardHeader__title">
      {{ title }}
    </h3>
    <p class="tui-performGoalTypeBasic-goalWizardHeader__subtitle">
      {{ subtitle }}
    </p>
  </div>
</template>

<style lang="scss">
.tui-performGoalTypeBasic-goalWizardHeader {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: var(--gap-8);
  text-align: center;

  &__icon {
    margin-bottom: gap(2);
    font-size: rem-px(48);
  }

  &__title {
    margin-top: 0;
    margin-bottom: var(--gap-2);
    line-height: 1.75;
  }

  &__subtitle {
    max-width: 26rem;
    margin-top: 0;
    margin-right: auto;
    margin-bottom: var(--gap-4);
    margin-left: auto;
    color: var(--color-text-hint);
    line-height: 1.25;
  }
}
</style>
