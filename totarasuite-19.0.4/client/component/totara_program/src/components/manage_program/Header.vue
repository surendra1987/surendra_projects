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
  <div>
    <h1 class="tui-totaraProgramHeader__fullname" v-html="fullname" />
    <AffectedCard
      :certificate-id="affected.certifid"
      :status-key="affected.statusstr"
      :audience-visibility-warning="affected.audiencevisibilitywarning"
      :assignments-deferred="affected.assignmentsdeferred"
      :expired="affected.expired"
      :statistics="{
        assignments: affected.assignments,
        exceptions: affected.exceptions,
        total: affected.total,
      }"
    />
  </div>
</template>
<script>
import AffectedCard from 'totara_program/components/manage_program/AffectedCard';

export default {
  components: {
    AffectedCard,
  },

  props: {
    /**
     * The programs fullname
     */
    fullname: String, // This needs to be sanitised server side

    /**
     * Contians the information for the AffectedCard
     */
    affected: Object,
  },
};
</script>
<style lang="scss">
.tui-totaraProgramHeader__fullname {
  margin-top: 0;
}
</style>
