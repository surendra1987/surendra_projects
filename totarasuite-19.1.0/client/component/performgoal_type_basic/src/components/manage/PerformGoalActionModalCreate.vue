<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module performgoal_type_basic
-->

<script setup>
import { computed, ref, watch } from 'vue';
import tui from 'tui/tui';
import { useQuery } from 'tui/apollo/composable';
import ComponentLoading from 'tui/components/loading/ComponentLoading';
import MethodChooser from 'performgoal_type_basic/components/manage/create/MethodChooser';
import configQuery from 'goaltype_basic/graphql/creation_config';

defineProps({
  // Subject ID we are creating a personal goal for
  subjectId: [String, Number],
  // Title ID so the modal can reference it
  titleId: { type: String, required: true },
});

defineEmits(['close', 'request-close', 'submitShow', 'submitted']);

const config = useQuery(configQuery);

const createOptions = computed(() => config.result.value?.config.options);

const selectedOption = ref(null);
const presetData = ref(null);

watch(
  createOptions,
  options => {
    // If there is only one create option, skip directly to that
    if (selectedOption.value === null && options?.length === 1) {
      selectedOption.value = options[0];
    }
  },
  { immediate: true }
);

const selectedOptionComponent = computed(() =>
  selectedOption.value
    ? tui.asyncComponent(selectedOption.value.ui_component)
    : null
);

function handleChoose(option) {
  presetData.value = null;
  selectedOption.value = option;
}

function handleBack() {
  if (createOptions.value.length === 1) {
    this.$emit('close');
  } else {
    selectedOption.value = null;
  }
}

function handleShowEditForm(data) {
  presetData.value = data.preset ?? null;
  selectedOption.value = createOptions.value.find(
    x => x.id === 'goaltype_basic/manual'
  );
}
</script>

<template>
  <ComponentLoading
    v-if="!config.result.value"
    class="tui-performGoalTypeBasic__loading"
  />
  <component
    :is="selectedOptionComponent"
    v-else-if="selectedOptionComponent"
    :subject-id="subjectId"
    :title-id="titleId"
    :preset-data="presetData"
    @back="handleBack"
    @show-edit-form="handleShowEditForm"
    @close="$emit('close', $event)"
    @request-close="$emit('request-close', $event)"
    @submit-show="$emit('submitShow', $event)"
    @submitted="$emit('submitted', $event)"
  />
  <MethodChooser
    v-else
    :title-id="titleId"
    :options="createOptions"
    :loading="config.loading.value"
    @choose="handleChoose"
    @close="$emit('close', $event)"
    @request-close="$emit('request-close', $event)"
  />
</template>

<style lang="scss">
.tui-performGoalTypeBasic {
  &__loading {
    flex: 1;
  }
}
</style>
