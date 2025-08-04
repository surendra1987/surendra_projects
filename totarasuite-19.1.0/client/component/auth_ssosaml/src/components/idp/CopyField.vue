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
  <InputGroup :char-length="charLength">
    <InputGroupInput type="text" monospace readonly :value="value" />
    <InputGroupButton
      :text="$str('copy', 'auth_ssosaml')"
      @click="copy(value)"
    />
  </InputGroup>
</template>

<script>
import { copyText } from 'tui/dom/clipboard';
import { notify } from 'tui/notifications';
import InputGroup from 'tui/components/form/InputGroup';
import InputGroupInput from 'tui/components/form/InputGroupInput';
import InputGroupButton from 'tui/components/form/InputGroupButton';
import { charLengthProp } from 'tui/components/form/form_common';

export default {
  components: {
    InputGroup,
    InputGroupInput,
    InputGroupButton,
  },

  props: {
    labelId: String,
    charLength: charLengthProp,
    value: String,
  },

  methods: {
    copy(text) {
      copyText(text);
      notify({ message: this.$str('copied_to_clipboard', 'auth_ssosaml') });
    },
  },
};
</script>
