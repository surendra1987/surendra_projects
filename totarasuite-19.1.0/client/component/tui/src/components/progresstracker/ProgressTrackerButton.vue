<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totaralearning.com>
  @module tui
-->

<template>
  <button
    type="button"
    class="tui-progressTrackerButton"
    :class="{
      'tui-progressTrackerButton--selected': selected,
    }"
    :aria-current="selected ? 'location' : null"
    :aria-label="accessibleText"
    :text="text"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    <div class="tui-progressTrackerButton__text">
      {{ text }}
    </div>
    <div
      v-if="required"
      aria-hidden="true"
      class="tui-progressTrackerButton__required"
      :class="{
        'tui-progressTrackerButton__required--selected': selected,
      }"
    >
      *
    </div>
  </button>
</template>

<script>
export default {
  props: {
    disabled: Boolean,
    required: { type: Boolean },
    selected: Boolean,
    text: String,
  },

  emits: ['click'],

  computed: {
    /**
     * Provide accessibility text for button
     *
     * @return {String}
     */
    accessibleText() {
      if (this.required) {
        return this.$str(
          'a11y_section_has_required_questions',
          'mod_perform',
          this.text
        );
      }

      return this.text;
    },
  },
};
</script>

<style lang="scss">
.tui-progressTrackerButton {
  display: flex;
  gap: var(--gap-2);
  justify-content: start;
  width: 100%;
  padding: var(--gap-2);
  color: var(--color-state);
  line-height: var(--font-body-line-height);
  text-align: left;
  overflow-wrap: break-word;
  background: transparent;
  border: none;

  &:focus-visible {
    @include tui-focus;
  }

  &:hover {
    color: var(--color-state);
    background: var(--color-state-highlight-neutral);
  }

  &--selected {
    color: var(--color-neutral-1);
    background: var(--color-state-active);
    border-color: var(--color-neutral-3);

    &:hover,
    &:focus {
      color: var(--color-neutral-1);
      background: var(--color-state-active);
      border-color: var(--color-neutral-3);
    }
  }

  &__required {
    color: var(--color-prompt-alert);

    &--selected {
      color: var(--color-neutral-1);
    }
  }
}
</style>
