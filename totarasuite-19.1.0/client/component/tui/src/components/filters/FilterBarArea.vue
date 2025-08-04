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
  @module tui
-->

<template>
  <div class="tui-filterBarArea">
    <span class="sr-only">
      {{ accessibilityTitle }}
    </span>

    <Responsive
      v-slot="slotProps"
      :breakpoints="[
        { name: 'vertical', boundaries: [0, 768] },
        { name: 'horizontal', boundaries: [769, 1600] },
      ]"
    >
      <div
        class="tui-filterBarArea__bar"
        :class="{
          'tui-filterBarArea__bar--hasTop': hasTopBar,
          'tui-filterBarArea__bar--hasBottom': hasBottomBar,
          'tui-filterBarArea__bar--horizontal':
            slotProps.currentBoundaryName === 'horizontal',
        }"
      >
        <!-- Vertical bar -->
        <template v-if="slotProps.currentBoundaryName === 'vertical'">
          <!-- Show / hide filters button -->
          <ButtonIcon
            class="tui-filterBarArea__bar-toggle"
            :aria-label="toggleFiltersAriaLabel"
            :styleclass="{ transparent: true }"
            :text="toggleFiltersText"
            :title="false"
            @click="toggleFilters"
          >
            <SliderIcon />
          </ButtonIcon>

          <!-- Bar and extra filters stacked -->
          <div v-if="showFilters" class="tui-filterBarArea__bar-stackedFilters">
            <slot
              name="bar-filters"
              :filters="value.bar"
              :stacked="true"
              :update="updateBar"
            />

            <slot
              name="extra-filters"
              :filters="value.extra"
              :stacked="true"
              :update="updateExtra"
            />

            <!-- Reset all button -->
            <Button
              class="tui-filterBarArea__bar-resetButton"
              :aria-label="$str('a11y_reset_all_filters', 'totara_core')"
              :styleclass="{ transparent: true }"
              :text="$str('reset_all', 'totara_core')"
              @click="resetAllFilters"
            />
          </div>
        </template>

        <!-- Horizontal bar -->
        <template v-else>
          <!-- Bar Filters -->
          <div class="tui-filterBarArea__bar-barFilters">
            <slot name="bar-filters" :filters="value.bar" :update="updateBar" />
          </div>

          <!-- Extra Filters -->
          <div class="tui-filterBarArea__bar-extraFilters">
            <template v-if="type == 'custom'">
              <slot
                name="custom-extra-filters"
                :filters="value.extra"
                :stacked="false"
                :update="updateExtra"
              />
            </template>

            <FilterBarAreaPopover
              v-else
              :active-filters-count="extraActiveFiltersCount"
              @reset="resetAllFilters"
            >
              <template v-slot:content>
                <slot
                  name="extra-filters"
                  :filters="value.extra"
                  :stacked="false"
                  :update="updateExtra"
                />
              </template>
            </FilterBarAreaPopover>
          </div>
        </template>
      </div>
    </Responsive>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import FilterBarAreaPopover from 'tui/components/filters/FilterBarAreaPopover';
import Responsive from 'tui/components/responsive/Responsive';
import SliderIcon from 'tui/components/icons/Slider';

// Util
import { produce } from 'tui/immutable';

export default {
  components: {
    Button,
    ButtonIcon,
    FilterBarAreaPopover,
    Responsive,
    SliderIcon,
  },

  props: {
    // Accessibility title describing what is being filtered
    accessibilityTitle: { type: String, required: true },

    // Should we display a bottom border?
    hasBottomBar: { type: Boolean, default: true },

    // Should we display a top border?
    hasTopBar: { type: Boolean, default: true },

    // The values to set when resetting the filters
    resetValues: {
      type: Object,
      validator: prop => {
        return 'bar' in prop && 'extra' in prop;
      },
    },

    // Type to determine how the extra filters are handled
    type: { type: String, default: 'popover' },

    // The current values of the provided filters
    value: {
      type: Object,
      required: true,
      validator: prop => {
        return 'bar' in prop && 'extra' in prop;
      },
    },
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      showFilters: false,
    };
  },

  computed: {
    /**
     * The number of active filters on the bar
     *
     * @return {Number}
     */
    barActiveFiltersCount() {
      return this.getActiveFiltersCount(this.value.bar);
    },

    /**
     * The number of active filters in the extra section
     *
     * @return {Number}
     */
    extraActiveFiltersCount() {
      return this.getActiveFiltersCount(this.value.extra);
    },

    /**
     * The aria label string for the show/hide filters button
     *
     * @return {String}
     */
    toggleFiltersAriaLabel() {
      return this.showFilters
        ? this.$str(
            'a11y_hide_filters',
            'totara_core',
            this.totalActiveFiltersCount
          )
        : this.$str(
            'a11y_show_filters',
            'totara_core',
            this.totalActiveFiltersCount
          );
    },

    /**
     * The string for the show/hide filters button
     *
     * @return {String}
     */
    toggleFiltersText() {
      if (this.totalActiveFiltersCount) {
        return this.$str(
          this.showFilters ? 'hide_filters_count' : 'show_filters_count',
          'totara_core',
          this.totalActiveFiltersCount
        );
      }

      return this.$str(
        this.showFilters ? 'hide_filters' : 'show_filters',
        'totara_core'
      );
    },

    /**
     * The total number of active filters
     *
     * @return {Number}
     */
    totalActiveFiltersCount() {
      return this.barActiveFiltersCount + this.extraActiveFiltersCount;
    },
  },

  methods: {
    /**
     * Gets the number of active filters from a filters object
     *
     * @return {Number}
     */
    getActiveFiltersCount(filters) {
      let count = 0;

      Object.keys(filters).forEach(key => {
        const value = filters[key];

        // Some filters allow multiselect so we need to count how many are selected
        if (Array.isArray(value)) {
          count += value.length;
        } else if (value) {
          count++;
        }
      });
      return count;
    },

    /**
     * Reset all the filters
     *
     * If reset values are provided, reset to that. Otherwise set all the values to empty.
     */
    resetAllFilters() {
      if (this.resetValues) {
        this.$emit('update:value', this.resetValues);
        this.$emit('input', this.resetValues);
      } else {
        const valueCopy = { ...this.value };

        Object.keys(valueCopy.bar).forEach(key => {
          let type = valueCopy.bar[key] instanceof Array ? [] : '';
          valueCopy.bar[key] = type;
        });

        Object.keys(valueCopy.extra).forEach(key => {
          let type = valueCopy.extra[key] instanceof Array ? [] : '';
          valueCopy.extra[key] = type;
        });

        this.$emit('update:value', valueCopy);
        this.$emit('input', valueCopy);
      }
    },

    /**
     * Show or hide the filters on stacked mobile view
     */
    toggleFilters() {
      this.showFilters = !this.showFilters;
    },

    /**
     * Update the model bar values from the slot filters
     *
     * @param {String} filter name of the filter to be updated
     * @param {String} value value to set the filter to
     */
    updateBar(filter, value) {
      const updated = produce(this.value, draft => {
        draft.bar[filter] = value;
      });

      this.$emit('update:value', updated);
      this.$emit('input', updated);
    },

    /**
     * Update the model extra values from the slot filters
     *
     * @param {String} filter name of the filter to be updated
     * @param {String} value value to set the filter to
     */
    updateExtra(filter, value) {
      const updated = produce(this.value, draft => {
        draft.extra[filter] = value;
      });

      this.$emit('update:value', updated);
      this.$emit('input', updated);
    },
  },
};
</script>

<lang-strings>
{
  "totara_core": [
    "a11y_hide_filters",
    "a11y_reset_all_filters",
    "a11y_show_filters",
    "hide_filters",
    "hide_filters_count",
    "reset_all",
    "show_filters",
    "show_filters_count"
  ]
}
</lang-strings>

<style lang="scss">
.tui-filterBarArea {
  display: flex;
  flex-direction: column;

  &__bar {
    display: flex;
    flex-direction: column;

    &--horizontal {
      flex-direction: row;
    }

    &--hasTop {
      padding-top: var(--gap-4);
      border-top: var(--border-width-thin) solid var(--filter-bar-border-color);
    }

    &--hasBottom {
      padding-bottom: var(--gap-4);
      border-bottom: var(--border-width-thin) solid
        var(--filter-bar-border-color);
    }

    &-toggle {
      display: flex;
      margin: auto;
    }

    &-resetButton {
      display: flex;
      align-items: flex-start;
      margin-top: var(--gap-4);
    }

    &-stackedFilters {
      display: flex;
      flex-direction: column;
      gap: var(--gap-4);
      margin-top: var(--gap-4);
      padding: 0 var(--gap-4);
    }

    &-barFilters {
      display: flex;
      gap: var(--gap-4);
    }

    &-extraFilters {
      display: flex;
      flex-grow: 1;
      align-items: center;
      justify-content: flex-end;
      height: var(--form-input-height);
      margin-top: auto;
    }
  }
}
</style>
