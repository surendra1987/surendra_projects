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

  @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
  @module mod_perform
-->

<template>
  <div class="tui-performUserActivityInvalid">
    <PageHeading
      :title="$str('external_participation_invalid_heading', 'mod_perform')"
    />

    <div>
      {{ $str('external_participation_invalid_message', 'mod_perform') }}
    </div>
  </div>
</template>

<script>
import PageHeading from 'tui/components/layouts/PageHeading';

export default {
  components: {
    PageHeading,
  },
};
</script>

<style lang="scss">
.tui-performUserActivityInvalid {
  @include font(body);

  & > * + * {
    margin-top: var(--gap-6);
  }
}
</style>
