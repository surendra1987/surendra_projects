/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Rami Habib <rami.habib@totara.com>
 * @module totara_program
 */

import EnrolConfirmationModal from '../EnrolConfirmationModal';
import { shallowMount } from '@vue/test-utils';

describe('EnrolConfirmationModal', () => {
  const mockSelectedOption = {
    id: '2',
    type: 'Group',
    name: 'Group 2',
    description: '',
    can_self_enrol: true,
    can_self_unenrol: true,
    is_enrolled: false,
    due_date: '30 April 2025',
    actual_due_date: '30 April 2025',
    can_due_date_change: true,
  };

  const mockEnrolmentOption1 = {
    ...mockSelectedOption,
    id: '3',
    due_date: '30 May 2025',
    actual_due_date: '30 May 2025',
  };

  const mockEnrolmentOption2 = {
    ...mockSelectedOption,
    id: '4',
    is_enrolled: true,
    due_date: '30 June 2025',
    actual_due_date: '30 June 2025',
  };

  describe('When a user is enrolling', () => {
    describe('When it is the first enrolment', () => {
      const modal = shallowMount(EnrolConfirmationModal, {
        props: {
          selectedEnrolmentOption: mockSelectedOption,
          isEnrolled: false,
          typeString: 'program',
          programName: 'Prog 101',
        },
      });

      it('modal title shows "Enrol in program"', () => {
        expect(modal.vm.modalTitle).toEqual(
          '[[enrolin, totara_program, "program"]]'
        );
      });

      it('confirmation text shows "are you sure you want to enrol with..."', () => {
        expect(modal.vm.confirmationText).toEqual(
          '[[areyousureprogramenrolmentmessage, totara_program, {"group":"Group 2","program":"Prog 101"}]]'
        );
      });

      it('confirmation button shows "Enrol"', () => {
        expect(modal.vm.confirmButtonText).toEqual('[[enrol, enrol]]');
      });
    });
    describe('When it is the second (or more) enrolment', () => {
      const modal = shallowMount(EnrolConfirmationModal, {
        props: {
          selectedEnrolmentOption: mockSelectedOption,
          isEnrolled: true,
          programName: 'Prog 101',
        },
      });

      it('modal title shows "Enrol in group"', () => {
        expect(modal.vm.modalTitle).toEqual(
          '[[enrolin, totara_program, "Group"]]'
        );
      });

      it('confirmation text shows "... add program to enrolment options?"', () => {
        expect(modal.vm.confirmationText).toEqual(
          '[[areyousureaddprogramenrolmentmessage, totara_program, {"group":"Group 2","program":"Prog 101"}]]'
        );
      });

      it('confirmation button shows "Enrol"', () => {
        expect(modal.vm.confirmButtonText).toEqual('[[enrol, enrol]]');
      });
    });
  });

  describe('When a user is withdrawing', () => {
    const selectedEnrolmentOption = {
      ...mockSelectedOption,
      is_enrolled: true,
    };
    describe('When it is the only enrolment', () => {
      const modal = shallowMount(EnrolConfirmationModal, {
        props: {
          selectedEnrolmentOption,
          currentEnrolmentOptions: [
            selectedEnrolmentOption,
            mockEnrolmentOption1,
          ],
          isEnrolled: true,
          typeString: 'program',
          programName: 'Prog 101',
        },
      });

      it('modal title shows "Withdraw from program"', () => {
        expect(modal.vm.modalTitle).toEqual(
          '[[withdrawfrom, totara_program, "program"]]'
        );
      });

      it('withdraw effect text shows "you will be removed..."', () => {
        expect(modal.vm.withdrawEffectText).toEqual(
          '[[removeenrolment, totara_program, "program"]]'
        );
      });

      it('confirmation text shows "are you sure you want to withdraw from..."', () => {
        expect(modal.vm.confirmationText).toEqual(
          '[[areyousureprogramwithdrawmessage, totara_program, {"group":"Group 2","program":"Prog 101"}]]'
        );
      });

      it('shows "Withdraw" when unenrolling"', () => {
        expect(modal.vm.confirmButtonText).toEqual(
          '[[withdraw, totara_program]]'
        );
      });
    });
    describe('When it is the second (or more) enrolment', () => {
      const modal = shallowMount(EnrolConfirmationModal, {
        props: {
          selectedEnrolmentOption,
          currentEnrolmentOptions: [
            selectedEnrolmentOption,
            mockEnrolmentOption2,
          ],
          isEnrolled: true,
          typeString: 'program',
          programName: 'Prog 101',
        },
      });

      it('modal title shows "Withdraw from group"', () => {
        expect(modal.vm.modalTitle).toEqual(
          '[[withdrawfrom, totara_program, "Group"]]'
        );
      });

      it('withdraw effect text shows "... other enrolments won\'t be affected"', () => {
        expect(modal.vm.withdrawEffectText).toEqual(
          '[[unaffectedenrolments, totara_program, "program"]]'
        );
      });

      it('confirmation text shows "are you sure you want to withdraw from..."', () => {
        expect(modal.vm.confirmationText).toEqual(
          '[[areyousureprogramwithdrawmessage, totara_program, {"group":"Group 2","program":"Prog 101"}]]'
        );
      });

      it('shows "Withdraw" when unenrolling"', () => {
        expect(modal.vm.confirmButtonText).toEqual(
          '[[withdraw, totara_program]]'
        );
      });
    });
  });
});
