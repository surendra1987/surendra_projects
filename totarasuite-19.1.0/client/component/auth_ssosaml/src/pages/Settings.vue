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
  @module auth_ssosaml
-->

<template>
  <LayoutOneColumn
    class="tui-auth_ssosaml-settings"
    :title="$str('pluginname', 'auth_ssosaml')"
  >
    <template v-slot:pre-body>
      <div class="tui-auth_ssosaml-settings__subtitle">
        {{ $str('settings_subtitle', 'auth_ssosaml') }}
      </div>
    </template>

    <template v-slot:content>
      <div class="tui-auth_ssosaml-settings__content">
        <NotificationBanner
          v-if="!opensslAvailable"
          :message="$str('warning_openssl_not_installed', 'auth_ssosaml')"
          type="warning"
        />

        <Tabs>
          <Tab id="idps" :name="$str('identity_providers', 'auth_ssosaml')">
            <ManageIdPs :openssl-available="opensslAvailable" />
          </Tab>
          <Tab id="settings" :name="$str('global_settings', 'auth_ssosaml')">
            <GlobalSettings :config="config" :field-info="fieldInfo" />
          </Tab>
        </Tabs>
      </div>
    </template>
  </LayoutOneColumn>
</template>

<script>
import { notify } from 'tui/notifications';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import ManageIdPs from 'auth_ssosaml/components/idp/ManageIdPs';
import GlobalSettings from 'auth_ssosaml/components/plugin_settings/GlobalSettings';

export default {
  components: {
    LayoutOneColumn,
    NotificationBanner,
    Tab,
    Tabs,
    ManageIdPs,
    GlobalSettings,
  },

  props: {
    opensslAvailable: Boolean,
    config: Object,
    fieldInfo: Object,
  },

  mounted() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('edit_success')) {
      notify({ message: this.$str('idp_saved', 'auth_ssosaml') });
      window.history.replaceState(null, null, window.location.pathname);
    }
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-settings {
  &__subtitle {
    margin-top: var(--gap-2);
  }

  &__content {
    @include tui-stack-vertical(var(--gap-6));
  }
}
</style>
