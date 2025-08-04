<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
  @module tool_diagnostic
-->

<template>
  <div class="tui-diagnosticProviders">
    <ModalPresenter
      :open="showSuccessModal"
      @request-close="showSuccessModal = false"
    >
      <DiagnosticSuccessModal
        :filename="downloadUrl"
        @ok="showSuccessModal = false"
      />
    </ModalPresenter>
    <div class="tui-diagnosticProviders__header">
      <PageHeading :title="$str('providers_page_heading', 'tool_diagnostic')" />
    </div>
    <div class="tui-diagnosticProviders__summary">
      {{ $str('tool_summary', 'tool_diagnostic') }}
      <h2 class="tui-diagnosticProvidersFeatures__heading">
        {{ $str('tool_summary_feature_heading', 'tool_diagnostic') }}
      </h2>
      <ul>
        <li>{{ $str('tool_summary_feature_features1', 'tool_diagnostic') }}</li>
        <li>{{ $str('tool_summary_feature_features2', 'tool_diagnostic') }}</li>
        <li>{{ $str('tool_summary_feature_features3', 'tool_diagnostic') }}</li>
        <li>{{ $str('tool_summary_feature_features4', 'tool_diagnostic') }}</li>
      </ul>
    </div>
    <Loader :loading="$apollo.loading" class="tui-diagnosticProviders__loader">
      <div class="tui-diagnosticProviders__wrapper">
        <Table
          v-if="!$apollo.loading"
          class="tui-diagnosticProviders__table"
          :data="providers"
        >
          <template v-slot:header-row>
            <HeaderCell size="3">
              {{ $str('column_name', 'tool_diagnostic') }}
            </HeaderCell>
            <HeaderCell size="7">
              {{ $str('column_description', 'tool_diagnostic') }}
            </HeaderCell>
            <HeaderCell size="1">
              {{ $str('column_include', 'tool_diagnostic') }}
            </HeaderCell>
          </template>
          <template v-slot:row="{ row: assessment }">
            <Cell
              size="3"
              :column-header="$str('column_name', 'tool_diagnostic')"
            >
              {{ assessment.name }}
            </Cell>
            <Cell
              size="7"
              :column-header="$str('column_description', 'tool_diagnostic')"
            >
              {{ assessment.description }}

              <HideShow
                v-if="assessment.whitelist"
                class="tui-diagnosticProviders__table-whitelist"
                :hide-content-text="$str('hide_whitelist', 'tool_diagnostic')"
                :show-content-text="$str('show_whitelist', 'tool_diagnostic')"
              >
                <template v-slot:trigger="{ controls, text, toggleContent }">
                  <Button
                    class="tui-diagnosticProviders__table-whitelistBtn"
                    :aria-controls="controls"
                    :styleclass="{ small: true, transparent: true }"
                    :text="text"
                    @click="toggleContent"
                  />
                </template>
                <template v-slot:content>
                  <div v-html="assessment.whitelist" />
                </template>
              </HideShow>
            </Cell>
            <Cell
              size="1"
              :column-header="$str('column_include', 'tool_diagnostic')"
            >
              <ToggleSwitch
                v-model:value="assessment.include"
                toggle-first
                :aria-label="$str('include_assessment', 'tool_diagnostic')"
              />
            </Cell>
          </template>
        </Table>
        <ButtonGroup
          v-if="!$apollo.loading"
          class="tui-diagnosticProviders__actions"
        >
          <Button
            :text="$str('run_diagnostics', 'tool_diagnostic')"
            :styleclass="{ primary: 'true' }"
            :disabled="!canSubmit"
            :loading="isRunning"
            @click="runDiagnostics()"
          />
        </ButtonGroup>
      </div>
      <div v-if="showDownloadButton">
        <FileCard
          class="tui-diagnosticProviders__fileCard"
          filename="diagnostic.zip"
          :file-size="downloadFilesize"
          :download-url="downloadUrl"
        />
      </div>
    </Loader>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Cell from 'tui/components/datatable/Cell';
import FileCard from 'tui/components/file/FileCard';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Loader from 'tui/components/loading/Loader';
import PageHeading from 'tui/components/layouts/PageHeading';
import Table from 'tui/components/datatable/Table';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import DiagnosticSuccessModal from 'tool_diagnostic/components/modal/DiagnosticSuccessModal';
import HideShow from 'tui/components/collapsible/HideShow';

// GraphQL
import getProviders from 'tool_diagnostic/graphql/get_providers';
import runDiagnostics from 'tool_diagnostic/graphql/run_diagnostics';

export default {
  components: {
    Button,
    ButtonGroup,
    Cell,
    FileCard,
    HeaderCell,
    Loader,
    PageHeading,
    Table,
    ToggleSwitch,
    ModalPresenter,
    DiagnosticSuccessModal,
    HideShow,
  },

  data() {
    return {
      providers: [],
      isRunning: false,
      showSuccessModal: false,
      showDownloadButton: false,
      downloadUrl: 'test',
      downloadFilesize: 0,
    };
  },

  apollo: {
    providers: {
      query: getProviders,
      update({ providers }) {
        return providers.map(assessment =>
          Object.assign({}, assessment, {
            include: !!assessment.enabled,
          })
        );
      },
    },
  },

  computed: {
    canSubmit() {
      return (
        this.providers.some(assessment => assessment.include) && !this.isRunning
      );
    },
  },

  methods: {
    async runDiagnostics() {
      this.isRunning = true;

      try {
        const {
          data: {
            tool_diagnostic_run_diagnostics: {
              download_url: downloadUrl,
              download_filesize: downloadFilesize,
            },
          },
        } = await this.$apollo.mutate({
          mutation: runDiagnostics,
          variables: {
            excluded_provider_ids: this.providers
              .filter(assessment => !assessment.include)
              .map(assessment => assessment.id),
          },
        });

        this.downloadUrl = downloadUrl;
        this.downloadFilesize = downloadFilesize;
        this.showSuccessModal = true;
        this.showDownloadButton = true;
      } finally {
        this.isRunning = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-diagnosticProviders {
  display: flex;
  flex-direction: column;
  gap: var(--gap-8);

  &__wrapper {
    display: flex;
    flex-direction: column;
    gap: var(--gap-8);
  }

  &__table {
    &-whitelist {
      background: none;
    }
  }

  &__loader {
    display: flex;
    flex-direction: column;
    gap: var(--gap-8);
  }
}
</style>
