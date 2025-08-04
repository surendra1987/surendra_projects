<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
  @module totara_engage
-->

<template>
  <span>
    <Modal
      :size="size"
      :aria-labelledby="$id('title')"
      :dismissable="dismissable"
    >
      <ModalContent
        class="tui-engageContributeModal"
        :close-button="false"
        :title="getTitle"
      >
        <template v-slot:header-buttons>
          <ModalHeaderButton
            v-if="expandable"
            v-show="!hideTabs"
            :title="resizeAriaLabel"
            :aria-label="resizeAriaLabel"
            @click="resize()"
          >
            <SizeContractIcon v-if="expanded" :size="200" state="dimmed" />
            <SizeExpandIcon v-else :size="200" state="dimmed" />
          </ModalHeaderButton>
        </template>
        <div class="tui-engageContributeModal__content">
          <template v-if="adderTextHtml && !hideTabs">
            <div class="tui-engageContributeModal__adderContainer">
              <span @click="handleAdderClick" v-html="adderTextHtml" />
            </div>
            <div
              v-if="modals.length > 0"
              class="tui-engageContributeModal__orCreateText"
            >
              {{ $str('or_create_a_new', 'totara_engage') }}
            </div>
          </template>
          <Tabs
            v-if="!$apollo.loading && modals.length > 0"
            v-show="displayTab"
            :selected="selectedTab"
            :small-tabs="true"
            :controlled="true"
            class="tui-engageContributeModal__tabs"
            @input="changeTabRequest"
          >
            <Tab
              v-for="modal in modals"
              :id="modal.id"
              :key="modal.id"
              :name="modal.label"
              :disabled="disabledId === modal.id"
            />
          </Tabs>

          <div
            v-if="!$apollo.loading"
            class="tui-engageContributeModal__componentContent"
          >
            <!-- This is where the content of selectedTab is -->
            <component
              :is="selectedTabComponent"
              :container="container"
              :show-notification="showNotification"
              :can-share="getCanShare(selectedTab)"
              @change-title="stage = $event"
              @done="$emit('done', $event)"
              @cancel="$emit('request-close')"
              @unsaved-changes="hasUnsavedChanges = true"
            />
          </div>
        </div>
      </ModalContent>
    </Modal>
    <ConfirmationModal
      :open="unsavedChangesModalOpen"
      :title="$str('unsaved_changes_title', 'totara_engage')"
      :confirm-button-text="$str('button_continue', 'totara_engage')"
      @confirm="changeTabConfirm"
      @cancel="unsavedChangesModalOpen = false"
    >
      <p>{{ $str('unsaved_changes_message', 'totara_engage') }}</p>
    </ConfirmationModal>
  </span>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ModalHeaderButton from 'tui/components/modal/ModalHeaderButton';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import SizeContractIcon from 'tui/components/icons/SizeContract';
import SizeExpandIcon from 'tui/components/icons/SizeExpand';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import tui from 'tui/tui';

// GraphQL
import getModals from 'totara_engage/graphql/modals';

// Mixins
import ContainerMixin from 'totara_engage/mixins/container_mixin';

export default {
  components: {
    Button,
    ModalHeaderButton,
    ButtonIcon,
    ConfirmationModal,
    Modal,
    ModalContent,
    SizeContractIcon,
    SizeExpandIcon,
    Tab,
    Tabs,
  },

  mixins: [ContainerMixin],

  props: {
    excludeModals: Array,
    adderText: String,
    showNotification: {
      type: Boolean,
    },
    modalTitle: String,
    showTab: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['adder-open', 'done', 'request-close', 'course-adder-open'],

  apollo: {
    modals: {
      query: getModals,
      variables() {
        return {
          exclude: this.getExcludeModals(),
        };
      },
      result({ data: { modals } }) {
        this.modals = modals.slice();
        for (const modal of this.modals) {
          this.canShares[modal.id] = modal.can_share;

          if (this.selectedTab == null) {
            this.selectedTab = modal.id;
          }
        }
        return this.modals;
      },
    },
  },

  data() {
    return {
      compress: this.$str('compress', 'totara_engage'),
      expand: this.$str('expand', 'totara_engage'),
      selectedTab: null,
      size: 'large',
      expanded: false,
      dismissable: {
        overlayClose: false,
        esc: true,
        backdropClick: false,
      },
      modals: [],
      hideTabs: false,
      disabledId: 0,
      stage: 0,
      hasUnsavedChanges: false,
      unsavedChangesModalOpen: false,
      requestedTab: null,
      canShares: [],
      showAdder: false,
      loading: false,
    };
  },

  computed: {
    expandable() {
      if (this.modals.length !== 0) {
        let modal = this.getSelectedModal();
        return modal.expandable;
      }
      return false;
    },

    resizeIcon() {
      return this.expanded ? 'compress' : 'expand';
    },

    resizeAriaLabel() {
      return this.expanded ? this.compress : this.expand;
    },

    getTitle() {
      if (this.stage === 1) {
        return this.$str('accesssettings', 'totara_engage');
      }
      return this.modalTitle
        ? this.modalTitle
        : this.$str('contribute', 'totara_engage');
    },

    adderTextHtml() {
      if (!this.adderText) {
        return null;
      }

      // Ideally we'd insert a Vue component here, e.g. with PLATFORM-44.
      // This is a workaround for now.
      // The data attribute is detected in handleAdderClick.

      return this.adderText
        .replace(
          /<AddResourcesLink>(.*?)<\/AddResourcesLink>/,
          '<a href="javascript:;" data-action="add-resources">$1</a>'
        )
        .replace(
          /<AddCoursesLink>(.*?)<\/AddCoursesLink>/,
          '<a href="javascript:;" data-action="add-courses">$1</a>'
        );
    },

    selectedTabComponent() {
      const component = this.modals.find(x => x.id === this.selectedTab)
        ?.component;
      return component ? tui.asyncComponent(component) : null;
    },

    displayTab() {
      return !this.hideTabs && this.showTab;
    },
  },

  watch: {
    selectedTab() {
      if (this.expanded && !this.expandable) {
        this.size = 'large';
        this.expanded = false;
      }
    },
    stage() {
      this.hideTabs = this.stage !== 0;
    },
  },

  methods: {
    resize() {
      this.expanded = !this.expanded;
      if (this.expanded) {
        this.size = 'sheet';
      } else {
        this.size = 'large';
      }
    },
    getSelectedModal() {
      const that = this;
      return that.modals.find(function(modal) {
        return modal.id === that.selectedTab;
      });
    },

    getExcludeModals() {
      if (this.container === null || this.container === undefined) {
        return this.excludeModals;
      }

      const { component, showModal } = this.container;
      if (showModal) {
        return [];
      }

      return [component];
    },

    /**
     * User wants to change the tab.
     *
     * @param {String} id
     */
    changeTabRequest(id) {
      if (this.hasUnsavedChanges) {
        this.unsavedChangesModalOpen = true;
        this.requestedTab = id;
      } else {
        this.selectedTab = id;
      }
    },

    /**
     * User has confirmed they want to change the tab.
     */
    changeTabConfirm() {
      this.hasUnsavedChanges = false;
      this.unsavedChangesModalOpen = false;
      this.selectedTab = this.requestedTab;
    },

    /**
     * Can canShare for the specific component.
     *
     * @param {String} selectedTab
     */
    getCanShare(selectedTab) {
      return this.canShares[selectedTab];
    },

    handleAdderClick(e) {
      const action = e.target.getAttribute('data-action');
      if (action === 'add-resources') {
        this.$emit('adder-open');
      } else if (action === 'add-courses') {
        this.$emit('course-adder-open');
      }
    },
  },
};
</script>

<style lang="scss">
.tui-engageContributeModal {
  position: relative;

  &__content {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-height: rem-px(650);
  }

  &__adderContainer {
    margin-bottom: var(--gap-4);
    padding: var(--gap-2) var(--gap-3);
    background-color: var(--color-neutral-2);
    border: var(--border-width-thin) solid var(--color-neutral-4);
    border-radius: var(--border-radius-normal);
  }

  &__orCreateText {
    margin-bottom: var(--gap-4);
    font-weight: 600;
  }

  &__tabs {
    display: flex;
    flex-direction: column;
    padding: 0;
    padding-bottom: var(--gap-6);
    .tui-tabs {
      &__panels {
        display: flex;
        flex-direction: column;
      }
    }
  }

  &__componentContent {
    position: relative;
    display: flex;
    flex-basis: 0;
    flex-direction: column;
    flex-grow: 1;
    min-height: 0;
  }
}
</style>
