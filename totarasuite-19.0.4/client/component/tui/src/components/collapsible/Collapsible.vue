<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module tui
-->

<template>
  <div
    class="tui-collapsible"
    :class="{
      'tui-collapsible--minimal': variant === 'minimal',
      'tui-collapsible--largePadding': padding === 'large',
    }"
  >
    <div class="tui-collapsible__header">
      <h2 class="tui-collapsible__header-heading">
        <div
          class="tui-collapsible__header-button"
          :class="{
            'tui-collapsible__header-button--excludeHeaderPadding': excludeHeaderPadding,
            'tui-collapsible__header-button--hideFocus': hideFocus,
          }"
          role="button"
          tabindex="0"
          :aria-expanded="expanded.toString()"
          :aria-controls="generatedId + 'region'"
          @click="toggleExpand"
          @keydown="handleHeaderKeydown"
        >
          <CollapseIcon
            v-if="expanded"
            class="tui-collapsible__header-icon"
            aria-hidden="true"
            :size="null"
          />
          <ExpandIcon
            v-else
            class="tui-collapsible__header-icon"
            aria-hidden="true"
            :size="null"
          />
          <span
            :id="generatedId + 'label'"
            class="tui-collapsible__header-text"
          >
            {{ label }}
            <slot name="label-extra" />
          </span>
        </div>
      </h2>

      <div
        v-if="$slots['collapsible-side-content']"
        class="tui-collapsible__header-sideContent"
      >
        <slot name="collapsible-side-content" />
      </div>
    </div>
    <div
      v-if="alwaysRender || (expanded && $slots.default)"
      v-show="!alwaysRender || (expanded && $slots.default)"
      :id="generatedId + 'region'"
      class="tui-collapsible__content"
      :class="{ 'tui-collapsible__content--indented': indentContents }"
      role="region"
      :aria-labelledby="generatedId + 'label'"
    >
      <slot :expanded="expanded" />
    </div>
  </div>
</template>

<script>
import CollapseIcon from 'tui/components/icons/Collapse';
import ExpandIcon from 'tui/components/icons/Expand';

export default {
  components: {
    CollapseIcon,
    ExpandIcon,
  },

  props: {
    alwaysRender: Boolean,
    id: {
      type: [String, Number],
    },
    label: {
      required: true,
      type: String,
    },
    initialState: {
      default: false,
      type: Boolean,
    },
    value: {
      default: undefined,
      type: Boolean,
    },
    indentContents: Boolean,
    variant: String,
    padding: String,
    excludeHeaderPadding: Boolean,
    hideFocus: Boolean,
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      state: this.initialState,
    };
  },

  computed: {
    /**
     * Update expand state base on value or internal state
     *
     * @return {Bool}
     */
    expanded() {
      // If no value prop provided use internal state
      if (this.value === undefined) {
        return this.state;
      }
      return this.value;
    },

    /**
     * Provide ID for accessibility tags
     *
     * @return {Bool}
     */
    generatedId() {
      return this.id || this.$id();
    },
  },

  methods: {
    // Toggle expanded state
    toggleExpand() {
      // If no value prop provided toggle internal state
      if (this.value === undefined) {
        this.state = !this.state;
        return;
      }
      // Propagate expanded value change to parent
      this.$emit('update:value', !this.value);
      this.$emit('input', !this.value);
    },

    handleHeaderKeydown(e) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
        e.preventDefault();
        this.toggleExpand();
      }
    },
  },
};
</script>

<style lang="scss">
.tui-collapsible {
  &__header {
    display: flex;
    background: var(--collapsible-header-bg-color);
    border: var(--border-width-thin) solid
      var(--collapsible-header-border-color);

    &-heading {
      display: flex;
      flex-grow: 1;
      margin: 0;
    }

    &-button {
      display: flex;
      flex-grow: 1;
      margin: 0;
      padding: var(--gap-2) var(--gap-2) var(--gap-2) 0;
      overflow-wrap: break-word;

      &--excludeHeaderPadding {
        padding: 0;
      }

      cursor: pointer;
      user-select: none;

      &:not(&--hideFocus) {
        &:focus-visible {
          @include tui-focus();
        }
      }
    }

    &-icon {
      $icon-size: rem-px(14);
      flex-shrink: 0;
      margin: 0 var(--gap-4);
      margin-top: calc((var(--font-h4-line-height) - #{$icon-size}) / 2);
      color: var(--color-state);
      font-size: $icon-size;
    }

    &-text {
      @include font(h4);
      position: relative;
      margin: 0;
    }

    &-sideContent {
      display: flex;
      flex-shrink: 0;
      padding: var(--gap-2);
    }
  }

  &__content {
    & > .tui-formRowStack {
      margin-top: var(--gap-4);
    }

    // line up with text of header
    &--indented {
      padding-left: var(--gap-12);
    }
  }
  &--minimal &__header {
    background: transparent;
    border-color: transparent;
  }

  &--largePadding &__header-button {
    padding: var(--gap-5) var(--gap-2) var(--gap-5) var(--gap-1);
  }
  &--largePadding &__header-sideContent {
    padding: var(--gap-2) var(--gap-4);
  }
  &--largePadding &__content {
    padding: var(--gap-3);
    padding-top: 0;

    &--indented {
      padding-left: calc(var(--gap-12) + var(--gap-1));
    }
  }
}
</style>
