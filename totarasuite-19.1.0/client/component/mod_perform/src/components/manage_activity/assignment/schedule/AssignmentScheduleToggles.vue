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
  @module mod_perform
-->
<template>
  <div class="tui-assignmentScheduleToggles">
    <div class="tui-assignmentScheduleToggles__group">
      <div class="tui-assignmentScheduleToggles__item">
        <h3 class="tui-assignmentScheduleToggles__item-header">
          {{ $str('schedule_creation_range', 'mod_perform') }}
        </h3>
        <ToggleSet
          :value="scheduleIsOpen"
          class="tui-assignmentScheduleToggles__item-toggle"
          :aria-label="$str('schedule_creation_range', 'mod_perform')"
          :large="true"
          @input="updateToggle($event, 'scheduleIsOpen')"
        >
          <ToggleButton
            :value="false"
            :aria-label="$str('schedule_is_limited', 'mod_perform')"
            :text="$str('schedule_is_limited', 'mod_perform')"
          >
            <DateLimitedIcon />
          </ToggleButton>

          <ToggleButton
            :value="true"
            :aria-label="$str('schedule_is_open', 'mod_perform')"
            :text="$str('schedule_is_open', 'mod_perform')"
          >
            <DateOpenIcon />
          </ToggleButton>
        </ToggleSet>
      </div>
      <div class="tui-assignmentScheduleToggles__item">
        <h3 class="tui-assignmentScheduleToggles__item-header">
          {{ $str('schedule_creation_date_type', 'mod_perform') }}
        </h3>

        <ToggleSet
          :value="scheduleIsFixed"
          class="tui-assignmentScheduleToggles__item-toggle"
          :aria-label="$str('schedule_creation_date_type', 'mod_perform')"
          :large="true"
          @input="updateToggle($event, 'scheduleIsFixed')"
        >
          <ToggleButton
            :value="true"
            :aria-label="$str('schedule_is_fixed', 'mod_perform')"
            :text="$str('schedule_is_fixed', 'mod_perform')"
          >
            <CalendarIcon />
          </ToggleButton>

          <ToggleButton
            :value="false"
            :aria-label="$str('schedule_is_relative', 'mod_perform')"
            :text="$str('schedule_is_relative', 'mod_perform')"
          >
            <DateRelativeIcon />
          </ToggleButton>
        </ToggleSet>
      </div>
    </div>
    <div class="tui-assignmentScheduleToggles__group">
      <div class="tui-assignmentScheduleToggles__item">
        <h3 class="tui-assignmentScheduleToggles__item-header">
          {{ $str('schedule_creation_frequency', 'mod_perform') }}
        </h3>

        <ToggleSet
          :value="repeatingIsEnabled"
          class="tui-assignmentScheduleToggles__item-toggle"
          :aria-label="$str('schedule_creation_frequency', 'mod_perform')"
          :large="true"
          @input="updateToggle($event, 'repeatingIsEnabled')"
        >
          <ToggleButton
            :value="false"
            :aria-label="
              $str('schedule_creation_frequency_once_off', 'mod_perform')
            "
            :text="$str('schedule_creation_frequency_once_off', 'mod_perform')"
          >
            <DateFrequencyOnceIcon />
          </ToggleButton>

          <ToggleButton
            :value="true"
            :aria-label="
              $str('schedule_creation_frequency_repeating', 'mod_perform')
            "
            :text="$str('schedule_creation_frequency_repeating', 'mod_perform')"
          >
            <DateFrequencyRepeatingIcon />
          </ToggleButton>
        </ToggleSet>
      </div>
    </div>
  </div>
</template>

<script>
// Imports
import ToggleButton from 'tui/components/toggle/ToggleButton';
import ToggleSet from 'tui/components/toggle/ToggleSet';
// Icons
import CalendarIcon from 'tui/components/icons/Calendar';
import DateFrequencyOnceIcon from 'tui/components/icons/DateFrequencyOnce';
import DateFrequencyRepeatingIcon from 'tui/components/icons/DateFrequencyRepeating';
import DateLimitedIcon from 'tui/components/icons/DateLimited';
import DateOpenIcon from 'tui/components/icons/DateOpen';
import DateRelativeIcon from 'tui/components/icons/DateRelative';

// Util

export default {
  components: {
    CalendarIcon,
    DateFrequencyOnceIcon,
    DateFrequencyRepeatingIcon,
    DateLimitedIcon,
    DateOpenIcon,
    DateRelativeIcon,
    ToggleButton,
    ToggleSet,
  },

  props: {
    value: {
      type: Object,
    },
    activity: {
      type: Object,
      required: true,
    },
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      repeatingIsEnabled: false,
      scheduleIsOpen: false,
      scheduleIsFixed: false,
    };
  },

  watch: {
    value: {
      handler() {
        this.repeatingIsEnabled = this.value.repeatingIsEnabled;
        this.scheduleIsOpen = this.value.scheduleIsOpen;
        this.scheduleIsFixed = this.value.scheduleIsFixed;
      },
      immediate: true,
    },
  },

  methods: {
    updateToggle(value, name) {
      this[name] = value;
      this.toggleChange();
    },

    /**
     * Inform schedule of change
     */
    toggleChange() {
      const value = {
        repeatingIsEnabled: this.repeatingIsEnabled,
        scheduleIsOpen: this.scheduleIsOpen,
        scheduleIsFixed: this.scheduleIsFixed,
      };
      this.$emit('update:value', value);
      this.$emit('input', value);
    },
  },
};
</script>

<style lang="scss">
.tui-assignmentScheduleToggles {
  display: flex;
  flex-wrap: wrap;

  &__group {
    display: flex;
    flex-wrap: wrap;
    margin-right: var(--gap-4);

    &:last-child {
      margin-right: 0;
    }
  }

  &__item {
    margin-right: var(--gap-4);

    &-header {
      @include font(h4);
      margin: var(--gap-4) 0 0;
    }

    &-toggle {
      margin-top: var(--gap-4);
    }
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-assignmentScheduleToggles {
    &__group {
      border-right: var(--border-width-thin) solid var(--color-neutral-6);

      &:last-child {
        border-right: none;
      }
    }

    &__item {
      &-header {
        margin: 0;
      }
    }
  }
}
</style>
