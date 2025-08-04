/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Simon Tegg <simon.tegg@totaralearning.com>
 * @module mod_approval
 */
import simpleMount from './__util__/simple_mount';
import SchemaForm from '../SchemaForm';
import form from './__fixtures__/test_form.json';

jest.mock('tui/tui', function() {
  return {
    import: async function(path) {
      return require(path);
    },

    defaultExport(x) {
      return x.default || x;
    },
  };
});

jest.mock('tui/apollo/client', function() {
  return {
    query() {
      return Promise.resolve({
        data: {
          editor: {
            context_id: 1,
            name: 'textarea',
          },
        },
      });
    },
  };
});

const props = {
  schema: form,
  inputCharLength: 'full',
};

describe('SchemaForm', () => {
  it('should correctly set the initial values', async () => {
    const wrapper = simpleMount(SchemaForm, props);

    form.sections.forEach(section => {
      section.fields.forEach(field => {
        const initialValue = wrapper.vm.calculatedInitialValues[field.key];
        expect(initialValue).not.toBe(undefined);
        if (field.default) {
          expect(initialValue).toBe(field.default);
        } else {
          expect(initialValue).toBe(null);
        }
      });
    });
  });
});
