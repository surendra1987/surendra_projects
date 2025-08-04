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
  @module totara_program
-->
<template>
  <Uniform
    :initial-values="formDefault"
    class="tui-totara_program-courseSetForm"
    :validate="validateForm"
    :errors="errors"
    @validation-changed="updateValidation"
    @submit="submit"
  >
    <FormRow
      :label="$str('label:setname', 'totara_program')"
      :help-title="$str('setlabel', 'totara_program')"
    >
      <FormText name="label" :char-length="50" />
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('setlabel_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>
    <FormRow
      :label="$str('label:learnermustcomplete', 'totara_program')"
      :help-title="$str('completiontype', 'totara_program')"
    >
      <FormSelect
        name="completiontype"
        :options="courseOptions"
        @input="changeCompletionType"
      />
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('completiontype_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>

    <FormRow
      :label="$str('mincourses', 'totara_program')"
      :help-title="$str('mincourses', 'totara_program')"
    >
      <FormNumber
        name="mincourses"
        :min="0"
        :max="courses.length"
        :disabled="completionType != COMPLETION.SOME"
        :validations="v => [v.integer(), v.min(0), v.max(courses.length)]"
        char-length="5"
      />
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('mincourses_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>
    <FormRow
      :label="$str('coursescorefield', 'totara_program')"
      :help-title="$str('coursescorefield', 'totara_program')"
    >
      <FormSelect
        name="coursesumfield"
        :options="customFields"
        :disabled="completionType != COMPLETION.SOME"
        @input="changeCustomField"
      />
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('coursescorefield_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>
    <FormRow
      :label="$str('label:minimumscore', 'totara_program')"
      :help-title="$str('minimumscore', 'totara_program')"
    >
      <FormNumber
        name="coursesumfieldtotal"
        :min="0"
        :max="2147483647"
        :disabled="completionType != COMPLETION.SOME || customField == 0"
        :validations="v => [v.integer(), v.min(0), v.max(2147483647)]"
        char-length="5"
      />
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('minimumscore_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>
    <FormRow
      :label="$str('label:minimumtimerequired', 'totara_program')"
      :label-legend="true"
      :help-title="$str('minimumtimerequired', 'totara_program')"
    >
      <InputSet>
        <InputSetCol units="1">
          <FormNumber
            name="amount"
            :aria-label="$str('label:timeallowance', 'totara_program')"
            :min="0"
            :max="maxTimeAllowed"
            :validations="v => [v.integer(), v.min(0)]"
            :autofocus="true"
          />
        </InputSetCol>
        <InputSetCol units="3">
          <FormSelect
            name="period"
            :options="dateOptions"
            :aria-label="$str('timeperiod', 'totara_program')"
            @input="updatePeriod"
          />
        </InputSetCol>
      </InputSet>
      <template v-slot:help-message>
        <div class="tui-totara_program-courseSetForm__helpText">
          {{ $str('minimumtimerequired_help', 'totara_program') }}
        </div>
      </template>
    </FormRow>
    <CoursesGrid
      :editing="true"
      :courses="courses"
      :course-set-name="formData.label"
      :tenant-id="tenantId"
      @add-courses="addCourses"
      @move-course="moveCourse"
      @remove-course="removeCourse"
    />
    <ButtonGroup class="tui-totara_program-courseSetForm__buttons">
      <Button
        :text="$str('save', 'totara_core')"
        :disabled="!isValid"
        :styleclass="{ primary: true }"
        type="submit"
      />
      <Button :text="$str('cancel', 'core')" @click="$emit('cancel')" />
    </ButtonGroup>
  </Uniform>
</template>
<script>
import {
  Uniform,
  FormNumber,
  FormRow,
  FormText,
  FormSelect,
} from 'tui/components/uniform';
import InputSet from 'tui/components/form/InputSet';
import InputSetCol from 'tui/components/form/InputSetCol';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Button from 'tui/components/buttons/Button';
import CoursesGrid from 'totara_program/components/manage_program/content/CoursesGrid';

const DAYS = 2;
const WEEKS = 3;
const MONTHS = 4;
const YEARS = 5;
const UNSPECIFIED = 6;

const DAYSECS = 24 * 3600;

export default {
  components: {
    Uniform,
    FormNumber,
    FormRow,
    FormText,
    FormSelect,
    InputSet,
    InputSetCol,
    ButtonGroup,
    Button,
    CoursesGrid,
  },

  props: {
    customFields: Array,
    formData: Object,
    serverCourses: Array,
    tenantId: {
      required: false,
      default: null,
      type: [String, Number],
    },
  },

  emits: ['cancel', 'submit'],

  data() {
    let amount = this.formData.timeallowed / DAYSECS;
    const COMPLETION = {
      ANY: 'ANY',
      ALL: 'ALL',
      SOME: 'SOME',
      OPTIONAL: 'OPTIONAL',
    };

    let period = UNSPECIFIED;
    if (amount === 0) {
      period = UNSPECIFIED;
    } else if (amount % 365 === 0) {
      period = YEARS;
      amount = amount / 365;
    } else if (amount % 30 === 0) {
      period = MONTHS;
      amount = amount / 30;
    } else if (amount % 7 === 0) {
      period = WEEKS;
      amount = amount / 7;
    } else {
      period = DAYS;
    }

    let courses = [...this.serverCourses].sort((a, b) => {
      if (a.sortorder && b.sortorder) {
        return Number(a.sortorder) - Number(b.sortorder);
      }
      return 0;
    });

    return {
      dateOptions: [
        {
          id: DAYS,
          label: this.$str('days', 'totara_core'),
        },
        {
          id: WEEKS,
          label: this.$str('weeks', 'totara_core'),
        },
        {
          id: MONTHS,
          label: this.$str('months', 'totara_core'),
        },
        {
          id: YEARS,
          label: this.$str('years', 'totara_program'),
        },
        {
          id: UNSPECIFIED,
          label: this.$str('nominimumtime', 'totara_program'),
        },
      ],
      courseOptions: [
        {
          id: COMPLETION.ANY,
          label: this.$str('onecourse', 'totara_program'),
        },
        {
          id: COMPLETION.ALL,
          label: this.$str('allcourses', 'totara_program'),
        },
        {
          id: COMPLETION.SOME,
          label: this.$str('somecourses', 'totara_program'),
        },
        {
          id: COMPLETION.OPTIONAL,
          label: this.$str('completionoptional', 'totara_program'),
        },
      ],
      completionType: this.formData.completiontype,
      customField: this.formData.coursesumfield,
      formDefault: Object.assign({}, this.formData, { amount, period }),
      COMPLETION,
      courses: courses,
      maxTimeAllowed: 11000,
      formValid: true,
      errors: {},
    };
  },

  computed: {
    isValid() {
      return this.courses.length > 0 && this.formValid;
    },
  },

  /**
   * Executes when the component is mounted to the DOM.
   * Updates the maxTimeAllowed and validates the form.
   * @returns {void}
   * @description This function set the maxTimeAllowed value and validates the form.
   */
  mounted() {
    // Set the current maxTimeAllowed value.
    this.updatePeriod(this.formDefault.period);

    // Validate the form with default amount and period
    this.validateForm({
      amount: this.formDefault.amount,
      period: this.formDefault.period,
    });

    // Note: You can also use ref to set focus on the input,
    // but it's not working as expected.
  },

  methods: {
    /**
     * Updates the local data property for the completion type
     * so the disabled attributes work correctly in the template
     *
     * @param {String} value the new completion type value
     */
    changeCompletionType(value) {
      this.completionType = value;
    },

    /**
     * Updates the local data property for the custom field
     * so the disabled attributes work correctly in the template
     *
     * @param {String} value the new completion type value
     */
    changeCustomField(value) {
      this.customField = value;
    },
    /**
     * Validates the form values.
     *
     * @param {Object} values - The form input values.
     * @param {number} values.amount - The time value.
     *
     * @returns {Object} An object representing validation errors. Empty if there are no errors.
     *
     * @description
     * This function validates the form input values, specifically the 'amount' field, which represents the time allowed.
     * The validation depends on the 'period' field, so it's recommended to handle the validation at the form scope.
     * If the 'amount' exceeds the maximum time allowed, an error message is returned.
     */
    validateForm(values) {
      if (values.amount > this.maxTimeAllowed) {
        return {
          amount: this.$str(
            'error:validate_max_input_number',
            'totara_program',
            this.maxTimeAllowed
          ),
        };
      }

      return {};
    },

    /**
     * Updates the form validation status so that the form can't be submitted
     * when the form has invalid data.
     *
     * @param {object} value The form validation even object
     */
    updateValidation(value) {
      this.formValid = value.isValid;
    },

    /**
     * As we can only submit values less than 4 billion seconds (~32 years),
     * we need to have a dynamic limit for the time period
     */
    updatePeriod(event) {
      switch (event) {
        case DAYS:
          this.maxTimeAllowed = 11000;
          break;
        case WEEKS:
          this.maxTimeAllowed = 600;
          break;
        case MONTHS:
          this.maxTimeAllowed = 360;
          break;
        case YEARS:
          this.maxTimeAllowed = 30;
          break;
        default:
          this.maxTimeAllowed = 11000;
          break;
      }
    },

    /**
     * Gets the form data to be feed back to the server
     *
     * @returns {Object} Data to be feed into the graphql from this form
     */
    submit(values) {
      let time_allowed = parseInt(values.amount, 10);

      switch (values.period) {
        case DAYS:
          time_allowed = time_allowed * DAYSECS;
          break;
        case WEEKS:
          time_allowed = time_allowed * DAYSECS * 7;
          break;
        case MONTHS:
          time_allowed = time_allowed * DAYSECS * 30;
          break;
        case YEARS:
          time_allowed = time_allowed * DAYSECS * 365;
          break;
        default:
          time_allowed = 0;
      }

      for (let course = 0; course < this.courses.length; course++) {
        this.courses[course].sortorder = course.toString();
      }

      this.$emit('submit', {
        label: values.label,
        completion_type: values.completiontype,
        min_courses: parseInt(values.mincourses ? values.mincourses : 0, 10),
        course_sum_field: values.coursesumfield,
        course_sum_field_total: parseInt(
          values.coursesumfieldtotal ? values.coursesumfieldtotal : 0,
          10
        ),
        time_allowed: time_allowed ? time_allowed : 0,
        courses: this.courses,
      });
    },

    addCourses(courses) {
      courses.forEach(({ id, fullname, image }) => {
        if (!this.courses.find(course => course.id == id)) {
          this.courses.push({
            id,
            fullname,
            image,
          });
        }
      });
    },

    moveCourse(course, index) {
      this.courses = this.courses.filter(c => c.id !== course.id);
      this.courses.splice(index, 0, course);
    },

    cancel() {
      this.$emit('cancel');
    },
    /**
     Remove the course with the given ID from the list of courses.
     @param {number} id - The ID of the course to remove.
     @returns {void}
     */
    removeCourse(id) {
      if (id) {
        this.courses = this.courses.filter(courseSet => {
          return courseSet.id !== id;
        });
      }
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-courseSetForm {
  &__helpText {
    // remove the gap above the first line the text
    margin-top: calc(var(--gap-4) * -1);
    white-space: pre-line;
  }

  &__buttons {
    float: right;
  }
}
</style>
