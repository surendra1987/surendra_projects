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
  <LayoutOneColumn :title="$str('x_logs', 'auth_ssosaml', idpName)">
    <template v-slot:content-nav>
      <PageBackLink
        :link="$url('/auth/ssosaml/plugin_settings.php')"
        :text="$str('backto', 'core', $str('manage_idps', 'auth_ssosaml'))"
      />
    </template>

    <template v-slot:header-buttons>
      <IdPNavButtons :idp-id="idpId" :exclude="['logs', 'view', 'delete']" />
    </template>

    <template v-slot:content>
      <SamlLog v-if="debugEnabled" :idp-id="idpId" />
      <p v-else>
        {{ $str('enable_debug_to_view_logs', 'auth_ssosaml') }}
      </p>
    </template>
  </LayoutOneColumn>
</template>

<script>
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import IdPNavButtons from 'auth_ssosaml/components/idp/IdPNavButtons';
import SamlLog from 'auth_ssosaml/components/log/SamlLog';

export default {
  components: {
    LayoutOneColumn,
    PageBackLink,
    IdPNavButtons,
    SamlLog,
  },

  props: {
    idpId: { type: String, required: true },
    idpName: { type: String, required: true },
    debugEnabled: { type: Boolean, default: true },
  },
};
</script>
