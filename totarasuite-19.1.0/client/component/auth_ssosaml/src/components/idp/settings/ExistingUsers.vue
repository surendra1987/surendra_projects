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
  <CheckboxGroup
    name="existingUsers"
    use-object
    :value="value"
    @input="handleChange"
  >
    <Checkbox value="automaticallyLink">
      {{ $str('existing_users_automaticallylink', 'auth_ssosaml') }}
    </Checkbox>
    <Checkbox
      value="requireEmailValidation"
      :disabled="!value.automaticallyLink"
    >
      {{ $str('existing_users_require_email_validation', 'auth_ssosaml') }}
    </Checkbox>
  </CheckboxGroup>
</template>

<script>
import Checkbox from 'tui/components/form/Checkbox';
import CheckboxGroup from 'tui/components/form/CheckboxGroup';

export default {
  components: {
    Checkbox,
    CheckboxGroup,
  },

  props: {
    value: Object,
  },

  emits: ['input', 'update:value'],

  methods: {
    handleChange(value) {
      if (!value.automaticallyLink && value.requireEmailValidation) {
        value = { ...value, requireEmailValidation: false };
      }
      this.$emit('update:value', value);
      this.$emit('input', value);
    },
  },
};
</script>
