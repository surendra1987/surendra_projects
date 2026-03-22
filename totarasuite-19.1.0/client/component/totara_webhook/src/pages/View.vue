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
import Button from 'tui/components/buttons/Button';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import InputSizedText from 'tui/components/form/InputSizedText';
import InputGroup from 'tui/components/form/InputGroup';
import InputGroupInput from 'tui/components/form/InputGroupInput';
import InputGroupButton from 'tui/components/form/InputGroupButton';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Refresh from 'tui/components/icons/Refresh';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import rotateWebhookAuthConfigMutation from 'totara_webhook/graphql/rotate_auth_config_totara_webhook';
import webhookQuery from 'totara_webhook/graphql/totara_webhook';
</script>

<template>
  <LayoutOneColumn :title="$str('totara_webhooks', 'totara_webhook')">
    <template v-slot:content-nav>
      <PageBackLink
        :link="$url('/totara/webhook/totara_webhook/')"
        :text="
          $str(
            'backto',
            'core',
            $str('manage_totara_webhooks', 'totara_webhook')
          )
        "
      />
    </template>

    <template v-slot:header-buttons>
      <Button
        v-if="canManage"
        variant="primary"
        :href="$url('/totara/webhook/totara_webhook/edit.php', { id: data.id })"
        :text="$str('edit', 'core')"
      />
    </template>

    <template v-slot:content>
      <Form role="presentation">
        <FormRow :label="$str('name', 'totara_webhook')">
          <InputSizedText>{{ data.name }}</InputSizedText>
        </FormRow>
        <FormRow :label="$str('endpoint', 'totara_webhook')">
          <InputSizedText>{{ `https://${data.endpoint}` }}</InputSizedText>
        </FormRow>
        <FormRow :label="$str('events', 'totara_webhook')">
          <InputSizedText v-for="event in data.events" :key="event.class"
            >{{ event.name }}
          </InputSizedText>
        </FormRow>
        <FormRow :label="$str('webhook_dispatch_timing', 'totara_webhook')">
          <InputSizedText>{{
            $str(
              data.immediate ? 'webhook_immediate' : 'webhook_scheduled',
              'totara_webhook'
            )
          }}</InputSizedText>
        </FormRow>
        <FormRow :label="$str('totara_webhook_status', 'totara_webhook')">
          <InputSizedText>
            {{
              data.status
                ? $str('totara_webhook_status_enabled', 'totara_webhook')
                : $str('totara_webhook_status_disabled', 'totara_webhook')
            }}
          </InputSizedText>
        </FormRow>
        <FormRow
          v-slot="{ id, labelId }"
          :label="$str('signing_secret', 'totara_webhook')"
        >
          <InputGroup :aria-labelledby="labelId">
            <InputGroupInput
              :id="id"
              :monospace="true"
              readonly
              :type="showSecret ? 'text' : 'password'"
              :value="auth_config"
            />
            <InputGroupButton
              class="tui-totara_api-clients__inputGroupBtn"
              :aria-controls="id"
              :aria-label="showHideAriaLabel()"
              :text="showHideText()"
              @click="toggleSecretVisibility()"
            />
          </InputGroup>
          <ButtonIcon
            v-if="canRotate"
            ref="tagIcon"
            :aria-label="$str('rotate_signing_secret_aria', 'totara_webhook')"
            :text="$str('rotate_signing_secret', 'totara_webhook')"
            @click="openRotateModal()"
          >
            <Refresh size="100" />
          </ButtonIcon>
          <ConfirmationModal
            :open="rotateModalOpen"
            :title="$str('rotate_signing_secret_title', 'totara_webhook')"
            :confirm-button-text="
              $str('rotate_signing_secret_confirm', 'totara_webhook')
            "
            @confirm="confirmRotateSecret(data.id)"
            @cancel="rotateModalOpen = false"
          >
            <p>
              {{ $str('rotate_signing_secret_modal_1', 'totara_webhook') }}
            </p>
            <p>
              {{ $str('rotate_signing_secret_modal_2', 'totara_webhook') }}
            </p>
            <p>
              {{ $str('rotate_signing_secret_modal_3', 'totara_webhook') }}
            </p>
          </ConfirmationModal>
        </FormRow>
      </Form>
    </template>
  </LayoutOneColumn>
</template>

<script>
import { notify } from 'tui/notifications';
export default {
  props: {
    id: Number,
    data: Object,
    canRotate: Boolean,
    canManage: Boolean,
  },

  data() {
    return {
      webhook: {},
      auth_config: this.data.auth_config,
      showSecret: false,
      rotateModalOpen: false,
    };
  },

  apollo: {
    webhook: {
      query: webhookQuery,
      variables() {
        return {
          input: {
            id: this.data.id,
          },
        };
      },
      update({ totara_webhook: { item } }) {
        return item;
      },
    },
  },

  methods: {
    /**
     * @param {number} id
     */
    toggleSecretVisibility() {
      this.showSecret = !this.showSecret;
    },

    /**
     * @param {number} id
     */
    showHideText() {
      return this.showSecret
        ? this.$str('hide', 'totara_webhook')
        : this.$str('show', 'totara_webhook');
    },

    showHideAriaLabel() {
      return this.showSecret
        ? this.$str(
            'hide_signing_secret_aria',
            'totara_webhook',
            this.data.name
          )
        : this.$str(
            'show_signing_secret_aria',
            'totara_webhook',
            this.data.name
          );
    },

    openRotateModal() {
      this.rotateModalOpen = true;
    },

    async confirmRotateSecret(id) {
      if (!this.rotateModalOpen) {
        return;
      }

      try {
        const { data: result } = await this.$apollo.mutate({
          mutation: rotateWebhookAuthConfigMutation,
          variables: {
            reference: { id },
          },
          update: proxy => {
            const variables = {
              input: {
                id: id,
              },
            };
            const data = proxy.readQuery({
              query: webhookQuery,
              variables,
            });
            this.auth_config = data.totara_webhook.item.auth_config;
          },
        });
        if (result) {
          notify({
            type: 'success',
            message: this.$str('secret_rotated_success', 'totara_webhook'),
          });
          this.showSecret = false;
        }
      } finally {
        this.rotateModalOpen = false;
      }
    },
  },
};
</script>
