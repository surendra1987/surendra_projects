<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Timothy Liew <timothy.liew@totara.com>
  @module totara_program
-->

<template>
  <div class="tui-totara_program-enrolment-options">
    <div v-if="title" class="tui-totara_program-enrolment-options__subheading">
      {{ title }}
    </div>
    <EnrolmentOptionCard
      v-for="option in enrolmentOptions"
      :key="option.id"
      :clickable="!option.is_enrolled || option.can_self_unenrol"
      :option="option"
      @click="$emit('setSelectedOption', option.id)"
    >
      <Button
        v-if="option.can_self_enrol && !option.is_enrolled"
        :text="$str('enrol', 'enrol')"
      />
      <Button
        v-else
        :text="$str('withdraw', 'totara_program')"
        :disabled="!option.can_self_unenrol"
      />
    </EnrolmentOptionCard>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import EnrolmentOptionCard from 'totara_program/components/learner/self_enrolment/EnrolmentOptionCard';

export default {
  components: { Button, EnrolmentOptionCard },

  props: {
    title: String,
    enrolmentOptions: [Object],
  },

  emits: ['setSelectedOption'],
};
</script>

<style scoped lang="scss">
.tui-totara_program-enrolment {
  &-options {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    padding-top: var(--gap-4);

    &__subheading {
      font-weight: 700;
      font-size: var(--font-body-lg-size);
    }
  }
}
</style>
