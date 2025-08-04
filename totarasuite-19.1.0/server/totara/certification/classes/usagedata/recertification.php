<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_certification
 */

namespace totara_certification\usagedata;

use core\orm\query\builder;
use tool_usagedata\export;
use totara_certification\entity\certification;
use totara_program\entity\program;

class recertification implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('certification_recertification_summary', 'totara_certification');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritdoc
     */
    public function export(): array {
        $certifications = builder::table(certification::TABLE, 'c')
            ->join([program::TABLE, 'p'], 'c.id', 'p.certifid')
            ->get();

        $certification_expirys = $certifications->filter(function ($certfication) {
            return $certfication->recertifydatetype == CERTIFRECERT_FIXED;
        })->to_array();

        $result = [
            'certification_completion_date' => $certifications->filter(function ($certfication) {
                return $certfication->recertifydatetype == CERTIFRECERT_COMPLETION;
            })->count(),
            'certification_expiry_date' => $certifications->filter(function ($certfication) {
                return $certfication->recertifydatetype == CERTIFRECERT_EXPIRY;
            })->count(),
            'fixed_expiry_date' => count($certification_expirys),
        ];

        $result = $this->calculate_minimum_recertify_ratios($result, $certifications);
        $result = $this->calculate_active_period_ratios($result, $certification_expirys);

        return $result;
    }

    /**
     * @param array $result
     * @param \core\orm\collection $certifications
     * @return array
     */
    public function calculate_minimum_recertify_ratios(array $result, \core\orm\collection $certifications): array {
        $result['minimum_recertify_ratio'] = [
            '0.0 - 0.2' => 0,
            '0.2 - 0.4' => 0,
            '0.4 - 0.6' => 0,
            '0.6 - 0.8' => 0,
            '0.8 - 1.0' => 0,
        ];

        foreach ($certifications->to_array() as $certification) {
            $ratio = number_format(1 - strtotime($certification['windowperiod']) / strtotime($certification['activeperiod']), 1);
            switch ($ratio) {
                case $ratio >= 0.0 && $ratio < 0.2:
                    ++$result['minimum_recertify_ratio']['0.0 - 0.2'];
                    break;
                case $ratio >= 0.2 && $ratio < 0.4:
                    ++$result['minimum_recertify_ratio']['0.2 - 0.4'];
                    break;
                case $ratio >= 0.4 && $ratio < 0.6:
                    ++$result['minimum_recertify_ratio']['0.4 - 0.6'];
                    break;
                case $ratio >= 0.6 && $ratio < 0.8:
                    ++$result['minimum_recertify_ratio']['0.6 - 0.8'];
                    break;
                case $ratio >= 0.8 && $ratio <= 1.0:
                    ++$result['minimum_recertify_ratio']['0.8 - 1.0'];
                    break;
                default:
                    break;
            }
        }
        return $result;
    }

    /**
     * @param array $result
     * @param array $certification_expirys
     * @return array
     */
    public function calculate_active_period_ratios(array $result, array $certification_expirys): array {
        $result['minimum_active_period_ratio'] = [
            '0.0' => 0,
            '0.0 - 0.2' => 0,
            '0.2 - 0.4' => 0,
            '0.4 - 0.6' => 0,
            '0.6 - 0.8' => 0,
            '0.8 - 1.0' => 0,
            '1.0' => 0
        ];
        if (!empty($certification_expirys)) {
            foreach ($certification_expirys as $cert) {
                if (strtotime($cert['minimumactiveperiod']) == strtotime($cert['windowperiod'])) {
                    ++$result['minimum_active_period_ratio']['0.0'];
                    continue;
                }

                if (strtotime($cert['minimumactiveperiod']) == strtotime($cert['activeperiod'])) {
                    ++$result['minimum_active_period_ratio']['1.0'];
                    continue;
                }

                $ratio = number_format(
                    (strtotime($cert['minimumactiveperiod']) - strtotime($cert['windowperiod'])) / (strtotime($cert['activeperiod']) - strtotime($cert['windowperiod'])),
                    1
                );

                switch ($ratio) {
                    case $ratio > 0 && $ratio < 0.2:
                        ++$result['minimum_active_period_ratio']['0.0 - 0.2'];
                        break;
                    case $ratio >= 0.2 && $ratio < 0.4:
                        ++$result['minimum_active_period_ratio']['0.2 - 0.4'];
                        break;
                    case $ratio >= 0.4 && $ratio < 0.6:
                        ++$result['minimum_active_period_ratio']['0.4 - 0.6'];
                        break;
                    case $ratio >= 0.6 && $ratio < 0.8:
                        ++$result['minimum_active_period_ratio']['0.6 - 0.8'];
                        break;
                    case $ratio >= 0.8 && $ratio < 1:
                        ++$result['minimum_active_period_ratio']['0.8 - 1.0'];
                        break;
                    default:
                        break;
                }
            }
        }
        return $result;
    }
}
