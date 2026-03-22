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
  @module tui
-->

<template>
  <Uniform
    :initial-values="initialValues"
    input-width="full"
    vertical
    @submit="$emit('submit', $event)"
  >
    <div class="tui-core_auth-loginForm__content">
      <FormRow
        :label="$str(loginOptions.email ? 'usernameemail' : 'username', 'core')"
      >
        <FormText
          name="username"
          size="large"
          :autofocus="loginOptions.autofocus"
        />
      </FormRow>
      <FormRow :label="$str('password', 'core')">
        <FormPassword name="password" size="large" autocomplete="off" />
      </FormRow>
      <div class="tui-core_auth-loginForm__options">
        <div v-if="loginOptions.rememberMode">
          <FormCheckbox name="remember">
            {{ rememberMeText }}
          </FormCheckbox>
        </div>
        <a :href="$url('/login/forgot_password.php')">
          {{ $str('trouble_signing_in', 'core') }}
        </a>
      </div>
      <div class="tui-core_auth-loginForm__submitWrap">
        <Button
          type="submit"
          :text="$str('login', 'core')"
          :styleclass="{ primary: true }"
          :loading="submitting"
        />
      </div>
    </div>
    <div v-if="cookieHelpContent" class="tui-core_auth-loginForm__help">
      <InfoIconButton :is-help-for="cookieHelpContent.heading" size="lg">
        <div>
          <div class="tui-core_auth-loginForm__heading">
            {{ cookieHelpContent.heading }}
          </div>
          <div v-html="cookieHelpContent.text" />
        </div>
      </InfoIconButton>
      <div>{{ cookieHelpContent.heading }}</div>
    </div>
  </Uniform>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import {
  FormCheckbox,
  FormPassword,
  FormRow,
  FormText,
  Uniform,
} from 'tui/components/uniform';
import InfoIconButton from 'tui/components/buttons/InfoIconButton';

export default {
  components: {
    Button,
    FormCheckbox,
    FormPassword,
    FormRow,
    FormText,
    InfoIconButton,
    Uniform,
  },

  props: {
    initialValues: Object,
    loginOptions: { type: Object, default: () => ({}) },
    cookieHelpContent: Object,
    submitting: Boolean,
  },

  emits: ['submit'],

  computed: {
    rememberMeText() {
      return this.loginOptions.rememberMode === 'USERNAME'
        ? this.$str('rememberusername', 'admin')
        : this.$str('remember_me', 'core');
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-loginForm {
  &__options {
    display: flex;
    flex-flow: row wrap;
    gap: var(--gap-2);
    align-items: baseline;
    justify-content: space-between;
  }

  &__submitWrap {
    display: flex;
    flex-flow: column;
  }

  &__content {
    display: flex;
    flex-flow: column;
    gap: var(--gap-6);
  }

  &__help {
    display: flex;
  }

  &__heading {
    @include font(h3);
    margin-bottom: var(--gap-4);
  }
}
</style>
