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
    :title="
      isNew
        ? $str('add_idp_configuration', 'auth_ssosaml')
        : $str('edit_idp_configuration', 'auth_ssosaml')
    "
  >
    <template v-slot:header-buttons>
      <IdPNavButtons :idp-id="idp.id" :exclude="['edit']" />
    </template>

    <template v-slot:content>
      <div class="tui-auth_ssosaml-idpEdit__content">
        <MetadataCard
          :idp-id="idp.id"
          :metadata="metadata"
          :initially-open="isNew"
        />

        <IdpEditForm
          :initial-values="values"
          :field-info="fieldInfo"
          :name-id-formats="nameIdFormats"
          :user-id-field-names="userIdFieldNames"
          :submitting="submitting"
          :return-url="returnPath"
          :errors="serverErrors"
          :default-entity-id="metadata.default_entity_id"
          @update="updateValues"
          @submit="handleSubmit"
        />
      </div>
    </template>
  </LayoutOneColumn>
</template>

<script>
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import IdpEditForm from 'auth_ssosaml/components/idp/IdpEditForm';
import IdPNavButtons from 'auth_ssosaml/components/idp/IdPNavButtons';
import MetadataCard from 'auth_ssosaml/components/idp/MetadataCard';
import updateMutation from 'auth_ssosaml/graphql/update_idp';
import { config } from 'tui/config';

const idpsPath = '/auth/ssosaml/plugin_settings.php';

export default {
  components: {
    LayoutOneColumn,
    IdpEditForm,
    IdPNavButtons,
    MetadataCard,
  },

  props: {
    idp: { type: Object, required: true },
    metadata: { type: Object, required: true },
    userIdFieldNames: { type: Object, required: true },
    fieldInfo: { type: Object, required: true },
    nameIdFormats: { type: Array, required: true },
  },

  data() {
    return {
      submitting: false,
      values: {
        metadataSource: 'URL',
        userIdField: { totara: 'username' },
        advancedSettings: {
          fieldMaps: [],
          signatures: {},
          nameIdFormat: 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
          logoutIdp: true,
          existingUsers: {},
        },
      },
      serverErrors: {},
    };
  },

  computed: {
    isNew() {
      return this.idp.metadata.source === 'NONE';
    },

    /**
     * URL to return to after saving/cancelling.
     */
    returnPath() {
      // Figure out from referrer, or fall back to listing page
      const prevPage =
        document.referrer &&
        document.referrer.startsWith(config.wwwroot) &&
        document.referrer.substring(config.wwwroot.length);
      return prevPage || idpsPath;
    },
  },

  created() {
    const { idp } = this;
    if (idp) {
      this.values = {
        metadataSource:
          idp.metadata.source === 'NONE' ? 'URL' : idp.metadata.source,
        metadataUrl: idp.metadata.url,
        metadataBlob: idp.metadata.xml,
        label: idp.label,
        userIdField: {
          external: idp.idp_user_id_field,
          internal: idp.totara_user_id_field || 'username',
        },
        advancedSettings: {
          attributeDelimiter: idp.field_mapping_config.delimiter,
          fieldMaps: idp.field_mapping_config.field_maps,
          signatures: {
            signMetadata: idp.saml_config.sign_metadata,
            signAuthnRequests: idp.saml_config.authnrequests_signed,
            wantsAssertionsSigned: idp.saml_config.wants_assertions_signed,
          },
          createUsers: idp.create_users,
          existingUsers: {
            automaticallyLink: idp.autolink_users !== 'NO_LINK',
            requireEmailValidation:
              idp.autolink_users === 'LINK_WITH_CONFIRMATION',
          },
          nameIdFormat: idp.saml_config.nameid_format,
          entityId: idp.saml_config.entity_id,
          logoutIdp: idp.logout_idp,
          logoutUrl: idp.logout_url,
          debug: idp.debug,
          loginHide: idp.login_hide,
        },
      };
    }
  },

  methods: {
    async handleSubmit(values) {
      const advanced = values.advancedSettings;

      // Skip any mappings that are already mapped via the user identifier field
      const fieldMaps = advanced.fieldMaps.filter(
        x => x.internal != values.userIdField.internal
      );

      const input = {
        id: this.idp.id,
        attributes: {
          metadata: {
            source: values.metadataSource,
            url: values.metadataUrl,
            xml: values.metadataBlob,
          },
          label: values.label,
          idp_user_id_field: values.userIdField.external,
          totara_user_id_field: values.userIdField.internal,
          debug: advanced.debug || false,
          login_hide: advanced.loginHide || false,
          create_users: advanced.createUsers || false,
          autolink_users: advanced.existingUsers.automaticallyLink
            ? advanced.existingUsers.requireEmailValidation
              ? 'LINK_WITH_CONFIRMATION'
              : 'LINK_NO_CONFIRMATION'
            : 'NO_LINK',
          field_mapping_config: {
            delimiter: advanced.attributeDelimiter,
            field_maps: fieldMaps,
          },
          logout_idp: advanced.logoutIdp,
          logout_url: advanced.logoutUrl,
        },
        saml_config: {
          sign_metadata: advanced.signatures.signMetadata,
          authnrequests_signed: advanced.signatures.signAuthnRequests,
          wants_assertions_signed: advanced.signatures.wantsAssertionsSigned,
          nameid_format: advanced.nameIdFormat,
          entity_id: advanced.entityId,
        },
      };

      this.submitting = true;
      this.serverErrors = {};

      try {
        await this.$apollo.mutate({
          mutation: updateMutation,
          variables: { input },
        });

        window.location = this.$url(
          this.returnPath,
          this.returnPath === idpsPath ? { edit_success: true } : null
        );
      } catch (e) {
        if (this.getGqlError(e, 'auth_ssosaml/invalid_metadata')) {
          this.addServerError(
            values.metadataSource === 'URL' ? 'metadataUrl' : 'metadataBlob',
            this.$str('error:invalid_metadata', 'auth_ssosaml')
          );
        } else if (
          this.getGqlError(e, 'auth_ssosaml/duplicate_idp_entity_id')
        ) {
          this.addServerError(
            values.metadataSource === 'URL' ? 'metadataUrl' : 'metadataBlob',
            this.$str('error:duplicate_idp_entity_id', 'auth_ssosaml')
          );
        } else {
          throw e;
        }

        const firstError = Object.keys(this.serverErrors)[0];
        if (firstError) {
          const el = this.$el.querySelector('[name="' + firstError + '"]');
          if (el) {
            el.focus();
          }
        }
      } finally {
        this.submitting = false;
      }
    },

    updateValues(values) {
      // clear server errors if they changed
      // eslint-disable-next-line
      for (const [key] of Object.entries(this.serverErrors)) {
        if (this.values[key] !== values[key]) {
          delete this.serverErrors[key];
        }
      }
      // clear metadata errors if source changes
      if (values.metadataSource !== this.values.metadataSource) {
        delete this.serverErrors.metadataUrl;
        delete this.serverErrors.metadataBlob;
      }

      this.values = values;
    },

    /**
     * Find a GraphQL error with the given category.
     *
     * @param {Error} e
     * @param {string} category
     */
    getGqlError(e, category) {
      const gqlError = e.graphQLErrors && e.graphQLErrors[0];
      return gqlError &&
        gqlError.extensions &&
        gqlError.extensions.category === category
        ? category
        : null;
    },

    /**
     * Add a server error to the form.
     *
     * @param {string} field
     * @param {string} message
     */
    addServerError(field, message) {
      this.serverErrors[field] = message;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idpEdit {
  &__subtitle {
    margin-top: var(--gap-2);
  }

  &__content {
    @include tui-stack-vertical(var(--gap-8));
  }
}
</style>
