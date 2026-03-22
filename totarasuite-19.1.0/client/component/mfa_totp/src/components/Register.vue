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
  @module mfa_totp
-->

<template>
  <Uniform
    v-model:state="formState"
    vertical
    :errors="serverErrors"
    @submit="handleSubmit"
  >
    <div class="tui-mfa_totp-register__rows">
      <FormRow :label="$str('step_1', 'mfa')">
        <div class="tui-mfa_totp-register__step1">
          <div>
            {{ $str('setup_help', 'mfa_totp') }}
          </div>
          <div class="tui-mfa_totp-register__qr">
            <img
              :src="data.qr_url"
              data-testid="qr"
              alt=""
              width="205"
              height="205"
            />
          </div>
          <div class="tui-mfa_totp-register__secret">
            {{ chunk(data.secret) }}
          </div>
        </div>
      </FormRow>
      <FormRow :label="$str('step_2', 'mfa')">
        <div>{{ $str('verify_desc', 'mfa_totp') }}</div>
        <FormText
          name="token"
          char-length="15"
          :aria-label="$str('verify', 'mfa')"
          :validations="v => [v.required()]"
          :placeholder="$str('enter_6_digit_code_placeholder', 'mfa_totp')"
          inputmode="numeric"
          autofocus
        />
      </FormRow>
      <FormRow actions>
        <Button
          type="submit"
          :text="$str('save', 'admin')"
          :loading="saving"
          :styleclass="{ primary: true }"
        />
      </FormRow>
    </div>
  </Uniform>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import { FormRow, FormText, Uniform } from 'tui/components/uniform';
import createInstanceMutation from 'mfa_totp/graphql/create_instance';

export default {
  components: {
    Button,
    FormRow,
    FormText,
    Uniform,
  },

  inheritAttrs: false,

  props: {
    data: { type: Object, required: true },
  },

  emits: ['saved'],

  data() {
    return {
      showQr: true,
      saving: false,
      formState: {
        values: {},
      },
      serverErrors: {},
    };
  },

  computed: {
    values() {
      return this.formState.values;
    },
  },

  watch: {
    values(values, old) {
      // clear server errors if they changed
      for (const key of Object.keys(this.serverErrors)) {
        if (old[key] !== values[key]) {
          delete this.serverErrors[key];
        }
      }
    },
  },

  methods: {
    async handleSubmit(values) {
      this.saving = true;
      try {
        const result = await this.$apollo.mutate({
          mutation: createInstanceMutation,
          variables: {
            input: {
              secret: this.data.secret,
              token: values.token,
            },
          },
        });
        this.$emit('saved', result.data.instance.id);
      } catch (e) {
        if (this.getGqlError(e, 'mfa_totp/invalid_token')) {
          this.serverErrors.token = this.$str(
            'error:invalid_token',
            'mfa_totp'
          );
        } else {
          throw e;
        }
      } finally {
        this.saving = false;
      }
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
     * Take a string and separate it into 4-character chunks separated by a space.
     */
    chunk(string) {
      return [...string.matchAll(/.{4}|.+$/g)].map(x => x[0]).join(' ');
    },
  },
};
</script>

<style lang="scss">
.tui-mfa_totp-register {
  &__rows {
    display: flex;
    flex-direction: column;
    gap: var(--gap-6);
  }

  &__step1 {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    align-items: flex-start;
  }

  &__qr {
    padding: var(--gap-4);
    background: var(--color-neutral-1);
    border: var(--border-width-thin) solid var(--color-neutral-4);
    border-radius: var(--border-radius-normal);
  }

  &__secret {
    padding: var(--gap-4);
    font-size: font-size-px(24);
    line-height: 1;
    background-color: var(--color-neutral-3);
    border-radius: var(--border-radius-normal);
  }
}
</style>
