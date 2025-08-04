/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Jack Humphrey <jack.humphrey@totara.com>
 * @module approvalform_enrol
 */

import CourseLink from 'approvalform_enrol/components/CourseLink';
import CourseLinkInputSized from 'approvalform_enrol/components/CourseLinkInputSized';
import { uniformFieldWrapper } from 'mod_approval/schema_form';
import { createUniformInputWrapper } from 'tui/components/uniform';

const CourseLinkConnected = uniformFieldWrapper(
  createUniformInputWrapper(CourseLinkInputSized)
);

export const fields = {
  course_link: {
    supports: {
      edit: false,
    },
    fieldComponent: CourseLinkConnected,
    viewFieldComponent: CourseLink,
    displayText(value) {
      if (!value) {
        return null;
      }
      try {
        const data = JSON.parse(value);
        return data.name;
      } catch (e) {
        return null;
      }
    },
  },
};
