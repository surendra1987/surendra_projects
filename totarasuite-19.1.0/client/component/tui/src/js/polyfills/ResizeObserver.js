/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 * @deprecated since Totara 19.0
 */

const ResizeObserver = window.ResizeObserver;

if (process.env.NODE_ENV !== 'production') {
  console.warning(
    'tui/polyfills/ResizeObserver is deprecated and should be replaced with direct usage of the browser API.'
  );
}

export default ResizeObserver;
