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
  <div class="tui-mfa_totp-verify">
    <div>{{ $str('enter_code_instructions', 'mfa_totp') }}</div>
    <form class="tui-mfa_totp-verify__form" @submit="handleSubmit">
      <div>
        <InputText
          v-model:value="token"
          :aria-label="$str('token', 'mfa_totp')"
          :aria-describedby="$id('error')"
          :placeholder="$str('enter_6_digit_code_placeholder', 'mfa_totp')"
          inputmode="numeric"
          char-length="15"
        />
        <FieldError :id="$id('error')" :error="errorMessage" />
      </div>
      <div>
        <Button
          type="submit"
          :text="$str('verify', 'mfa')"
          :styleclass="{ primary: true }"
          :loading="submitting"
        />
      </div>
    </form>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FieldError from 'tui/components/form/FieldError';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    Button,
    FieldError,
    InputText,
  },

  inheritAttrs: false,

  props: {
    data: Object,
    submitting: Boolean,
    submissionError: Object,
  },

  emits: ['submit'],

  data() {
    return {
      token: '',
      errorMessage: null,
    };
  },

  watch: {
    submissionError(err) {
      if (!err) {
        this.errorMessage = null;
        return;
      }

      if (err.type === 'verify') {
        this.errorMessage = this.$str('error:invalid_token', 'mfa_totp');
      } else {
        this.errorMessage = err.message;
      }
    },

    token() {
      this.errorMessage = null;
    },
  },

  methods: {
    handleSubmit(e) {
      e.preventDefault();
      this.$emit('submit', { token: this.token });
    },
  },
};
</script>

<style lang="scss">
.tui-mfa_totp-verify {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__form {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }
}
</style>
