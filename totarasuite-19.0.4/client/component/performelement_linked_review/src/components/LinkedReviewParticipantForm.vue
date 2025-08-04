<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @author Mark Metcalfe <mark.metcalfe@totaralearning.com>
  @module performelement_linked_review
-->

<template>
  <Loader v-if="$apollo.loading" :loading="$apollo.loading" />
  <div v-else class="tui-linkedReviewParticipantForm">
    <!-- Overview of who selected the content -->
    <template v-if="selectedContent.items && selectedContent.items.length">
      <div class="tui-linkedReviewParticipantForm__selectedBy">
        <template v-if="showSelectedBy">
          {{
            $str('items_selected_by', 'mod_perform', {
              date: selectedContent.items[0].created_at_date,
              user: selectedContent.items[0].selector.fullname,
            })
          }}
        </template>

        <template v-else-if="showItemNotSelected">
          {{ $str('no_items_selected', 'mod_perform') }}
        </template>
      </div>

      <div class="tui-linkedReviewParticipantForm__items">
        <!-- Iterate thought selected content -->
        <div
          v-for="item in selectedContent.items"
          :key="item.id"
          ref="selected-content-item"
          class="tui-linkedReviewParticipantForm__item"
        >
          <!-- Card summary of selected content item-->
          <Card
            class="tui-linkedReviewParticipantForm__item-card"
            :no-border="true"
          >
            <div class="tui-linkedReviewParticipantForm__item-cardContent">
              <component
                :is="getContentComponent(element)"
                v-if="hasContentComponent"
                :content="getContent(item.content)"
                :created-at="item.created_at_date"
                :element="element"
                :from-print="fromPrint"
                :is-external-participant="isExternalParticipant"
                :participant-instance-id="participantInstanceId"
                :settings="contentSettings"
                :subject-user="subjectUser"
              />
            </div>
            <div
              v-if="!fromPrint && item.can_remove && !hasUnsavedChanges"
              class="tui-linkedReviewParticipantForm__item-cardActions"
            >
              <CloseButton
                :size="300"
                :aria-label="$str('remove', 'core')"
                @click="removeItem(item.id)"
              />
            </div>
          </Card>

          <!-- Display for each respondable question within the group -->
          <div class="tui-linkedReviewParticipantForm__questions">
            <div
              v-for="childElement in element.children"
              :key="childElement.id"
            >
              <ResponseHeader
                v-if="childElement.title"
                :id="$id('title')"
                :has-printed-to-do-icon="
                  hasPrintedToDoIcon && childElement.is_respondable
                "
                :is-respondable="childElement.is_respondable"
                :required="childElement.is_required"
                :sub-element="true"
                :title="childElement.title"
              />

              <div class="tui-linkedReviewParticipantForm__questions-content">
                <ContentChildElementFormScope
                  :content-item="item"
                  :path="path"
                  :section-element="sectionElement"
                  :child-element="childElement"
                >
                  <!-- Load child component here -->
                  <component
                    :is="getFormComponent(childElement)"
                    v-if="hasFormComponent"
                    v-bind="$attrs"
                    :element="childElement"
                    :element-components="childElement.element_plugin"
                    :participant-instance-id="participantInstanceId"
                    :from-print="fromPrint"
                    :path="'response_data'"
                    :section-element="
                      sectionElementWithResponseGroups(item.id, childElement)
                    "
                    :active-section-is-closed="activeSectionIsClosed"
                    :anonymous-responses="anonymousResponses"
                    :error="error"
                    :group-id="checkboxGroupId"
                    :is-draft="isDraft"
                    :is-external-participant="isExternalParticipant"
                    :participant-can-answer="participantCanAnswer"
                    :subject-instance-id="subjectInstanceId"
                    :show-other-response="showOtherResponse"
                    :view-only="viewOnly"
                    :token="token"
                    :extra-data="{ content: getContent(item.content) }"
                  />
                </ContentChildElementFormScope>
              </div>
            </div>
          </div>

          <!-- Display of additional information / functionality after the question - optional -->
          <component
            :is="getFooterComponent(element)"
            :can-update="!hasUnsavedChanges"
            :content="getContent(item.content)"
            :element-data="element.data"
            :participant-instance-id="participantInstanceId"
            :section-element-id="sectionElement.id"
            :subject-user="subjectUser"
            :from-print="fromPrint"
            @update="refetch"
            @show-banner="$emit('show-banner', $event)"
          />
        </div>
      </div>
    </template>

    <div
      v-if="!hasPickerComponent"
      class="tui-linkedReviewParticipantForm__noContent"
    >
      {{ $str('content_cannot_be_added', 'mod_perform') }}
    </div>

    <div
      v-else-if="activeSectionIsClosed"
      class="tui-linkedReviewParticipantForm__noContent"
    >
      {{ $str('content_cannot_be_added_because_closed', 'mod_perform') }}
    </div>

    <!-- Add item button and content -->
    <component
      :is="getPickerComponent(element)"
      v-else-if="participantInstanceId"
      :is-draft="isDraft"
      :is-external-participant="isExternalParticipant"
      :can-show-adder="canSelectContent"
      :core-relationship="element.data.selection_relationships_display"
      :existing-item-ids="
        selectedContent.items.map(item => item.unique_id || item.content_id)
      "
      :participant-instance-id="participantInstanceId"
      :preview-component="getContentComponent(element)"
      :required="element.is_required"
      :section-element-id="sectionElement.id"
      :settings="contentSettings"
      :subject-user="subjectUser"
      :user-id="userId"
      :content-type="element.data.content_type"
      @update="refetch"
      @unsaved-plugin-change="handleUnsavedChanges"
    />

    <!-- Remove confirmation modal -->
    <ConfirmationModal
      :confirm-button-text="$str('remove', 'core')"
      :loading="removingItem"
      :open="showRemoveItemModal"
      :title="
        $str(
          'modal_remove_review_item_title',
          'mod_perform',
          element.data.content_type_display_generic
        )
      "
      @confirm="confirmRemoveItem"
      @cancel="cancelRemoveItem"
    >
      <p>
        {{
          $str(
            'modal_remove_review_item_message',
            'mod_perform',
            element.data.content_type_display_generic
          )
        }}
      </p>
    </ConfirmationModal>
  </div>
</template>

<script>
import Card from 'tui/components/card/Card';
import CloseButton from 'tui/components/buttons/CloseIcon';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Loader from 'tui/components/loading/Loader';
import ResponseHeader from 'mod_perform/components/element/ElementParticipantResponseHeader';
import selectedContentItemsQuery from 'performelement_linked_review/graphql/content_items';
import selectedContentItemsQueryExternal from 'performelement_linked_review/graphql/content_items_nosession';
import ContentChildElementFormScope from 'performelement_linked_review/components/ContentChildElementFormScope';
import { notify } from 'tui/notifications';

// GraphQL
import RemoveItemMutation from 'performelement_linked_review/graphql/remove_linked_review_content';

export default {
  components: {
    Card,
    CloseButton,
    ConfirmationModal,
    ContentChildElementFormScope,
    Loader,
    ResponseHeader,
  },

  props: {
    activeSectionIsClosed: Boolean,
    anonymousResponses: Boolean,
    checkboxGroupId: String,
    coreRelationshipId: [String, Number],
    element: Object,
    error: String,
    fromPrint: Boolean,
    hasPrintedToDoIcon: Boolean,
    isDraft: Boolean,
    isExternalParticipant: Boolean,
    participantCanAnswer: Boolean,
    participantInstanceId: {
      type: [String, Number],
      required: false,
    },
    path: {
      type: [String, Array],
      default: '',
    },
    sectionElement: Object,
    showOtherResponse: Boolean,
    subjectUser: {
      required: true,
      type: Object,
    },
    subjectInstanceId: {
      type: Number,
      required: true,
    },
    token: String,
    viewOnly: Boolean,
    currentUserId: Number,
  },

  emits: [
    'refetch-sections',
    'remove-from-form-data',
    'show-banner',
    'unsaved-plugin-change',
  ],

  data() {
    return {
      contentSettings: this.element.data.content_type_settings,
      groupId: this.$id('label'),
      hasUnsavedChanges: false,
      itemToRemove: null,
      removingItem: false,
      selectedContent: [],
      showRemoveItemModal: false,
    };
  },

  computed: {
    userId() {
      return parseInt(this.subjectUser.id);
    },

    canSelectContent() {
      return (
        this.coreRelationshipId == this.element.data.selection_relationships &&
        !this.fromPrint &&
        !this.isExternalParticipant &&
        (!this.selectedContent || this.selectedContent.can_add)
      );
    },

    /**
     * Has a content component available
     *
     * @return {Boolean}
     */
    hasContentComponent() {
      return (
        this.element.data.components &&
        this.element.data.components.participant_content
      );
    },

    /**
     * Has a form component available
     *
     * @return {Boolean}
     */
    hasFormComponent() {
      return this.element.element_plugin.participant_form_component;
    },

    /**
     * Has a picker component available
     *
     * @return {Boolean}
     */
    hasPickerComponent() {
      return (
        this.element.data.components &&
        this.element.data.components.content_picker
      );
    },

    showSelectedBy() {
      return (
        this.coreRelationshipId ===
          this.element.data.selection_relationships[0] &&
        this.selectedContent.items[0] &&
        this.selectedContent.items[0].selector &&
        this.currentUserId != this.selectedContent.items[0].selector.id
      );
    },

    showItemNotSelected() {
      return this.viewOnly && this.selectedContent.items[0] === undefined;
    },
  },

  methods: {
    /**
     * Close the remove item modal
     */
    cancelRemoveItem() {
      this.showRemoveItemModal = false;
      this.itemToRemove = null;
    },

    /**
     * Call the remove item mutation
     */
    async confirmRemoveItem() {
      this.removingItem = true;
      try {
        const result = await this.$apollo.mutate({
          mutation: RemoveItemMutation,
          variables: {
            input: {
              id: this.itemToRemove,
            },
          },
        });

        if (
          result.data.performelement_linked_review_remove_linked_review_content
            .success
        ) {
          // Success
          notify({
            message: this.$str(
              'modal_remove_review_item_success',
              'mod_perform',
              this.element.data.content_type_display_generic
            ),
            type: 'success',
          });

          // Need to specify the section element,
          // and provide the path in the form data to the item we just removed so that it can be deleted
          this.$emit('remove-from-form-data', this.sectionElement.id, [
            'response',
            'contentItemResponses',
            this.itemToRemove,
          ]);

          this.refetch();
        } else {
          // Expected error
          notify({
            message:
              result.data
                .performelement_linked_review_remove_linked_review_content
                .error,
            type: 'error',
          });
        }
      } catch (e) {
        // Unexpected error
        notify({
          message: this.$str(
            'modal_remove_review_item_error',
            'mod_perform',
            this.element.data.content_type_display_generic
          ),
          type: 'error',
        });
      } finally {
        this.showRemoveItemModal = false;
        this.itemToRemove = null;
        this.removingItem = false;
      }
    },

    /**
     * Get dynamic component
     *
     * @param {Object} element
     * @return {function}
     */
    getContentComponent(element) {
      if (!this.hasContentComponent) {
        return null;
      }
      return tui.asyncComponent(element.data.components.participant_content);
    },

    /**
     * Get dynamic component
     *
     * @return {function}
     */
    getFooterComponent(element) {
      return element.data.components.participant_content_footer
        ? tui.asyncComponent(element.data.components.participant_content_footer)
        : null;
    },

    /**
     * Get dynamic component
     *
     * @param {Object} element
     * @return {function}
     */
    getFormComponent(element) {
      return tui.asyncComponent(
        element.element_plugin.participant_form_component
      );
    },

    /**
     * Get dynamic component
     *
     * @param {Object} element
     * @return {function}
     */
    getPickerComponent(element) {
      return tui.asyncComponent(element.data.components.content_picker);
    },

    /**
     * Checks if we have unsaved changes (currently adding items) and emits the event
     *
     * @param {Object} event
     */
    handleUnsavedChanges(event) {
      this.hasUnsavedChanges = event.hasChanges;
      this.$emit('unsaved-plugin-change', event);
    },

    /**
     * Displays the remove item modal
     */
    removeItem(id) {
      this.itemToRemove = id;
      this.showRemoveItemModal = true;
    },

    /**
     * Selects the right child element to the sectionElement object.
     * Selects the response_data, response_data_raw & response_data_formatted_lines values for the
     * specified content child element.
     *
     * @param {Number} contentItemId
     * @param {Object} childElement
     * @return {Object}
     */
    sectionElementWithResponseGroups(contentItemId, childElement) {
      return Object.assign({}, this.sectionElement, {
        element: childElement,
        response_data: this.childElementResponseData(
          contentItemId,
          childElement.id
        ),
        response_data_raw: this.childElementResponseDataRaw(
          contentItemId,
          childElement.id
        ),
        response_data_formatted_lines: this.childElementResponseDataFormattedLines(
          contentItemId,
          childElement.id
        ),
        other_responder_groups: this.childElementOtherResponderGroups(
          contentItemId,
          childElement.id
        ),
      });
    },

    /**
     * Get the child element response data specific to the content item id.
     *
     * @param {Number} contentItemId
     * @param {Number} childElementId
     */
    childElementResponseData(contentItemId, childElementId) {
      if (!this.sectionElement.response_data) {
        return null;
      }

      return this.getContentChildElementValue(
        this.sectionElement.response_data,
        contentItemId,
        childElementId
      );
    },

    /**
     * Get the child element raw response data specific to the content item id.
     *
     * @param {Number} contentItemId
     * @param {Number} childElementId
     */
    childElementResponseDataRaw(contentItemId, childElementId) {
      if (!this.sectionElement.response_data_raw) {
        return null;
      }

      return this.getContentChildElementValue(
        this.sectionElement.response_data_raw,
        contentItemId,
        childElementId
      );
    },

    /**
     * Get the child element response data formatted lines specific to the content item id.
     *
     * @param {Number} contentItemId
     * @param {Number} childElementId
     * @return Array
     */
    childElementResponseDataFormattedLines(contentItemId, childElementId) {
      if (this.sectionElement.response_data_formatted_lines.length < 1) {
        return [];
      }
      let formattedLines = JSON.parse(
        this.sectionElement.response_data_formatted_lines[0]
      );

      return this.getContentChildElementValue(
        formattedLines,
        contentItemId,
        childElementId
      );
    },

    /**
     * Get the child element other responder groups specific to the content item id.
     *
     * @param {Number} contentItemId
     * @param {Number} childElementId
     * @return Array
     */
    childElementOtherResponderGroups(contentItemId, childElementId) {
      return this.sectionElement.other_responder_groups.map(responseGroup => {
        let contentChildElementResponses = responseGroup.responses.map(
          participantResponse => {
            let responseData = null;
            if (participantResponse.response_data !== null) {
              responseData = this.getContentChildElementValue(
                JSON.parse(participantResponse.response_data),
                contentItemId,
                childElementId
              );
            }

            let formattedLines = [];
            if (participantResponse.response_data_formatted_lines.length > 0) {
              let responseDataFormattedLines = JSON.parse(
                participantResponse.response_data_formatted_lines[0]
              );

              formattedLines = this.getContentChildElementValue(
                responseDataFormattedLines,
                contentItemId,
                childElementId
              );
            }

            return Object.assign({}, participantResponse, {
              response_data: responseData,
              response_data_formatted_lines: formattedLines,
            });
          }
        );

        return Object.assign({}, responseGroup, {
          responses: contentChildElementResponses,
        });
      });
    },

    /**
     * Get the content's child element value from the response object.
     *
     * @param {Object} response
     * @param {Number} contentItemId
     * @param {Number} childElementId
     *
     * @return Object|String|Array
     */
    getContentChildElementValue(response, contentItemId, childElementId) {
      let repeatingItemIdentifier = this.sectionElement.element.element_plugin
        .child_element_config.repeating_item_identifier;
      let childElementResponsesIdentifier = this.sectionElement.element
        .element_plugin.child_element_config.child_element_responses_identifier;

      let childElementResponse =
        response[repeatingItemIdentifier][contentItemId]?.[
          childElementResponsesIdentifier
        ]?.[childElementId];

      if (!childElementResponse?.response_data) {
        return null;
      }

      return childElementResponse.response_data;
    },

    /**
     * Fetch the content display data after confirming the selection.
     *
     */
    refetch(canNotSelectContentMessage) {
      if (canNotSelectContentMessage) {
        this.$emit('show-banner', canNotSelectContentMessage);
      }
      this.loading = true;
      this.$apollo.queries.selectedContent.refetch().then(() => {
        let elements = this.$refs['selected-content-item'];
        if (elements.length) {
          elements[0].scrollIntoView();
        }

        /* Refetch sections data to check if the review content has added
          required questions to the section. */
        this.$emit('refetch-sections');
      });
    },

    /**
     * @param {string} content
     */
    getContent(content) {
      let result = content ? JSON.parse(content) : {};

      return result !== null ? result : {};
    },
  },

  apollo: {
    selectedContent: {
      query() {
        return this.isExternalParticipant
          ? selectedContentItemsQueryExternal
          : selectedContentItemsQuery;
      },
      variables() {
        return {
          input: {
            subject_instance_id: this.subjectInstanceId,
            participant_section_id: this.element.participantSectionId
              ? this.element.participantSectionId
              : null,
            section_element_id: this.sectionElement.id,
            token: this.token ? this.token : null,
          },
        };
      },
      update({ performelement_linked_review_content_items }) {
        return performelement_linked_review_content_items;
      },
    },
  },
};
</script>

<style lang="scss">
.tui-linkedReviewParticipantForm {
  & > * + * {
    margin-top: var(--gap-4);
  }

  &__noContent {
    font-size: font-size-px(15);
    font-style: italic;
  }

  &__items {
    & > * + * {
      margin-top: var(--gap-8);
    }
  }

  &__item {
    & > * + * {
      margin-top: var(--gap-4);
    }

    &-card {
      max-width: 1200px;
      background: var(--color-neutral-3);
    }

    &-cardContent {
      width: 100%;
      padding: var(--gap-4);
    }

    &-cardActions {
      display: flex;
      align-items: flex-start;
      width: var(--gap-9);
      margin-top: var(--gap-2);
    }
  }

  &__questions {
    & > * + * {
      margin-top: var(--gap-8);
    }

    &-content {
      margin-top: var(--gap-8);

      & > * + * {
        margin-top: var(--gap-8);
      }
    }
  }
}
</style>
