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
  <div
    class="tui-core_auth-loginLayout"
    :class="[
      layoutData.backgroundImage && 'tui-core_auth-loginLayout--hasBackground',
    ]"
  >
    <div class="tui-core_auth-loginLayout__panels">
      <div class="tui-core_auth-loginLayout__panel-interface">
        <LoginNav
          :lang="layoutData.lang"
          :site-name="layoutData.siteName"
          :home-url="layoutData.homeUrl"
          :logo-url="layoutData.logoUrl"
        />
        <div class="tui-core_auth-loginLayout__contentWrap">
          <div role="main" class="tui-core_auth-loginLayout__main">
            <slot />
          </div>
        </div>
        <Footer :html="layoutData.footerHtml" />
      </div>
      <div
        v-if="layoutData.backgroundImage"
        class="tui-core_auth-loginLayout__panel-graphic"
        :style="bgStyle"
      />
    </div>
  </div>
</template>

<script>
import Footer from 'core_auth/components/layout/Footer';
import LoginNav from 'core_auth/components/layout/LoginNav';

/**
 * @typedef {object} LayoutData
 * @property {LangData} lang
 * @property {string} siteName
 * @property {string} homeUrl
 * @property {string} logoUrl
 * @property {{ url: string } | null} backgroundImage
 * @property {?string} footerHtml
 */

/**
 * @typedef {object} LangData
 * @property {string} current
 * @property {Record<string, string>} langs
 * @property {boolean} showMenu
 */

export default {
  components: {
    Footer,
    LoginNav,
  },

  props: {
    /** @type {import('vue').PropType<LayoutData>} */
    layoutData: Object,
  },

  computed: {
    bgStyle() {
      if (!this.layoutData.backgroundImage) {
        return null;
      }
      return {
        backgroundImage: 'url("' + this.layoutData.backgroundImage.url + '")',
      };
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-loginLayout {
  display: flex;
  flex-direction: column;
  flex-grow: 1;

  &__panels {
    flex-grow: 1;
  }

  &__panel-interface {
    display: flex;
    flex: auto;
    flex-flow: column;
    min-height: 100%;
  }

  &__panel-graphic {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    display: none;
    width: 50%;
    background-position: center;
    background-size: cover;
  }

  &__contentWrap {
    display: flex;
    flex: auto;
    flex-flow: column;
    align-items: center;
    justify-content: center;
    padding: var(--gap-8);
  }

  &__main {
    width: 100%;
    max-width: rem-px(400);
  }

  @media (min-width: $tui-screen-md) {
    &--hasBackground &__panels {
      grid-template-columns: 1fr 1fr;
    }

    &--hasBackground &__panel-interface {
      width: 50%;
    }

    &--hasBackground &__panel-graphic {
      display: flex;
    }
  }
}
</style>
