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
import ModalContent from 'tui/components/modal/ModalContent';
import Loader from 'tui/components/loading/Loader';
import GoalWizardHeader from 'performgoal_type_basic/components/manage/create/GoalWizardHeader';
import { computed } from 'vue';

const props = defineProps({
  // Title ID so the modal can reference it
  titleId: { type: String, required: true },
  loading: Boolean,
  options: Array,
});

defineEmits(['choose', 'cancel']);

const computedOptions = computed(() =>
  (props.options ?? []).map(option => ({
    ...option,
    icon_component: tui.asyncComponent(option.icon_component),
  }))
);
</script>

<template>
  <ModalContent close-button>
    <!-- suppress title warning -->
    <template v-slot:title />
    <div class="tui-performGoalTypeBasic-methodChooser">
      <GoalWizardHeader
        :title="$str('goal_creation_choice_title', 'goaltype_basic')"
        :title-id="titleId"
        :subtitle="$str('goal_creation_choice_subtitle', 'goaltype_basic')"
      />

      <h4 class="tui-performGoalTypeBasic-methodChooser__sectionTitle">
        {{ $str('goal_creation_choice_options_title', 'goaltype_basic') }}
      </h4>

      <div class="tui-performGoalTypeBasic-methodChooser__options">
        <div v-if="computedOptions.length === 0 && loading">
          <Loader loading />
        </div>
        <template v-for="(option, index) in computedOptions" :key="index">
          <div
            v-if="index !== 0"
            class="tui-performGoalTypeBasic-methodChooser__separator"
          >
            {{ $str('or', 'goaltype_basic') }}
          </div>

          <button
            class="tui-performGoalTypeBasic-methodChooser__option"
            @click="$emit('choose', option)"
          >
            <div class="tui-performGoalTypeBasic-methodChooser__optionIcon">
              <component
                :is="option.icon_component"
                custom-class="tui-performGoalTypeBasic-methodChooser__icon"
                :size="null"
              />
            </div>
            <div class="tui-performGoalTypeBasic-methodChooser__optionText">
              <h5 class="tui-performGoalTypeBasic-methodChooser__optionTitle">
                {{ option.name }}
              </h5>
              <div
                class="tui-performGoalTypeBasic-methodChooser__optionDescription"
              >
                {{ option.description }}
              </div>
            </div>
          </button>
        </template>
      </div>
    </div>
  </ModalContent>
</template>

<style lang="scss">
.tui-performGoalTypeBasic-methodChooser {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: var(--gap-8);
  text-align: center;

  &__sectionTitle {
    margin-bottom: var(--gap-6);
    line-height: 1.5;
  }

  &__options {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    width: 100%;
    max-width: 352px;
  }

  &__option {
    display: flex;
    align-items: center;
    width: 100%;
    padding: var(--gap-4);
    text-align: left;
    background-color: transparent;
    border: var(--border-width-thin) solid var(--color-neutral-5);
    border-radius: var(--border-radius-curved);
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s;

    &:hover {
      background-color: rgba(0, 0, 0, 0.05);
      border-color: var(--color-neutral-6);
    }

    &Icon {
      display: flex;
      flex-shrink: 0;
      align-items: center;
      justify-content: center;
      width: rem-px(40);
      height: rem-px(40);
      margin-right: var(--gap-4);

      .tui-performGoalTypeBasic-methodChooser__icon {
        display: inline-block;
        font-size: rem-px(28);
      }
    }

    &Text {
      display: flex;
      flex-direction: column;
    }

    &Title {
      display: flex;
      align-items: center;
      margin-top: 0;
    }

    &Description {
      color: var(--color-text-hint);
    }
  }

  &__separator {
    display: flex;
    align-items: center;
    width: 100%;
    margin: var(--gap-4) 0;

    &::before,
    &::after {
      flex-grow: 1;
      height: 1px;
      margin: 0 var(--gap-2);
      background-color: var(--color-neutral-5);
      content: '';
    }
  }
}
</style>
