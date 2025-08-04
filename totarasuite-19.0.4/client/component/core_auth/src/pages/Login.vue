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
  @module core_auth
-->

<template>
  <LoginLayout :layout-data="layoutData">
    <div class="tui-core_auth-login">
      <h1 id="main-header" aria-level="1" class="tui-core_auth-login__title">
        {{ $str('login', 'core') }}
      </h1>

      <div v-if="error" class="tui-core_auth-login__error">
        <div class="tui-core_auth-login__errorInner">
          {{ error.message }}
        </div>
      </div>

      <template v-if="methodOptions.length > 0">
        <StackChooser
          :options="methodOptions"
          :loading-option="loadingMethod"
          @select="handleMethodSelect"
        />

        <div class="tui-core_auth-login__separator" />
      </template>

      <LoginForm
        :initial-values="formData"
        :login-options="loginOptions"
        :cookie-help-content="cookieHelpContent"
        :submitting="submitting"
        @submit="handleSubmit"
      />

      <div v-if="noAccountHtml">
        <div>{{ $str('dont_have_an_account', 'core') }}</div>
        <div @click="handleNoAccountClick" v-html="noAccountHtml" />
      </div>
    </div>
  </LoginLayout>
</template>

<script>
import { config } from 'tui/config';
import { redirectWithPost } from 'tui/dom/form';
import LoginLayout from 'core_auth/components/layout/LoginLayout';
import LoginForm from 'core_auth/components/login/LoginForm';
import StackChooser from 'core_auth/components/ui/StackChooser';

export default {
  components: {
    LoginLayout,
    LoginForm,
    StackChooser,
  },

  props: {
    /** @type {import('vue').PropType<import('core_auth/components/layout/LoginLayout').LayoutData>} */
    layoutData: { type: Object, required: true },
    cookieHelpContent: Object,
    error: Object,
    formData: Object,
    loginOptions: { type: Object, default: () => ({}) },
  },

  data() {
    return {
      submitting: false,
      loadingMethod: null,
    };
  },

  computed: {
    noAccountHtml() {
      const { guest, signup } = this.loginOptions;
      let str = null;
      if (signup && guest) {
        str = this.$str('no_account_options_create_or_guest', 'core');
      } else if (signup) {
        str = this.$str('no_account_options_create', 'core');
      } else if (guest) {
        str = this.$str('no_account_options_guest', 'core');
      } else {
        return '';
      }

      return str
        .replace(
          /<CreateLink>(.*)<\/CreateLink>/,
          '<a href="' + this.$url('/login/signup.php') + '">$1</a>'
        )
        .replace(
          /<GuestLink>(.*)<\/GuestLink>/,
          // Ideally we'd insert a Vue component here, e.g. with PLATFORM-44.
          // This is a workaround for now.
          // `data-guest-link` is detected in handleNoAccountClick.
          '<a href="javascript:;" data-guest-link>$1</a>'
        );
    },

    methodOptions() {
      if (!this.loginOptions.idps) {
        return [];
      }
      return this.loginOptions.idps.map(idp => ({
        id: idp.url,
        name: idp.name,
        url: idp.url,
      }));
    },
  },

  methods: {
    handleSubmit(values) {
      this.submitting = true;

      // handle back button
      const handler = event => {
        if (event.persisted) {
          this.submitting = false;
          window.removeEventListener('pageshow', handler);
        }
      };
      window.addEventListener('pageshow', handler);

      redirectWithPost(this.$url('/login/index.php'), {
        username: values.username,
        password: values.password,
        rememberusernamechecked: values.remember,
        logintoken: config.sesskey,
      });
    },

    handleNoAccountClick(e) {
      if (e.target.getAttribute('data-guest-link') != null) {
        redirectWithPost(this.$url('/login/index.php'), {
          username: 'guest',
          password: 'guest',
          logintoken: config.sesskey,
        });
      }
    },

    handleMethodSelect(option) {
      // handle back button
      const handler = event => {
        if (event.persisted) {
          this.loadingMethod = null;
          window.removeEventListener('pageshow', handler);
        }
      };
      window.addEventListener('pageshow', handler);

      this.loadingMethod = option.id;
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-login {
  display: flex;
  flex-flow: column;
  gap: var(--gap-6);

  &__separator {
    border: 1px solid var(--color-neutral-5);
    border-width: 1px 0 0 0;
  }

  &__errorInner {
    display: inline-flex;
    @include font(body-sm);
    padding: var(--gap-3);
    color: var(--color-prompt-alert);
    background-color: var(--color-prompt-alert-bg);
    border-radius: 8px;
  }

  &__title {
    margin: 0;
  }
}
</style>
