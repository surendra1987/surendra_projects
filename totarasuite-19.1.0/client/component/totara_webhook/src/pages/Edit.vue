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

  @author ben fesili <ben.fesili@totara.com>
  @module totara_webhook
-->

<script setup>
import { ref } from 'vue';
import { url, totaraUrl } from 'tui/util';
import apollo from 'tui/apollo/client';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import Totara_webhookForm from 'totara_webhook/components/Totara_webhookForm';
import createMutation from 'totara_webhook/graphql/create_totara_webhook';
import updateMutation from 'totara_webhook/graphql/update_totara_webhook';

const props = defineProps({
  data: Object,
  availableEvents: Array,
});

const submitting = ref(false);

const backUrl = totaraUrl('/totara/webhook/totara_webhook/');

async function handleSubmit(value) {
  // format the events
  let event_classes = value.events.map(event => event.class);
  submitting.value = true;
  try {
    const input = {
      name: value.name,
      endpoint: value.endpoint,
      events: event_classes,
      status: value.status,
      immediate: value.immediate,
    };
    if (props.data.id) {
      input.reference = { id: props.data.id };
    }
    await apollo.mutate({
      mutation: props.data.id ? updateMutation : createMutation,
      variables: { input },
    });
    window.location = url(backUrl, {
      notify: props.data.id ? 'saved' : 'created',
    });
  } finally {
    submitting.value = false;
  }
}

function handleCancel() {
  window.location = backUrl;
}
</script>

<template>
  <LayoutOneColumn
    :title="
      $str(
        data.id ? 'edit_totara_webhook' : 'create_totara_webhook',
        'totara_webhook'
      )
    "
  >
    <template v-slot:content-nav>
      <PageBackLink
        :link="backUrl"
        :text="
          $str(
            'backto',
            'core',
            $str('manage_totara_webhooks', 'totara_webhook')
          )
        "
      />
    </template>

    <template v-slot:content>
      <Totara_webhookForm
        :initial-values="data || {}"
        :available-events="availableEvents"
        :submitting="submitting"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </template>
  </LayoutOneColumn>
</template>

<style lang="scss">
.tui-totara_webhookCreate {
  &__content {
    display: flex;
    flex-flow: column;
    gap: var(--gap-4);
  }
}
</style>
