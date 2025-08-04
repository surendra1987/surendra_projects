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
  <div class="tui-auth_ssosaml-globalSettings">
    <h2 class="tui-auth_ssosaml-globalSettings__title">
      {{ $str('user_attribute_locking', 'auth_ssosaml') }}
    </h2>
    <Uniform :initial-values="initialValues" @submit="handleSubmit">
      <UserAttributes path="fields" :field-info="fieldInfo" />

      <FormRow actions>
        <Button
          type="submit"
          :text="$str('save', 'totara_core')"
          :styleclass="{ primary: 'true' }"
          :loading="submitting"
        />
      </FormRow>
    </Uniform>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import { FormRow, Uniform } from 'tui/components/uniform';
import UserAttributes from 'auth_ssosaml/components/plugin_settings/UserAttributes';
import updateMutation from 'auth_ssosaml/graphql/update_plugin_config';
import { notify } from 'tui/notifications';

export default {
  components: {
    Button,
    FormRow,
    Uniform,
    UserAttributes,
  },

  props: {
    config: { type: Object, required: true },
    fieldInfo: { type: Object, required: true },
  },

  data() {
    return {
      submitting: false,
    };
  },

  computed: {
    initialValues() {
      return {
        fields: this.config.fields.map(field => ({
          id: field.id,
          locked: field.locked,
        })),
      };
    },
  },

  methods: {
    async handleSubmit(values) {
      this.submitting = true;
      try {
        await this.$apollo.mutate({
          mutation: updateMutation,
          variables: {
            input: {
              fields: values.fields,
            },
          },
        });

        notify({
          message: this.$str('changessaved', 'core'),
        });
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-globalSettings {
  @include tui-stack-vertical(var(--gap-4));

  &__title {
    margin: 0;
  }
}
</style>
