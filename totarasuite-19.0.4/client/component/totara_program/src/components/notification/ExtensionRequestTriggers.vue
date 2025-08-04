<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
  @module totara_program
-->
<template>
  <FormRow
    :label="
      $str(
        'notification_extension_request_response_type_selector',
        'totara_program'
      )
    "
    :is-stacked="false"
  >
    <FormCheckboxGroup
      name="extensionRequestTriggers"
      :validations="v => [v.required()]"
      :required="required"
      :disabled="disabled"
    >
      <Checkbox value="request_granted">{{
        $str('notification_extension_granted_radio_label', 'totara_program')
      }}</Checkbox>
      <Checkbox value="request_denied">{{
        $str('notification_extension_denied_radio_label', 'totara_program')
      }}</Checkbox>
    </FormCheckboxGroup>
    <slot name="override-toggle" />
  </FormRow>
</template>

<script>
// Imports are absolute paths that are interpreted by the Vue compiler, they do not need to correspond to paths here
import { FormCheckboxGroup, FormRow } from 'tui/components/uniform';
import Checkbox from 'tui/components/form/Checkbox';

export default {
  components: { FormCheckboxGroup, FormRow, Checkbox },

  props: {
    required: Boolean,
    disabled: Boolean,
  },
};
</script>
