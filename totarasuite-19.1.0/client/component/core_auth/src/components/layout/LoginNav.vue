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
  @module tui
-->

<template>
  <div class="tui-core_auth-loginNav">
    <a
      class="tui-core_auth-loginNav__logo"
      :href="homeUrl"
      :aria-label="siteName"
    >
      <img
        :src="logoUrl"
        class="tui-core_auth-loginNav__image"
        aria-hidden="true"
      />
    </a>
    <div class="tui-core_auth-loginNav__side">
      <div class="tui-core_auth-loginNav__lang">
        <LanguageSelect
          v-if="lang.showMenu"
          :langs="lang.langs"
          :value="lang.current"
          @input="changeLang"
        />
      </div>
    </div>
  </div>
</template>

<script>
import LanguageSelect from 'core_auth/components/layout/LanguageSelect';

export default {
  components: {
    LanguageSelect,
  },

  props: {
    lang: { type: Object, required: true },
    siteName: { type: String, required: true },
    homeUrl: { type: String, required: true },
    logoUrl: { type: String, required: true },
  },

  methods: {
    changeLang(lang) {
      const url = new URL(window.location);
      url.searchParams.set('lang', lang);
      window.location = url.toString();
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-loginNav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--gap-8);
  color: var(--nav-text-color);
  background-color: var(--nav-bg-color);

  &__image {
    height: rem-px(28);
  }

  &__lang {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 0;
  }
}
</style>
