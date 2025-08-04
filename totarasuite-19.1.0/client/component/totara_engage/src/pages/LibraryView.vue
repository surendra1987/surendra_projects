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

  @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
  @module totara_engage
-->

<template>
  <YourLibrary :page-id="id" :page-props="pageProps" :creation="creation" />
</template>

<script>
import YourLibrary from 'totara_engage/components/contribution/YourLibrary';

export default {
  components: {
    YourLibrary,
  },

  props: {
    id: {
      type: String,
      required: true,
    },
    pageProps: {
      type: Object,
      default: () => ({}),
    },
    creation: String,
  },
};
</script>
