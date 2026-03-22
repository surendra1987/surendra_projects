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
  @module samples
-->

<template>
  <div class="tui-loader">
    A visual 'or' separator

    <SamplesExample>
      <OrBox />
    </SamplesExample>
  </div>
</template>

<script>
import OrBox from 'tui/components/decor/OrBox';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

export default {
  components: {
    OrBox,
    SamplesExample,
  },

  data() {
    return {
      fullpage: false,
      loading: true,
    };
  },
};
</script>
