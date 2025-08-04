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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module perform_goal
-->

<template>
  <FormRowStack spacing="large">
    <FormRow>
      <FormCheckbox name="enable_status_change">
        {{ $str('goal_enable_goal_progress_change', 'perform_goal') }}
      </FormCheckbox>
    </FormRow>

    <Field v-slot="{ value }" name="enable_status_change">
      <SelectParticipant
        v-if="value"
        field-name="status_change_relationship"
        :help-msg="
          $str('goal_enable_goal_progress_change_help', 'perform_goal')
        "
        :label="
          $str('goal_enable_goal_progress_change_participant', 'perform_goal')
        "
        :relationships="relationships"
      />
    </Field>
  </FormRowStack>
</template>

<script>
import Field from 'tui/components/reform/Field';
import { FormCheckbox, FormRow } from 'tui/components/uniform';
import FormRowStack from 'tui/components/form/FormRowStack';
import SelectParticipant from 'performelement_linked_review/components/SelectParticipant';

export default {
  components: {
    Field,
    FormRowStack,
    FormCheckbox,
    FormRow,
    SelectParticipant,
  },

  props: {
    relationships: Array,
  },
};
</script>
