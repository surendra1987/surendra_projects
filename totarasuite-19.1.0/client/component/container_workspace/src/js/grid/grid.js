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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @module container_workspace
 */

/** calculate width X number of cards would take up, including gutters */
const width = cards => 252 * cards + (cards - 1) * 16;

/** calculate units (of 12) X number of cards per row would take up each */
const units = cards => 12 / cards;

export const cardGrid = {
  xs: {
    name: 'xs',
    boundaries: [0, width(2) - 1],
    direction: 'horizontal',
    cardUnits: units(1),
  },

  s: {
    name: 's',
    boundaries: [width(2), width(3) - 1],
    direction: 'horizontal',
    cardUnits: units(2),
  },

  smallplus: {
    name: 'smallplus',
    boundaries: [width(3), width(4) - 1],
    direction: 'horizontal',
    cardUnits: units(3),
  },

  m: {
    name: 'm',
    boundaries: [width(4), width(6) - 1],
    direction: 'horizontal',
    cardUnits: units(4),
  },

  l: {
    name: 'l',
    boundaries: [width(6), Infinity],
    direction: 'horizontal',
    cardUnits: units(6),
  },
};
