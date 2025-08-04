<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Jack Humphrey <jack.humphrey@totara.com>
  @module totara_program
-->
<template>
  <div class="tui-totara_program-programHeader">
    <div class="tui-totara_program-programHeader__wrapper">
      <h1 class="tui-totara_program-programHeader__title">
        {{ programName }}
        <Lozenge
          v-if="dueStatus !== STATUS.NONE"
          class="tui-totara_program-programHeader__status"
          :type="dueStatus === STATUS.DUE_SOON ? 'warning' : 'alert'"
          :text="dueStatusString"
        />
      </h1>
      <EnrolModal
        :show-enrol-button="showEnrolButton"
        :program-name="programName"
        :program-id="programId"
        :is-enrolled="userAssigned"
        :applicable-assignments="applicableAssignments"
        :can-edit="canEdit"
        :is-certification="isCertification"
        :due-date="dueDateString"
        :date-assigned="dateAssignedString"
        :withdraw-string="withdrawString"
        :type-string="typeString"
      />
    </div>
    <Responsive
      :breakpoints="[
        { name: 'small', boundaries: [0, 992] },
        { name: null, boundaries: [992, 992] },
      ]"
      @responsive-resize="resize"
    >
      <Grid :direction="stacked ? 'vertical' : 'horizontal'">
        <GridItem :units="9">
          <div class="tui-totara_program-programHeader__info">
            <img :src="image" class="tui-totara_program-programHeader__image" />
            <div>
              <p v-if="statusMessage">
                {{ statusMessage }}
              </p>
              <div
                class="tui-totara_program-programHeader__summary"
                v-html="summary"
              />
            </div>
          </div>
        </GridItem>
        <GridItem v-if="userAssigned" :units="3">
          <div class="tui-totara_program-programHeader__progress">
            <p v-if="criteria" v-html="criteria" />
            <p v-if="dateAssignedString">
              {{ $str('dateassigned_a', 'totara_program', dateAssignedString) }}
            </p>
            <p>
              <span
                class="tui-totara_program-programHeader__dueDate"
                :class="{
                  'tui-totara_program-programHeader__dueDate--overdue':
                    dueStatus === STATUS.OVERDUE ||
                    dueStatus === STATUS.DUE_TODAY,
                }"
              >
                {{ $str('duedate_a', 'totara_program', dueDateString) }}</span
              >
              <RequestExtension
                v-if="canRequestExtension"
                :program-id="programId"
                :current-due="dueDate"
                :extension-requested="extensionRequested"
              />
            </p>

            <p class="tui-totara_program-programHeader__programStatus">
              <span class="tui-totara_program-programHeader__progress-label">
                {{ $str('program_status_label', 'totara_program') }}</span
              >
              <Progress :value="programStatus" />
            </p>
          </div>
        </GridItem>
      </Grid>
    </Responsive>
  </div>
</template>
<script>
import Lozenge from 'tui/components/lozenge/Lozenge';
import Responsive from 'tui/components/responsive/Responsive';
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import Progress from 'tui/components/progress/Progress';
import RequestExtension from 'totara_program/components/RequestExtension';
import EnrolModal from 'totara_program/components/learner/self_enrolment/EnrolModal';

export default {
  components: {
    Lozenge,
    Responsive,
    Grid,
    GridItem,
    Progress,
    RequestExtension,
    EnrolModal,
  },

  props: {
    programId: [Number, String],
    dueDate: String,
    dueDateString: String,
    programName: {
      type: String,
      required: true,
    },
    canRequestExtension: Boolean,
    extensionRequested: Boolean,
    summary: {
      type: String,
      required: true,
    },
    image: {
      type: String,
      required: true,
    },
    criteria: String,
    dateAssignedString: String,
    programStatus: Number,
    userAssigned: {
      type: Boolean,
      required: true,
    },
    statusMessage: String,
    canEdit: Boolean,
    isCertification: Boolean,
    applicableAssignments: [Object],
  },

  data() {
    return {
      currentBoundaryName: null,
      DAYSECS: 24 * 3600,
      now: Date.now() / 1000,
      STATUS: {
        NONE: 'NONE',
        OVERDUE: 'OVERDUE',
        DUE_TODAY: 'DUE_TODAY',
        DUE_SOON: 'DUE_SOON',
      },
    };
  },

  computed: {
    stacked() {
      return this.currentBoundaryName === 'small';
    },
    daysUntilDue() {
      return Math.floor((this.dueDate - this.now) / this.DAYSECS);
    },
    dueStatus() {
      if (this.dueDate > -1 && this.now > this.dueDate) {
        return this.STATUS.OVERDUE;
      }
      if (this.daysUntilDue == 0) {
        return this.STATUS.DUE_TODAY;
      }
      if (this.daysUntilDue > 0 && this.daysUntilDue < 11) {
        return this.STATUS.DUE_SOON;
      }
      return this.STATUS.NONE;
    },
    typeString() {
      return this.$str(
        this.isCertification ? 'certification' : 'program',
        'totara_program'
      ).toLowerCase();
    },
    dueStatusString() {
      switch (this.dueStatus) {
        case this.STATUS.OVERDUE:
          return this.$str('overdue', 'totara_program');
        case this.STATUS.DUE_TODAY:
          return this.$str('duetoday', 'totara_program');
        case this.STATUS.DUE_SOON:
          return this.$str('dueinxdays', 'totara_program', this.daysUntilDue);
        case this.STATUS.NONE:
          return '';
        default:
          return '';
      }
    },
    showEnrolButton() {
      if (this.userAssigned) {
        return false;
      }

      return this.applicableAssignments?.some(
        assignment => assignment.can_self_enrol
      );
    },
    withdrawString() {
      const forceEnrolled = this.applicableAssignments?.some(
        option => option.is_enrolled && !option.can_self_unenrol
      );

      return forceEnrolled
        ? this.$str('youcantwithdraw', 'totara_program', this.typeString)
        : '';
    },
  },

  methods: {
    /**
     * Handles responsive resizing which wraps the grid layout for this page
     *
     * @param {String} boundaryName
     */
    resize(boundaryName) {
      this.currentBoundaryName = boundaryName;
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-programHeader {
  margin-bottom: var(--gap-8);

  &__wrapper {
    display: flex;
    justify-content: space-between;
    margin: 0 0 var(--gap-8);
  }

  &__image {
    width: rem-px(240);
    height: fit-content;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    border-radius: var(--border-radius-normal);
  }

  &__title {
    @include font(h1);
    margin: 0;
  }

  &__status {
    margin-top: var(--gap-1);
    margin-bottom: calc(var(--gap-2) + (var(--gap-1) / 2));
    margin-left: var(--gap-2);
    padding-top: calc(var(--gap-1) / 2);
    white-space: nowrap;
    vertical-align: middle;
  }

  &__info {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--gap-4);
    @media (min-width: $tui-screen-xs) {
      grid-template-columns: auto 1fr;
    }
  }

  &__programStatus {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: var(--gap-2);
    align-items: center;
  }

  &__dueDate {
    &--overdue {
      color: var(--color-prompt-alert);
    }
  }

  &__extensionLink {
    white-space: nowrap;
  }
}
</style>
