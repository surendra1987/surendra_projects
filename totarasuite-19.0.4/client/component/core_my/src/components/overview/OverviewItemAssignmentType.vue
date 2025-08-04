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
  <!-- Represents the assignment type across the different plugins -->
  <div class="tui-myPerformOverviewItemAssignmentType">
    <span class="sr-only">
      {{
        $str('a11y_overview_assignment_type_label', 'core_my', assignmentType)
      }}
    </span>
    <span aria-hidden="true">
      {{ assignmentType }}
    </span>
  </div>
</template>

<script>
export default {
  props: {
    // Assignment type
    assignmentType: {
      required: true,
      type: String,
    },
  },
};
</script>
