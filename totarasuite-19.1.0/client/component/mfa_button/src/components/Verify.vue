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
  @module mfa_button
-->

<template>
  <div class="tui-mfa_button-verify">
    <form class="tui-mfa_button-verify__form" @submit="handleSubmit">
      <div>
        <Button
          type="submit"
          :text="$str('verify', 'mfa')"
          :styleclass="{ primary: true }"
          :loading="submitting"
        />
        <FieldError :error="errorMessage" />
      </div>
    </form>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FieldError from 'tui/components/form/FieldError';

export default {
  components: {
    Button,
    FieldError,
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
    };
  },

  computed: {
    errorMessage() {
      if (!this.submissionError) {
        return null;
      }

      return this.submissionError.message;
    },
  },

  methods: {
    handleSubmit(e) {
      e.preventDefault();
      this.$emit('submit', {});
    },
  },
};
</script>

<style lang="scss">
.tui-mfa_button-verify {
  &__form {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }
}
</style>
