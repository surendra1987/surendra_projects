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
  <section
    class="tui-filterBar"
    :class="{
      'tui-filterBar--hasTop': hasTopBar,
      'tui-filterBar--hasBottom': hasBottomBar,
    }"
  >
    <div class="tui-filterBar__heading">
      <h2 class="tui-filterBar__heading-header">
        {{ title }}
      </h2>

      <div
        v-if="Object.entries(value).length"
        class="tui-filterBar__heading-status"
        role="status"
      >
        {{
          $str(
            activeCount == 1
              ? 'a11y_active_filter_type'
              : 'a11y_active_filter_type_plural',
            'totara_core',
            activeCount
          )
        }}
      </div>
    </div>

    <OverflowDetector v-slot="{ measuring }" @change="overflowChanged">
      <div
        class="tui-filterBar__filters"
        :class="{
          'tui-filterBar__filters--stacked': vertical && !measuring,
        }"
      >
        <div class="tui-filterBar__toggle">
          <ButtonIcon
            v-show="vertical && !measuring"
            :aria-label="false"
            class="tui-filterBar__toggle-btn"
            :styleclass="{
              transparent: true,
            }"
            :text="
              $str(showFilters ? 'hide_filters' : 'show_filters', 'totara_core')
            "
            @click="toggleFilters"
          >
            <SliderIcon />
          </ButtonIcon>
        </div>
        <div
          v-if="showFilters || !vertical || measuring"
          class="tui-filterBar__filters-left"
        >
          <!-- Left content -->
          <slot
            name="filters-left"
            :filters="value"
            :stacked="vertical && !measuring"
          />
        </div>
        <Separator v-if="vertical && showFilters" />
        <div
          v-if="showFilters || !vertical || measuring"
          class="tui-filterBar__filters-right"
        >
          <!-- Right content after separator -->
          <slot
            name="filters-right"
            :filters="value"
            :stacked="vertical && !measuring"
          />
        </div>
        <Button
          v-if="showReset"
          :class="{
            'tui-filterBar__reset': true,
            'tui-filterBar__reset-stacked': vertical && !measuring,
          }"
          :text="$str('reset', 'totara_core')"
          :aria-label="$str('reset_filters', 'totara_core')"
          :styleclass="{
            stealth: true,
          }"
          @click="$emit('reset')"
        />
      </div>
    </OverflowDetector>
  </section>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import OverflowDetector from 'tui/components/util/OverflowDetector';
import SliderIcon from 'tui/components/icons/Slider';
import Separator from 'tui/components/decor/Separator';

export default {
  components: {
    Button,
    ButtonIcon,
    OverflowDetector,
    Separator,
    SliderIcon,
  },

  props: {
    title: {
      type: String,
      required: true,
    },
    value: {
      type: Object,
      default() {
        return {};
      },
    },
    showReset: Boolean,
    hasTopBar: {
      type: Boolean,
      default: true,
    },
    hasBottomBar: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['reset', 'active-count-changed'],

  data() {
    return {
      showFilters: false,
      vertical: false,
    };
  },

  computed: {
    /**
     * Calculate the number of active filters
     *
     * @return {Int}
     */
    activeCount() {
      let count = 0;
      Object.keys(this.value).forEach(key => {
        let val = this.value[key];

        if (val instanceof Array) {
          if (val.length !== 0) {
            count += val.length;
          }
        } else if (val !== '' && val !== null && val !== false) {
          count++;
        }
      });
      return count;
    },
  },

  watch: {
    activeCount: {
      handler(value) {
        this.$emit('active-count-changed', value);
      },
      immediate: true,
    },
  },

  methods: {
    /**
     * Switch vertical Bool to true when content is overflowing
     */
    overflowChanged({ overflowing }) {
      this.vertical = overflowing;
    },

    /**
     * Toggle visibility of filters on mobile
     *
     */
    toggleFilters() {
      this.showFilters = !this.showFilters;
    },
  },
};
</script>

<style lang="scss">
.tui-filterBar {
  display: flex;
  flex-direction: column;

  &--hasTop {
    padding-top: var(--gap-4);
    border-top: var(--border-width-thin) solid var(--filter-bar-border-color);
  }

  &--hasBottom {
    padding-bottom: var(--gap-4);
    border-bottom: var(--border-width-thin) solid var(--filter-bar-border-color);
  }

  &__heading {
    @include sr-only();
  }

  &__toggle {
    display: flex;
    justify-content: center;

    &-btn {
      margin: var(--gap-2) 0;
    }
  }

  &__filters {
    $stacked: #{&}--stacked;
    display: flex;
    flex-grow: 1;

    &-left,
    &-right {
      display: flex;
      flex-shrink: 0;

      > * {
        flex-shrink: 0;
        margin-left: var(--gap-4);
      }

      > * + * {
        margin-left: var(--gap-4);
      }

      #{$stacked} & {
        flex-direction: column;
        margin-left: 0;
        border-left: none;

        .tui-formLabel {
          @include font(body-sm, var(--label-weight));
        }

        > * {
          margin-left: 0;
          padding-left: 0;
        }

        > :first-child {
          border-left: none;
        }
      }
    }

    &-right {
      justify-content: flex-start;

      > :first-child {
        border-left: var(--border-width-thin) solid var(--color-neutral-5);
      }

      > * {
        padding-left: var(--gap-4);
      }
    }

    &--stacked {
      flex-direction: column;
    }
  }

  &__reset {
    margin-left: var(--gap-4);

    &-stacked {
      margin-top: var(--gap-4);
      padding-top: var(--gap-4);
      padding-bottom: var(--gap-4);
    }
  }
}
</style>
