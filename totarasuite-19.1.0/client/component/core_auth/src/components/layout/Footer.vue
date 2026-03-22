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
  @module core_auth
-->

<template>
  <div class="tui-core_auth-footer">
    <div v-if="html" v-html="html" />
    <div class="tui-core_auth-footer__poweredBy" v-html="poweredByHTML" />
  </div>
</template>

<script>
export default {
  props: {
    html: String,
  },

  computed: {
    poweredByHTML() {
      return this.$str('poweredbyxhtml', 'totara_core', {
        url: 'https://www.totara.com/',
        totaralearn: this.$str('totaralearn', 'totara_core'),
      });
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-footer {
  display: flex;
  flex-flow: column;
  gap: var(--gap-2);
  margin-top: var(--gap-6);
  padding: var(--gap-6) var(--gap-8);
  color: var(--footer-text-color);
  background: var(--footer-bg-color);

  p {
    margin-bottom: var(--gap-2);

    &:last-child {
      margin-bottom: 0;
    }
  }

  a {
    color: inherit;
    text-decoration: underline;
  }

  &__poweredBy {
    font-size: font-size-px(12);
  }
}
</style>
