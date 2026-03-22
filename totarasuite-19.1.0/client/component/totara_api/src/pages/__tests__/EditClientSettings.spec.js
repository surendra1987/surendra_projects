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
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Keanu Johnson-Carnevale <keanu.johnson-carnevale@totara.com>
 * @module totara_api
 */

import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import EditClientSettings from '../EditClientSettings.vue';
import apiClientSettingsQuery from 'totara_api/graphql/client_settings';

async function renderElements() {
  const apiClientsSettingsQueryResult = jest.fn(() => ({
    data: {
      settings: {
        client_settings: {
          id: 1,
          client_rate_limit: 250000,
          default_token_expiry_time: 86400,
          response_debug: 'DEVELOPER',
          enable_introspection: false,
          allowed_ip_list: '3.3.3.3',
        },
        global_settings: {
          site_rate_limit: 500000,
          client_rate_limit: 250000,
          max_complexity_cost: 6000,
          default_token_expiry_time: 86400,
          response_debug: 'NORMAL',
        },
      },
    },
  }));

  render(EditClientSettings, {
    props: {
      clientId: 1,
    },
    mockQueries: [
      {
        request: {
          query: apiClientSettingsQuery,
          variables: { client_id: 1 },
        },
        result: apiClientsSettingsQueryResult,
      },
    ],
  });
}

describe('Test allowed IP addresses setting', () => {
  async function testValidationOfAllowedIpAddresses(ip_address) {
    await waitFor(() => {
      expect(
        screen.queryByText(/\[\[loading, core\]\]/i)
      ).not.toBeInTheDocument();
    });

    const textbox = screen.getByRole('textbox', {
      name: /\[\[setting:allowed_ip_list, totara_api\]\]/i,
    });

    await fireEvent.update(textbox, ip_address);

    expect(textbox.value).toBe(ip_address);

    const saveButton = screen.getByRole('button', {
      name: /\[\[save, totara_core\]\]/i,
    });

    await fireEvent.click(saveButton);

    await waitFor(() => {
      const error_message = screen.getByText(
        /\[\[setting:allowed_ip_list_error, totara_api, "(.)*"\]\]/i
      );

      expect(error_message).toBeInTheDocument();
    });
  }
  it('checks incomplete IP addresss', async () => {
    await renderElements();
    await testValidationOfAllowedIpAddresses('3.3.3.');
    await testValidationOfAllowedIpAddresses('.3.3.3');
    await testValidationOfAllowedIpAddresses('.3.3..3');
  });

  it('catches an incomplete IP when hidden among valid IPs', async () => {
    await renderElements();

    await testValidationOfAllowedIpAddresses(
      '111.111.111.111\n222.222.222.222\n333.333.3.'
    );

    await testValidationOfAllowedIpAddresses(
      '111.111.111.111\n2.222.222.222\n333.333.333.333\n'
    );
  });

  it('catches incorrect cidr notation', async () => {
    await renderElements();
    await testValidationOfAllowedIpAddresses('203.0.113.128/290');
    await testValidationOfAllowedIpAddresses('122.332.1.1283/290');
    await testValidationOfAllowedIpAddresses('122.332.1.000/000');
  });

  it('catches an incorrect cidr ip hidden among valid cidr ips', async () => {
    await renderElements();
    await testValidationOfAllowedIpAddresses(
      '203.0.113.128/29\n105.343.300/222\n666.543.777/99'
    );
    await testValidationOfAllowedIpAddresses(
      '203.0.113.128/29\n105.343.300/22\n666.543.777/99'
    );
  });

  it('catches an incorrectly entered ip-range', async () => {
    await renderElements();
    await testValidationOfAllowedIpAddresses('231.3.56.0-9000');
    await testValidationOfAllowedIpAddresses('100-200.3.56.200');
    await testValidationOfAllowedIpAddresses('1.2.300.3000-300');
  });

  it('catches an incorrectly entered ip-range in a group of correct ip-ranges', async () => {
    await renderElements();

    await testValidationOfAllowedIpAddresses(
      '231.3.56.10-20\n231.3.56.50-20\n291.5.56-60.10-20\n291.5.60.400-900'
    );
    await testValidationOfAllowedIpAddresses(
      '231.3.56.10-20\n231.3.56.0~20\n291.5.60.10-20\n291.5.60.400-900'
    );
  });

  it('catches an incorrect ip within a mix of different acceptable formats', async () => {
    await renderElements();

    await testValidationOfAllowedIpAddresses(
      '231.3.56.10-20\n231.300.56.220\n291.5.60/20\n291.5.60.400-900\n100\n100.200.300.400.500'
    );

    await testValidationOfAllowedIpAddresses(
      '231.3.56.10-20\n231.300.56.220\n291.5.60/20\n291.5.60.400-900\n100\n100.200.300.400.500'
    );
  });

  describe('Testing the text displayed for the allowed IP list form row', () => {
    it('displays the correct text for each area', async () => {
      await renderElements();

      await waitFor(() => {
        expect(
          screen.queryByText(/\[\[loading, core\]\]/i)
        ).not.toBeInTheDocument();
      });

      const fieldName = screen.getByText(
        /\[\[setting:allowed_ip_list, totara_api\]\]/i
      );

      expect(fieldName).toBeInTheDocument();

      const description = screen.getByText(
        /\[\[setting:allowed_ip_list_desc, totara_api\]\]/i
      );

      expect(description).toBeInTheDocument();

      const defaultValue = screen.getByText(
        /\[\[formfield_defaults, totara_core\]\] \[\[setting:allowed_ip_list_default, totara_api\]\]/i
      );

      expect(defaultValue).toBeInTheDocument();

      const helpText = screen.getAllByText(
        /\[\[setting:allowed_ip_list_info, totara_api\]\]/i
      );

      expect(helpText[0]).toBeInTheDocument();
      expect(helpText[1]).toBeInTheDocument();
    });
  });
});
