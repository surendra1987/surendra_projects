/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { defineComponent, h, mergeProps, toHandlerKey } from 'vue';
import FormField from 'tui/components/uniform/FormField';
import { charLengthProp } from '../form/form_common';

/**
 * Create a wrapper component for an input.
 *
 * The only hard requirement is that the input component works with v-model.
 *
 * @param {(object|Vue)} input Input component to wrap.
 * @param {object} [options]
 * @param {boolean} [options.passAriaLabelledby] Pass the label ID as aria-labelledby.
 * @param {boolean} [options.touchOnChange] Mark input as touched whenever it changes.
 * @param {string} [options.valueProp] Name of prop to pass value to.
 * @returns {(object|Vue)} Vue component.
 */
export function createUniformInputWrapper(input, options = {}) {
  const wrapperProps = {
    id: {},

    name: {
      type: [String, Number, Array],
      required: true,
    },

    validate: Function,
    validations: [Function, Array],
    charLength: charLengthProp,
  };

  const valueProp = options.valueProp || 'value';
  const valueEvent = `update:${valueProp}`;

  return defineComponent({
    inheritAttrs: false,

    props: wrapperProps,

    render() {
      return h(
        FormField,
        {
          name: this.name,
          validate: this.validate,
          validations: this.validations,
          charLength: this.charLength,
        },
        {
          default: ({ value, update, blur, touch, labelId, attrs }) => {
            const inputProps = {
              ...this.$attrs,
              ...attrs,
              charLength: 'full',
              [valueProp]: value,
            };
            if (options.passAriaLabelledby) {
              this.ariaLabelledby = labelId;
            }
            if (this.id) {
              inputProps.id = this.id;
            }
            if (options.passId === false) {
              delete inputProps.id;
            }
            return h(
              input,
              mergeProps(inputProps, {
                [toHandlerKey(valueEvent)]: value => {
                  update(value);
                  if (options.touchOnChange) touch();
                },
                onBlur: blur,
              }),
              this.$slots
            );
          },
        }
      );
    },
  });
}
