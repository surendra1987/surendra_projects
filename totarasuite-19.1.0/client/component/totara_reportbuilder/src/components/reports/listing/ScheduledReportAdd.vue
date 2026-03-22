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

  @author Simon Chester <simon.chester@totara.com>
  @module totara_reportbuilder
-->

<template>
  <form
    class="tui-totara_reportbuilder-scheduledReportAdd"
    @submit.prevent="handleSubmit"
  >
    <Label
      legend
      :label="$str('addanewscheduledreport', 'totara_reportbuilder')"
    />
    <Select
      v-model:value="reportId"
      :aria-label="$str('report', 'totara_reportbuilder')"
      :options="reportOptions"
      char-length="20"
      name="addanewscheduledreport[reportid]"
    />
    <Button
      v-if="reportId"
      :text="$str('addscheduledreport', 'totara_reportbuilder')"
      type="submit"
    />
  </form>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Label from 'tui/components/form/Label';
import Select from 'tui/components/form/Select';

export default {
  components: {
    Button,
    Label,
    Select,
  },

  props: {
    reports: { type: Array, required: true },
  },

  data() {
    return {
      reportId: null,
    };
  },

  computed: {
    reportOptions() {
      return this.reports.map(x => ({ id: x.id, label: x.name }));
    },

    addUrl() {
      if (!this.reportId) {
        return null;
      }
      return this.$url('/totara/reportbuilder/scheduled.php', {
        'addanewscheduledreport[reportid]': this.reportId,
      });
    },
  },

  watch: {
    reports: {
      handler(value) {
        if (!value.some(x => x.id == this.reportId)) {
          this.reportId = value[0] ? value[0].id : null;
        }
      },
      deep: true,
      immediate: true,
    },
  },

  methods: {
    handleSubmit() {
      if (this.addUrl) {
        window.location = this.addUrl;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-totara_reportbuilder-scheduledReportAdd {
  display: flex;
  flex-wrap: wrap;
  gap: var(--gap-2);
  align-items: center;
}
</style>
