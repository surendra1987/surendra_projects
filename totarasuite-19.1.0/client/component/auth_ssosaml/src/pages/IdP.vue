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
  <LayoutOneColumn :title="name">
    <template v-slot:content-nav>
      <PageBackLink
        :link="$url('/auth/ssosaml/plugin_settings.php')"
        :text="$str('backto', 'core', $str('manage_idps', 'auth_ssosaml'))"
      />
    </template>

    <template v-slot:header-buttons>
      <IdPNavButtons :idp-id="idp.id" :exclude="['view']" />
    </template>

    <template v-slot:content>
      <div class="tui-auth_ssosaml-idp__content">
        <MetadataCard :idp-id="idp.id" :metadata="metadata" />

        <Form role="presentation">
          <h2>{{ $str('provider_general_information', 'auth_ssosaml') }}</h2>
          <FormRow :label="$str('name', 'core')">
            <InputSizedText>{{ name }}</InputSizedText>
          </FormRow>

          <FormRow :label="$str('idp_metadata', 'auth_ssosaml')">
            <InputSizedText>
              <a
                v-if="idp.metadata.source === 'URL'"
                :href="idp.metadata.url"
                target="_blank"
              >
                {{ idp.metadata.url }}
              </a>
              <Button
                v-else-if="idp.metadata.source === 'XML'"
                :text="$str('view_xml', 'auth_ssosaml')"
                :styleclass="{ transparent: true }"
                @click="xmlOpen = true"
              />
              <Lozenge v-else :text="$str('none', 'core')" type="warning" />
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('user_identifier', 'auth_ssosaml')">
            <InputSet split :stack-below="30" char-length="30">
              <FormRow :label="$str('idp_field', 'auth_ssosaml')" vertical>
                {{ idp.idp_user_id_field || '-' }}
              </FormRow>
              <FormRow :label="$str('local_field', 'auth_ssosaml')" vertical>
                {{ userIdFieldNames[idp.totara_user_id_field] || '-' }}
              </FormRow>
            </InputSet>
          </FormRow>

          <FormRow :label="$str('status', 'core')">
            <InputSizedText>
              {{
                idp.status
                  ? $str('enabled', 'auth_ssosaml')
                  : $str('disabled', 'auth_ssosaml')
              }}
            </InputSizedText>
          </FormRow>

          <h2>{{ $str('advanced_settings', 'auth_ssosaml') }}</h2>

          <FormRow :label="$str('new_users', 'auth_ssosaml')">
            <InputSizedText>
              {{
                idp.create_users
                  ? $str('automatically_create', 'auth_ssosaml')
                  : $str('not_automatically_created', 'auth_ssosaml')
              }}
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('existing_users_label', 'auth_ssosaml')">
            <InputSizedText>
              <div class="tui-auth_ssosaml-idp__options">
                <div v-if="idp.autolink_users !== 'NO_LINK'">
                  {{ $str('existing_users_automaticallylink', 'auth_ssosaml') }}
                </div>
                <div v-if="idp.autolink_users === 'LINK_WITH_CONFIRMATION'">
                  {{
                    $str(
                      'existing_users_require_email_validation',
                      'auth_ssosaml'
                    )
                  }}
                </div>
                <div v-if="idp.autolink_users === 'NO_LINK'">-</div>
              </div>
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('saml_nameid_format', 'auth_ssosaml')">
            <InputSizedText>
              <code>{{ nameIdFormat }}</code>
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('saml_entity_id', 'auth_ssosaml')">
            <InputSizedText class="tui-auth_ssosaml-idp__entityId">
              {{ idp.saml_config.entity_id || metadata.entity_id }}
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('logout_behavior', 'auth_ssosaml')">
            <InputSizedText>
              {{ idp.logout_idp ? $str('logout_at_idp', 'auth_ssosaml') : '-' }}
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('redirect_after_logout', 'auth_ssosaml')">
            <InputSizedText>
              {{ idp.logout_url || '-' }}
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('delimiter', 'auth_ssosaml')">
            <InputSizedText>
              <code>{{ idp.field_mapping_config.delimiter }}</code>
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('signatures', 'auth_ssosaml')">
            <InputSizedText>
              <div
                v-if="signatures.length > 0"
                class="tui-auth_ssosaml-idp__options"
              >
                <div v-for="(option, i) in signatures" :key="i">
                  {{ option }}
                </div>
              </div>
              <div v-else>-</div>
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('setting_login_hide', 'auth_ssosaml')">
            <InputSizedText>
              <div class="tui-auth_ssosaml-idp__options">
                <div>
                  {{
                    idp.login_hide
                      ? $str('enabled', 'auth_ssosaml')
                      : $str('disabled', 'auth_ssosaml')
                  }}
                </div>
                <div>
                  {{ $str('login_page_url', 'auth_ssosaml', loginUrl) }}
                  <a :href="loginUrl" target="_blank">
                    {{ loginUrl }}
                  </a>
                </div>
              </div>
            </InputSizedText>
          </FormRow>

          <FormRow :label="$str('setting_debug', 'auth_ssosaml')">
            <InputSizedText>
              {{
                idp.debug
                  ? $str('enabled', 'auth_ssosaml')
                  : $str('disabled', 'auth_ssosaml')
              }}
            </InputSizedText>
          </FormRow>

          <template v-if="mappings && mappings.length">
            <h2>{{ $str('field_mappings', 'auth_ssosaml') }}</h2>

            <Table :data="mappings">
              <template v-slot:header-row>
                <HeaderCell size="4" valign="center">
                  {{ $str('local_field', 'auth_ssosaml') }}
                </HeaderCell>
                <HeaderCell size="4" valign="center">
                  {{ $str('idp_field', 'auth_ssosaml') }}
                </HeaderCell>
                <HeaderCell size="4" valign="center">
                  {{ $str('update_local_field', 'auth_ssosaml') }}
                </HeaderCell>
              </template>

              <template v-slot:row="{ row }">
                <Cell
                  size="4"
                  :column-header="$str('local_field', 'auth_ssosaml')"
                >
                  {{ fieldName(row.internal) }}
                </Cell>
                <Cell
                  size="4"
                  :column-header="$str('idp_field', 'auth_ssosaml')"
                >
                  {{ row.external }}
                </Cell>
                <Cell
                  size="4"
                  :column-header="$str('update_local_field', 'auth_ssosaml')"
                >
                  {{ updateNames[row.update] }}
                </Cell>
              </template>
            </Table>
          </template>
        </Form>
      </div>
    </template>

    <template v-slot:modals>
      <ModalPresenter
        v-if="idp.metadata.source === 'XML'"
        :open="xmlOpen"
        @request-close="xmlOpen = false"
      >
        <Modal :aria-labelledby="$id('modal-title')">
          <ModalContent
            :title="$str('metadata', 'auth_ssosaml')"
            :title-id="$id('modal-title')"
          >
            <Textarea :value="idp.metadata.xml" :rows="10" readonly />

            <template v-slot:buttons>
              <Button
                :text="$str('close', 'totara_core')"
                @click="xmlOpen = false"
              />
            </template>
          </ModalContent>
        </Modal>
      </ModalPresenter>
    </template>
  </LayoutOneColumn>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import InputSet from 'tui/components/form/InputSet';
import InputSizedText from 'tui/components/form/InputSizedText';
import Textarea from 'tui/components/form/Textarea';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import Lozenge from 'tui/components/lozenge/Lozenge';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import IdPNavButtons from 'auth_ssosaml/components/idp/IdPNavButtons';
import MetadataCard from 'auth_ssosaml/components/idp/MetadataCard';

export default {
  components: {
    Button,
    Cell,
    HeaderCell,
    Table,
    Form,
    FormRow,
    InputSet,
    InputSizedText,
    Textarea,
    LayoutOneColumn,
    PageBackLink,
    Lozenge,
    Modal,
    ModalContent,
    ModalPresenter,
    IdPNavButtons,
    MetadataCard,
  },

  props: {
    idp: { type: Object, required: true },
    metadata: { type: Object, required: true },
    fieldInfo: { type: Object, required: true },
    userIdFieldNames: { type: Object, required: true },
    loginUrl: { type: String, required: true },
  },

  data() {
    return {
      xmlOpen: false,
      updateNames: {
        CREATE: this.$str('update_local_field_create', 'auth_ssosaml'),
        LOGIN: this.$str('update_local_field_login', 'auth_ssosaml'),
      },
    };
  },

  computed: {
    name() {
      return this.idp.label || this.$str('default_label', 'auth_ssosaml');
    },

    nameIdFormat() {
      const format = this.idp.saml_config.nameid_format;
      return format ? format.split(':').slice(-1)[0] : '-';
    },

    signatures() {
      const result = [];

      if (this.idp.saml_config.sign_metadata) {
        result.push(this.$str('saml_sign_metadata', 'auth_ssosaml'));
      }
      if (this.idp.saml_config.authnrequests_signed) {
        result.push(this.$str('saml_authnrequests_signed', 'auth_ssosaml'));
      }
      if (this.idp.saml_config.wants_assertions_signed) {
        result.push(this.$str('saml_wants_assertions_signed', 'auth_ssosaml'));
      }

      return result;
    },

    mappings() {
      return this.idp.field_mapping_config.field_maps;
    },
  },

  methods: {
    fieldName(field) {
      return this.fieldInfo[field] && this.fieldInfo[field].label;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idp {
  &__subtitle {
    margin-top: var(--gap-2);
  }

  &__content {
    @include tui-stack-vertical(var(--gap-8));
  }

  &__entityId {
    overflow-wrap: break-word;
  }

  &__options {
    @include tui-stack-vertical(var(--gap-2));
  }
}
</style>
