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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <!-- Represents the assigned state across the different plugins -->
  <div class="tui-myPerformOverviewItemAssigned">
    {{ $str('overview_assigned_date', 'core_my', date) }}
  </div>
</template>

<script>
export default {
  props: {
    // Update date
    date: {
      required: true,
      type: String,
    },
  },
};
</script>
