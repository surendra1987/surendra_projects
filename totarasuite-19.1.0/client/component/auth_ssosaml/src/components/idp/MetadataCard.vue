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
  <aside class="tui-auth_ssosaml-metadataCard">
    <ButtonAria>
      <div class="tui-auth_ssosaml-metadataCard__header" @click="open = !open">
        <component
          :is="open ? 'HideIcon' : 'ShowIcon'"
          class="tui-auth_ssosaml-metadataCard__icon"
        />
        <div class="tui-auth_ssosaml-metadataCard__heading">
          {{ $str('metadata_panel_title', 'auth_ssosaml') }}
        </div>
      </div>
    </ButtonAria>
    <div v-show="open" class="tui-auth_ssosaml-metadataCard__content">
      <div class="tui-auth_ssosaml-metadataCard__info">
        <InfoIcon class="tui-auth_ssosaml-metadataCard__infoIcon" />
        {{ $str('metadata_panel_info', 'auth_ssosaml') }}
      </div>
      <div class="tui-auth_ssosaml-metadataCard__cols">
        <div class="tui-auth_ssosaml-metadataCard__main">
          <div class="tui-auth_ssosaml-metadataCard__kv">
            <label
              :id="$id('sp_metadata_url')"
              class="tui-auth_ssosaml-metadataCard__label"
            >
              {{ $str('service_provider_metadata', 'auth_ssosaml') }}
            </label>
            <CopyField
              :value="metadata.sp_metadata_url"
              :aria-labelledby="$id('sp_metadata_url')"
              char-length="full"
            />
            <div>
              <a
                :href="$url(metadata.sp_metadata_url, { download: 1 })"
                download
              >
                <DownloadIcon />
                {{ $str('download_service_provider_metadata', 'auth_ssosaml') }}
              </a>
            </div>
          </div>
          <div class="tui-auth_ssosaml-metadataCard__kv">
            <label
              :id="$id('acs_url')"
              class="tui-auth_ssosaml-metadataCard__label"
            >
              {{ $str('service_provider_acs_url_long', 'auth_ssosaml') }}
            </label>
            <CopyField
              :value="metadata.acs_url"
              :aria-labelledby="$id('acs_url')"
              char-length="full"
            />
          </div>
          <div class="tui-auth_ssosaml-metadataCard__kv">
            <label
              :id="$id('entity_id')"
              class="tui-auth_ssosaml-metadataCard__label"
            >
              {{ $str('service_provider_entity_id', 'auth_ssosaml') }}
            </label>
            <CopyField
              :value="metadata.entity_id"
              :aria-labelledby="$id('entity_id')"
              char-length="full"
            />
          </div>
          <div class="tui-auth_ssosaml-metadataCard__kv">
            <label
              :id="$id('slo_url')"
              class="tui-auth_ssosaml-metadataCard__label"
            >
              {{ $str('slo_url', 'auth_ssosaml') }}
            </label>
            <CopyField
              :value="metadata.slo_url"
              :aria-labelledby="$id('slo_url')"
              char-length="full"
            />
          </div>
        </div>
        <div class="tui-auth_ssosaml-metadataCard__side">
          <div class="tui-auth_ssosaml-metadataCard__kv">
            <label class="tui-auth_ssosaml-metadataCard__label">
              {{ $str('certificate', 'auth_ssosaml') }}
            </label>
            <div>
              <a
                :href="$url('/auth/ssosaml/certificate.php', { idp: idpId })"
                download
              >
                <DownloadIcon />
                {{ $str('download_certificate', 'auth_ssosaml') }}
              </a>
            </div>
            <div>
              <ButtonIcon
                :styleclass="{ transparentNoPadding: true }"
                :text="$str('regenerate_certificate', 'auth_ssosaml')"
                :aria-label="$str('regenerate_certificate', 'auth_ssosaml')"
                @click="showRegenerate"
              >
                <RefreshIcon />
              </ButtonIcon>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmationModal
      :open="regenerateOpen"
      :title="$str('regenerate_certificate', 'auth_ssosaml')"
      :confirm-button-text="$str('regenerate_certificate', 'auth_ssosaml')"
      :loading="regenerating"
      @confirm="handleRegenerateConfirm"
      @cancel="handleRegenerateCancel"
    >
      {{ $str('regenerate_certificate_confirm_message', 'auth_ssosaml') }}
    </ConfirmationModal>
  </aside>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonAria from 'tui/components/buttons/ButtonAria';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import DownloadIcon from 'tui/components/icons/Download';
import HideIcon from 'tui/components/icons/Hide';
import InfoIcon from 'tui/components/icons/Info';
import RefreshIcon from 'tui/components/icons/Refresh';
import ShowIcon from 'tui/components/icons/Show';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import CopyField from 'auth_ssosaml/components/idp/CopyField';
import regenerateMutation from 'auth_ssosaml/graphql/regenerate_idp_certificates';
import { notify } from 'tui/notifications';

export default {
  components: {
    Button,
    ButtonAria,
    ButtonIcon,
    DownloadIcon,
    HideIcon,
    InfoIcon,
    RefreshIcon,
    ShowIcon,
    ConfirmationModal,
    CopyField,
  },

  props: {
    idpId: String,
    metadata: Object,
    initiallyOpen: Boolean,
  },

  data() {
    return {
      open: this.initiallyOpen,
      regenerateOpen: false,
      regenerating: false,
    };
  },

  methods: {
    showRegenerate() {
      this.regenerateOpen = true;
    },

    async handleRegenerateConfirm() {
      this.regenerating = true;
      try {
        await this.$apollo.mutate({
          mutation: regenerateMutation,
          variables: { input: { id: this.idpId } },
        });

        notify({
          message: this.$str(
            'regenerate_certificate_success_message',
            'auth_ssosaml'
          ),
        });
      } finally {
        this.regenerateOpen = false;
        this.regenerating = false;
      }
    },

    handleRegenerateCancel() {
      this.regenerateOpen = false;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-metadataCard {
  background-color: var(--color-neutral-2);
  border: 1px solid var(--card-border-color);
  border-radius: var(--card-border-radius);

  &__header {
    display: flex;
    align-items: center;
    padding: var(--gap-4);
  }

  &__icon {
    flex: 0 0 auto;
  }

  &__heading {
    @include font(h4);
    flex-grow: 1;
    margin: 0 var(--gap-2) 0 var(--gap-2);
  }

  &__content {
    padding: 0 var(--gap-4) var(--gap-4) var(--gap-4);
  }

  &__info {
    padding-top: var(--gap-4);
    border-top: 1px solid var(--color-border);
  }

  &__infoIcon {
    margin-right: var(--gap-1);
  }

  &__cols {
    margin-top: var(--gap-4);
  }

  &__main {
    @include tui-stack-vertical(var(--gap-4));
  }

  &__side {
    margin-top: var(--gap-4);
  }

  &__kv {
    @include tui-stack-vertical(var(--gap-2));
  }

  &__label {
    @include font(body-sm, var(--label-weight));
    margin: 0;
    padding: 0;
  }

  @media (min-width: $tui-screen-sm) {
    &__cols {
      display: grid;
      grid-template-columns: 70% 30%;
    }

    &__main {
      padding-right: var(--gap-4);
    }

    &__side {
      margin: 0;
      padding-left: var(--gap-4);
      border-left: 1px solid var(--color-border);
    }
  }
}
</style>
