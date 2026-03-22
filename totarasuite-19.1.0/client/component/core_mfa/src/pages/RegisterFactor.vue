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
  @module core_mfa
-->

<template>
  <LayoutOneColumn :title="title">
    <template v-slot:content-nav>
      <PageBackLink
        :link="backUrl"
        :text="
          $str(
            'backto',
            'core',
            $str('manage_multi_factor_authentication', 'mfa')
          )
        "
      />
    </template>

    <template v-slot:content>
      <component :is="registerComponent" :data="data" @saved="handleSaved" />
    </template>
  </LayoutOneColumn>
</template>

<script>
import tui from 'tui/tui';
import Button from 'tui/components/buttons/Button';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import ModalPresenter from 'tui/components/modal/ModalPresenter';

export default {
  components: {
    Button,
    Dropdown,
    DropdownItem,
    DropdownButton,
    LayoutOneColumn,
    PageBackLink,
    ModalPresenter,
  },

  props: {
    factor: { type: String, required: true },
    title: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
  },

  data() {
    return {
      addOpen: false,
    };
  },

  computed: {
    registerComponent() {
      return tui.asyncComponent(`mfa_${this.factor}/components/Register`);
    },

    backUrl() {
      return this.$url('/mfa/user_preferences.php');
    },
  },

  methods: {
    handleSaved() {
      window.location = this.backUrl;
    },
  },
};
</script>
