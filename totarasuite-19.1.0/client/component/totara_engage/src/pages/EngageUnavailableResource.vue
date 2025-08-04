<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD’s customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module totara_engage
-->
<template>
  <div class="tui-engageUnavailableResource">
    <h1>{{ $str('resource_unavailable_heading', 'totara_engage') }}</h1>
    <p class="tui-engageUnavailableResource__text">{{ message }}</p>
  </div>
</template>

<script>
import { getString } from 'tui/i18n';

export default {
  props: {
    message: {
      type: String,
      default: getString('resource_unavailable', 'totara_engage'),
    },
  },
};
</script>

<style lang="scss">
.tui-engageUnavailableResource {
  width: 100%;
  padding: 0 var(--gap-6);

  &__text {
    @include font(body);
  }
}
</style>
