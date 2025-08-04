<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totara.com>
  @module format_pathway
-->
<template>
  <div
    class="tui-format_pathway-courseInformation"
    :class="{
      'tui-format_pathway-courseInformation--inProgress': displayProgress,
    }"
  >
    <div class="tui-format_pathway-courseInformation__base">
      <div
        class="tui-format_pathway-courseInformation__baseName"
        :title="courseData.full_name"
      >
        {{ courseData.full_name }}
      </div>
      <img
        :src="courseData.image_url"
        alt=""
        class="tui-format_pathway-courseInformation__baseImage"
      />
    </div>
    <Button
      v-if="!$apollo.loading && displayEnrolButton"
      :styleclass="{
        primary: !interactiveEnrolPendingApproval,
      }"
      :aria-label="$str('enrol_course', 'format_pathway')"
      :text="displayEnrolText"
      :loading="isEnrolling"
      @click="enrol"
    />
    <Button
      v-if="displayCompleteButton"
      class="tui-format_pathway-courseInformation__selfComplete"
      :styleclass="{ primary: true }"
      :aria-label="
        $str('markcoursexcomplete', 'format_pathway', courseData.full_name)
      "
      :text="$str('markcoursecomplete', 'format_pathway')"
      :loading="isSelfCompleting"
      @click="selfComplete"
    />
    <Lozenge
      v-if="displayProgress && !displayCompleteButton"
      class="tui-format_pathway-courseInformation__status"
      :text="completionStatus"
      type="info"
    />
    <Progress
      v-if="displayProgress"
      class="tui-format_pathway-courseInformation__progress"
      :x-small="true"
      :hide-value="true"
      :chromeless="true"
      :value="completionData.completion.progress"
      :aria-label="$str('course_progress_aria', 'format_pathway')"
    />
  </div>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import Progress from 'tui/components/progress/Progress';
import Lozenge from 'tui/components/lozenge/Lozenge';
import { notify } from 'tui/notifications';

// GraphQL
import requestNonInteractiveEnrol from 'format_pathway/graphql/request_non_interactive_enrol_v2';
import courseSelfCompletion from 'completion/graphql/course_self_complete';
import getNonInteractiveEnrolPendingApprovalInfoQuery from 'core/graphql/non_interactive_enrol_pending_approval_info';

export default {
  components: {
    Button,
    Progress,
    Lozenge,
  },

  props: {
    courseData: {
      type: Object,
      required: true,
    },
    completionData: Object,
    courseId: {
      type: Number,
      required: true,
    },
    courseInteractor: {
      type: Object,
      required: true,
    },
    hasActiveActivity: {
      type: Boolean,
      required: true,
    },
  },

  data() {
    return {
      canSelfComplete: true,
      isEnrolling: false,
      isSelfCompleting: false,
      nonInteractiveEnrolPendingApprovalInfo: {},
    };
  },

  apollo: {
    nonInteractiveEnrolPendingApprovalInfo: {
      query: getNonInteractiveEnrolPendingApprovalInfoQuery,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ result: data }) {
        return data;
      },
      skip() {
        return !this.courseInteractor.non_interactive_enrol_requires_approval;
      },
    },
  },

  computed: {
    completionVisible() {
      return (
        this.completionData &&
        this.completionData.completionenabled === true &&
        this.completionData.completion &&
        this.completionStatus !== null
      );
    },

    completionStatus() {
      switch (this.completionData.completion.statuskey) {
        case 'complete':
          return this.$str('complete', 'completion');
        case 'completeviarpl':
          return this.$str('completeviarpl', 'completion');
        case 'inprogress':
          return this.$str('inprogress', 'completion');
        case 'notyetstarted':
          return this.$str('notyetstarted', 'completion');
        default:
          return null;
      }
    },

    canEnrol() {
      return (
        this.courseInteractor.can_enrol &&
        !this.courseInteractor.is_site_guest &&
        this.courseInteractor.non_interactive_enrol_instance_enabled
      );
    },

    displayEnrolButton() {
      return this.canEnrol && this.hasActiveActivity;
    },

    displayCompleteButton() {
      return (
        this.courseInteractor.can_mark_self_complete && this.canSelfComplete
      );
    },

    interactiveEnrolPendingApproval() {
      return (
        this.canEnrol &&
        !this.courseInteractor.is_enrolled_not_pending_approval &&
        !this.courseInteractor.non_interactive_enrol_requires_approval
      );
    },

    displayEnrolText() {
      if (this.nonInteractiveEnrolPendingApprovalInfo.pending) {
        return this.nonInteractiveEnrolPendingApprovalInfo.button_name;
      }
      if (this.courseInteractor.non_interactive_enrol_requires_approval) {
        return this.$str('requestapproval', 'enrol_self');
      }
      if (this.interactiveEnrolPendingApproval) {
        return this.$str('enrolmentoptions', 'core_enrol');
      }
      return this.$str('enrol', 'core_enrol');
    },

    displayProgress() {
      return !this.$apollo.loading && this.completionVisible && !this.canEnrol;
    },
  },

  methods: {
    async enrol() {
      if (this.courseInteractor.supports_non_interactive_enrol) {
        const {
          pending,
          needs_create_new_application,
          redirect_url,
        } = this.nonInteractiveEnrolPendingApprovalInfo;
        if (pending && !needs_create_new_application) {
          window.location.href = redirect_url;
          return;
        }

        await this.nonInteractiveEnrol();
      } else {
        window.location.href = this.$url('/enrol/index.php', {
          id: this.courseId,
        });
      }
    },

    async nonInteractiveEnrol() {
      if (this.isEnrolling) {
        return;
      }
      try {
        this.isEnrolling = true;
        let {
          data: { result },
        } = await this.$apollo.mutate({
          mutation: requestNonInteractiveEnrol,
          variables: { course_id: this.courseId },
          refetchQueries: [
            'format_pathway_get_course_interactor',
            'format_pathway_get_course_user_navigation',
            'totara_core_settings_navigation_tree',
          ],
          awaitRefetchQueries: true,
        });

        if (result.redirect_url) {
          window.location.href = result.redirect_url;
          return;
        }

        if (result.success) {
          notify({
            message: this.$str('enrol_success_message', 'enrol_self'),
            type: 'success',
          });
        }
        this.isEnrolling = false;
      } catch (e) {
        this.isEnrolling = false;
        throw e;
      }
    },

    async selfComplete() {
      if (!this.canSelfComplete) {
        return;
      }
      this.isSelfCompleting = true;

      let {
        data: { core_completion_course_self_complete: result },
      } = await this.$apollo.mutate({
        mutation: courseSelfCompletion,
        variables: { courseid: this.courseId },
        refetchQueries: ['format_pathway_get_course_completion'],
      });

      if (result) {
        notify({
          message: this.$str('coursemarkedascompleted', 'format_pathway'),
          type: 'success',
        });
        this.canSelfComplete = false;
      }
      this.isSelfCompleting = false;
    },
  },
};
</script>
<style lang="scss">
.tui-format_pathway-courseInformation {
  padding-bottom: var(--gap-4);
  overflow: hidden;
  background-color: var(--color-neutral-3);
  border-radius: var(--border-radius-normal);

  &--inProgress {
    padding-bottom: 0;
  }

  &__base {
    display: flex;
    padding-top: var(--gap-4);
  }

  &__baseName {
    @include font(h4);
    display: -webkit-box;
    flex-grow: 1;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__baseImage {
    width: rem-px(64);
    height: fit-content;
    aspect-ratio: 16 / 9;
    margin-left: var(--gap-2);
    object-fit: cover;
    border-radius: var(--border-radius-normal);
  }

  &__completionProgress {
    flex-grow: 1;
    margin: auto 0 auto var(--gap-4);
  }

  > *:not(.tui-format_pathway-courseInformation__progress) {
    margin-right: var(--gap-4);
    margin-left: var(--gap-4);
  }

  > * + * {
    margin-top: var(--gap-4);
  }
}
</style>
