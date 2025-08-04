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

  @author Brian Barnes <brian.barnes@totara.com>
  @module totara_program
-->
<template>
  <Card
    class="tui-totaraProgramAffectedCard"
    data-assignmentstatus
    aria-live="polite"
  >
    {{ currentStatus }}
    <template v-if="moreInfo">
      <br />
      {{ $str('learnersassignedbreakdown', 'totara_program', statistics) }}

      <template v-if="audienceVisibilityWarning">
        <br />
        {{ $str('audiencevisibilityconflictmessage', 'totara_program') }}
      </template>
      <template v-if="assignmentsDeferred">
        <br />
        {{ $str('assignmentsdeferred', 'totara_program') }}
      </template>
    </template>
    <template v-if="expired">
      <br />
      {{ $str('ifreactivatinglearnerupdate', 'totara_program') }}
    </template>
  </Card>
</template>
<script>
import Card from 'tui/components/card/Card';
import { loadLangStrings, langString } from 'tui/i18n';

export default {
  components: {
    Card,
  },

  props: {
    /**
     * If this is a certification, the id of the certification
     */
    certificateId: [Number, String],

    /**
     * The key for the status string to be displayed
     */
    statusKey: {
      required: true,
      type: String,
    },

    /**
     * Whether there is any audience issues
     */
    audienceVisibilityWarning: Boolean,

    /**
     * Are there too many users to be added immediately
     */
    assignmentsDeferred: Number,

    /**
     * Whether this program is expired
     */
    expired: Boolean,

    /**
     * User statistics for the program
     */
    statistics: Object,
  },

  data() {
    return {
      currentStatus: '',
    };
  },

  computed: {
    component() {
      if (this.certificateId) {
        return 'totara_certification';
      } else {
        return 'totara_program';
      }
    },

    moreInfo() {
      if (
        this.statusKey === 'notduetostartuntil' ||
        this.statusKey === 'nolongeravailabletolearners'
      ) {
        return false;
      } else {
        return true;
      }
    },
  },

  async mounted() {
    const statusstr = langString(this.statusKey, this.component);
    await loadLangStrings([statusstr]);
    this.currentStatus = statusstr.toString();
  },
};
</script>

<style lang="scss">
.tui-totaraProgramAffectedCard {
  display: block;
  padding: var(--gap-2);
}
</style>
