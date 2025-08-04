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

  @author Cody Finegan <cody.finegan@totara.com>
  @module auth_ssosaml
-->

<template>
  <LayoutOneColumn
    class="tui-auth_ssosaml-idpTest"
    :title="$str('test_idp_label', 'auth_ssosaml', idpLabel)"
  >
    <template v-slot:content-nav>
      <PageBackLink
        :link="$url('/auth/ssosaml/plugin_settings.php')"
        :text="$str('backto', 'core', $str('manage_idps', 'auth_ssosaml'))"
      />
    </template>

    <template v-slot:header-buttons>
      <IdPNavButtons :idp-id="idpId" :exclude="['test', 'view', 'delete']" />
    </template>

    <template v-slot:pre-body>
      <NotificationBanner
        v-if="message"
        :type="message.type"
        :message="message.message"
      />
    </template>

    <template v-slot:content>
      <div class="tui-auth_ssosaml-idpTest__content">
        <Uniform :initial-values="values" @submit="handleSubmit">
          <FormRow :label="$str('test_choice', 'auth_ssosaml')">
            <FormRadioGroup name="type">
              <Radio key="login" value="login">
                {{ $str('test_login', 'auth_ssosaml') }}
              </Radio>
              <Radio key="logout" value="logout">
                {{ $str('test_logout', 'auth_ssosaml') }}
              </Radio>
            </FormRadioGroup>
          </FormRow>

          <FormRow actions>
            <ButtonGroup>
              <Button
                type="submit"
                :text="$str('test', 'auth_ssosaml')"
                :styleclass="{ primary: 'true' }"
                :loading="submitting"
              />

              <ActionLink
                :text="$str('cancel', 'core')"
                :disabled="submitting"
                :href="$url('/auth/ssosaml/plugin_settings.php')"
              />
            </ButtonGroup>
          </FormRow>
        </Uniform>

        <div
          v-if="attributes.length"
          class="tui-auth_ssosaml-idpTest__sectionHeader"
        >
          <div class="tui-auth_ssosaml-idpTest__sectionHeader-label">
            {{ $str('attributes', 'auth_ssosaml') }}
          </div>
        </div>

        <Table v-if="attributes.length" :data="attributes">
          <template v-slot:header-row>
            <HeaderCell>
              {{ $str('attribute_field_name', 'auth_ssosaml') }}
            </HeaderCell>
            <HeaderCell>
              {{ $str('attribute_field_values', 'auth_ssosaml') }}
            </HeaderCell>
          </template>
          <template v-slot:row="{ row }">
            <Cell :column-header="$str('attribute_field_name', 'auth_ssosaml')">
              {{ row.name }}
            </Cell>
            <Cell
              :column-header="$str('attribute_field_values', 'auth_ssosaml')"
            >
              {{ row.value }}
            </Cell>
          </template>
        </Table>

        <div
          v-if="debugLogRows && debugLogRows.length"
          class="tui-auth_ssosaml-idpTest__sectionHeader"
        >
          <div class="tui-auth_ssosaml-idpTest__sectionHeader-label">
            {{ $str('logs', 'core') }}
          </div>
        </div>

        <SamlLogTable
          v-if="debugLogRows && debugLogRows.length"
          :rows="debugLogRows"
        />
      </div>
    </template>
  </LayoutOneColumn>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Table from 'tui/components/datatable/Table';
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Radio from 'tui/components/form/Radio';
import ActionLink from 'tui/components/links/ActionLink';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import { FormRadioGroup, FormRow, Uniform } from 'tui/components/uniform';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import IdPNavButtons from 'auth_ssosaml/components/idp/IdPNavButtons';
import SamlLogTable from 'auth_ssosaml/components/log/SamlLogTable';
import PageBackLink from 'tui/components/layouts/PageBackLink';

export default {
  components: {
    Button,
    ButtonGroup,
    Table,
    Cell,
    HeaderCell,
    Radio,
    ActionLink,
    LayoutOneColumn,
    FormRow,
    Uniform,
    FormRadioGroup,
    NotificationBanner,
    IdPNavButtons,
    SamlLogTable,
    PageBackLink,
  },

  props: {
    idpLabel: String,
    testUrl: String,
    message: Object,
    attributes: Array,
    idpId: String,
    debugLogRows: Array,
  },

  data() {
    return {
      submitting: false,
      values: {
        type: 'login',
      },
    };
  },

  methods: {
    async handleSubmit(values) {
      this.submitting = true;

      // handle back button
      const handler = event => {
        if (event.persisted) {
          this.submitting = false;
          window.removeEventListener('pageshow', handler);
        }
      };
      window.addEventListener('pageshow', handler);

      window.location = this.$url(this.testUrl, { test_action: values.type });
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idpTest {
  @include font(body);

  &__subtitle {
    margin-top: var(--gap-2);
  }

  &__sectionHeader {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: var(--gap-8) 0 var(--gap-2) 0;
    padding: var(--gap-4);
    background-color: var(--color-neutral-3);

    &-label {
      @include font(h4);
    }
  }
}
</style>
