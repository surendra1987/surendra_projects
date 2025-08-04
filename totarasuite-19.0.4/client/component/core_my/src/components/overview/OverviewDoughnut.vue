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
  @module core_my
-->

<template>
  <ChartJs
    :aria-label="ariaLabel"
    :data="chartData"
    :options="chartOptions"
    type="doughnut"
  />
</template>

<script>
import ChartJs from 'tui_charts/components/ChartJs';
import theme from 'tui/theme';
import { isRtl } from 'tui/i18n';

const screenDirectionRTL = isRtl();

// Theme colours
let labelColor = theme.getVar('color-neutral-7');
let chartColorAchieved = theme.getVar('color-chart-background-7');
let chartColorNotProgressed = theme.getVar('color-chart-background-4');
let chartColorNotStarted = theme.getVar('color-chart-background-2');
let chartColorProgressed = theme.getVar('color-chart-background-1');

export default {
  components: { ChartJs },

  props: {
    // Number of achieved
    achievedCount: {
      type: Number,
      required: true,
    },
    // Use completed string instead of achieved
    completed: {
      type: Boolean,
    },
    // Number of progressed
    progressedCount: {
      type: Number,
      required: true,
    },
    // Number of not progressed
    notProgressedCount: {
      type: Number,
      required: true,
    },
    // Number of not started
    notStartedCount: {
      type: Number,
      required: true,
    },
  },

  computed: {
    /**
     * Return the options for the chart
     *
     * @return {Object}
     */
    chartOptions() {
      return {
        cutoutPercentage: 70,
        tooltips: {
          enabled: false,
        },
        hover: {
          mode: null,
        },
        elements: {
          arc: {
            borderWidth: 0,
          },
        },
        legend: {
          position: screenDirectionRTL ? 'left' : 'right',
          labels: {
            boxWidth: 26,
            fontColor: labelColor,
            fontSize: 13,
          },
          onClick() {
            return;
          },
        },
      };
    },

    /**
     * Return the data for the chart
     *
     * @return {Object}
     */
    chartData() {
      let data = [
        this.achievedCount,
        this.progressedCount,
        this.notProgressedCount,
        this.notStartedCount,
      ];

      let colors = [
        chartColorAchieved,
        chartColorProgressed,
        chartColorNotProgressed,
        chartColorNotStarted,
      ];

      return {
        labels: this.labels,
        datasets: [
          {
            data: data,
            backgroundColor: colors,
          },
        ],
      };
    },

    /**
     * Return the label language strings for the chart data
     *
     * @return {Array}
     */
    labels() {
      let achieved = this.completed
        ? this.$str('x_completed', 'core_my', this.achievedCount)
        : this.$str('x_achieved', 'core_my', this.achievedCount);
      let notProgressed = this.$str(
        'x_not_progressed',
        'core_my',
        this.notProgressedCount
      );
      let notStarted = this.$str(
        'x_not_started',
        'core_my',
        this.notStartedCount
      );
      let progressed = this.$str(
        'x_progressed',
        'core_my',
        this.progressedCount
      );

      return [achieved, progressed, notProgressed, notStarted];
    },

    /**
     * Returns the labels as a comma separated string to be read by screen readers
     *
     * @return {Array}
     */
    ariaLabel() {
      return this.$str(
        this.completed
          ? 'a11y_overview_doughnut_label_completed'
          : 'a11y_overview_doughnut_label',
        'core_my',
        {
          achieved: this.achievedCount,
          notProgressed: this.notProgressedCount,
          notStarted: this.notStartedCount,
          progressed: this.progressedCount,
        }
      );
    },
  },
};
</script>
