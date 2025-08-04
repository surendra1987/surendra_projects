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
  <div class="tui-dateSelector">
    <div class="tui-dateSelector__date">
      <template v-for="field in fieldOrder">
        <div
          v-if="field === 'd'"
          :key="field + 'd'"
          class="tui-dateSelector__date-day"
        >
          <Label
            :for-id="$id('event-date-day')"
            :label="$str('day', 'totara_core')"
            :hidden="true"
          />
          <Select
            :id="$id('event-date-day')"
            :value="day"
            :disabled="disabled"
            :options="dayRange"
            data-testid="day"
            @input="handleSelectChange('day', $event)"
          />
        </div>

        <div
          v-else-if="field === 'm'"
          :key="field + 'm'"
          class="tui-dateSelector__date-month"
        >
          <Label
            :for-id="$id('event-date-month')"
            :label="$str('month', 'totara_core')"
            :hidden="true"
          />
          <Select
            :id="$id('event-date-month')"
            :value="month"
            :disabled="disabled"
            :options="optionsMonths"
            data-testid="month"
            @input="handleSelectChange('month', $event)"
          />
        </div>

        <div
          v-else-if="field === 'y'"
          :key="field + 'y'"
          class="tui-dateSelector__date-year"
        >
          <Label
            :for-id="$id('event-date-year')"
            :label="$str('year', 'totara_core')"
            :hidden="true"
          />
          <Select
            :id="$id('event-date-year')"
            :value="year"
            :disabled="disabled"
            :options="yearRange"
            data-testid="year"
            @input="handleSelectChange('year', $event)"
          />
        </div>
      </template>
    </div>

    <div v-if="hasTimezone" class="tui-dateSelector__time">
      <Label
        :for-id="$id('event-date-time-zone')"
        :label="$str('time_zone', 'totara_core')"
        :hidden="true"
      />
      <Select
        :id="$id('event-date-time-zone')"
        v-model:value="timezone"
        :disabled="disabled"
        :options="optionsTimezones"
        char-length="full"
        @input="update"
      />
    </div>
  </div>
</template>

<script>
// Components
import Label from 'tui/components/form/Label';
import Select from 'tui/components/form/Select';

// Utils
import {
  getCurrentDateValues,
  getDateOrderFromStrftime,
  getDaysInMonthSelectArray,
  getIsoFromValues,
  getMonthStringsSelectArray,
  getValuesFromIso,
  getYearsSelectArray,
  getFixedYearsSelectArray,
} from 'tui/date';
import { getTimeZoneKeyStrings } from 'tui/time';
import { config } from 'tui/config';
import { loadLangStrings } from 'tui/i18n';

const optionsMonths = getMonthStringsSelectArray();

export default {
  components: {
    Label,
    Select,
  },

  props: {
    initialCurrentDate: Boolean,
    initialCustomDate: [Boolean, String],
    initialTimezone: [Boolean, String],
    disabled: Boolean,
    hasTimezone: Boolean,
    type: {
      default: 'date',
      type: String,
      validator: x => ['date', 'dateTime'].includes(x),
    },
    value: Object,
    yearsMidrange: Number,
    yearsBeforeMidrange: Number,
    yearsAfterMidrange: Number,
    // First year of range (alternative to yearsMidrange & yearsAfterMidrange)
    yearRangeStart: Number,
    // Last year of range (alternative to yearsMidrange & yearsAfterMidrange)
    yearRangeEnd: Number,
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      day: '',
      month: '',
      year: '',
      timezone: '',
      optionsMonths: [],
      optionsTimezones: [],
    };
  },

  computed: {
    fieldOrder() {
      return getDateOrderFromStrftime(
        this.$str('strftimedatefulllong', 'langconfig')
      );
    },

    date() {
      let date = {
        day: this.day,
        month: this.month,
        year: this.year,
      };

      // Get ISO for provided date values
      let iso = {
        iso: getIsoFromValues(date, this.type === 'date'),
      };

      if (this.hasTimezone) {
        iso.timezone = this.timezone;
      }

      return iso;
    },

    /**
     * Get available days of month
     *
     */
    dayRange() {
      let days = getDaysInMonthSelectArray(this.month, this.year);

      // Check any existing day value is within range
      this.isDayWithinRange(days);
      return days;
    },

    /**
     * Get available years range
     *
     */
    yearRange() {
      let years;

      if (this.yearRangeStart || this.yearRangeEnd) {
        years = getFixedYearsSelectArray(
          this.yearRangeStart,
          this.yearRangeEnd
        );
      } else {
        years = getYearsSelectArray(
          this.yearsMidrange,
          this.yearsBeforeMidrange,
          this.yearsAfterMidrange
        );
      }

      // Check any existing year value is within range
      this.isYearWithinRange(years);
      return years;
    },
  },

  watch: {
    /**
     * Updates date to value
     */
    value() {
      if (this.value && this.value.iso) {
        const date = getValuesFromIso(this.value.iso);
        this.setDate(date);
      }
      if (
        this.value &&
        this.value.timezone &&
        this.timezone != this.value.timezone
      ) {
        this.timezone = this.value.timezone;
        this.update();
      }
    },
  },

  /**
   * Fetch required data for populating selects
   *
   */
  mounted() {
    // Get select list options
    this.getMonths();
    if (this.hasTimezone) {
      this.getTimezones();
    }

    if (this.hasTimezone) {
      if (this.value) {
        this.timezone = this.value.timezone;
      } else if (this.initialTimezone) {
        this.timezone = this.initialTimezone;
      }
    }

    let initialDate = '';
    if (this.value) {
      initialDate = getValuesFromIso(this.value.iso);
      this.setInitialDate(initialDate);
    } else if (this.initialCustomDate) {
      initialDate = getValuesFromIso(this.initialCustomDate);
      this.setDate(initialDate);
    } else if (this.initialCurrentDate) {
      initialDate = getCurrentDateValues();
      this.setDate(initialDate);
    }
  },

  methods: {
    /**
     * Get month keys & strings array for select options
     */
    getMonths() {
      this.optionsMonths = optionsMonths;
    },

    /**
     * Get server time zone string
     *
     * @param {string} zone
     */
    getServerTimezone(zone) {
      return this.$str('server_timezone', 'totara_core', zone);
    },

    /**
     * Get time zone keys & strings array for select options
     *
     */
    async getTimezones() {
      const zones = getTimeZoneKeyStrings().slice();
      const serverTimezone = config.timezone.server;
      await loadLangStrings(zones.map(x => x.label));
      const index = zones.findIndex(x => x.id == serverTimezone);
      if (index !== -1) {
        const item = zones[index];
        item.label = this.getServerTimezone(item.label.toString());
        zones.splice(index, 1);
        zones.unshift(item);
      }
      this.optionsTimezones = zones;
    },

    /**
     * Check if all required fields have been populated
     *
     * @returns {boolean}
     */
    hasRequiredFields() {
      return (
        Number.isInteger(this.day) &&
        Number.isInteger(this.month) &&
        Number.isInteger(this.year)
      );
    },

    /**
     * Make sure existing day value in within current range
     *
     * @param {array} range array of days
     */
    isDayWithinRange(range) {
      let currentDay = this.day;

      // Remove existing value if not within range
      if (currentDay > range.length) {
        this.day = '';
        this.update();
      }
    },

    /**
     * Make sure existing year value in within current range
     *
     * @param {array} range array of years
     */
    isYearWithinRange(range) {
      let currentYear = this.year;

      if (currentYear) {
        const withinRange = range.filter(function(year) {
          return year.id === currentYear;
        });

        // Remove existing value if not within range
        if (!withinRange.length) {
          this.year = '';
          this.update();
        }
      }
    },

    /**
     * Set initial values to date
     *
     */
    setInitialDate(initialDate) {
      if (initialDate) {
        this.day = initialDate.day;
        this.month = initialDate.month;
        this.year = initialDate.year;
      }
    },

    /**
     * Set values to date
     *
     */
    setDate(newDate) {
      if (
        newDate &&
        (this.day != newDate.day ||
          this.month != newDate.month ||
          this.year != newDate.year)
      ) {
        this.day = newDate.day;
        this.month = newDate.month;
        this.year = newDate.year;
        this.update();
      }
    },

    /**
     * Update the selected date
     *
     */
    update() {
      // Check if all required fields have been populated if not emit null which will trigger the validation required error
      if (!this.hasRequiredFields()) {
        this.$emit('update:value');
        this.$emit('input');
        return;
      }

      // Emit date values
      this.$emit('update:value', this.date);
      this.$emit('input', this.date);
    },

    handleSelectChange(field, value) {
      this[field] = value;
      this.update();
    },
  },
};
</script>

<style lang="scss">
.tui-dateSelector {
  display: flex;
  flex-flow: column;
  gap: var(--gap-2);
  max-width: rem-px(350);
  font-size: var(--form-input-font-size);

  &__date {
    display: flex;
    gap: var(--gap-2);

    // set ratio and intrinsic width for each field

    &-day {
      flex: 6;
      width: calc(var(--gap-10) + 1.5em);
    }

    &-month {
      flex: 12;
      width: calc(var(--gap-10) + 6em);
    }

    &-year {
      flex: 8;
      width: calc(var(--gap-10) + 3em);
    }
  }
}
</style>
