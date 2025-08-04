<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package mod_certificate
 */
namespace mod_certificate\download;

use Dompdf\Dompdf;
use mod_certificate\output\view_generator_factory;
use stdClass;
use totara_mobile\download\download_helper;
use totara_mobile\download\downloadable_activity;

class certificate extends downloadable_activity {
    /**
     * @var string|null
     */
    public ?string $grade;

    /**
     * @var string|null
     */
    public ?string $certificate_content = null;

    /**
     * @var array
     */
    public array $certificate_issues = [];

    /**
     * @return self
     */
    public function get_content(): self {
        global $CFG, $USER;
        require_once "$CFG->dirroot/mod/certificate/locallib.php";

        $activity = download_helper::get_generic_activity_content($this->cm_info, $this);

        $course = $this->get_course();
        $certificate = download_helper::get_activity_instance($this->cm_info);
        $attempts = certificate_get_attempts($certificate->id);
        if ($attempts && count($attempts) > 0) {
            foreach ($attempts as $attempt) {
                $activity->certificate_issues[] = $this->create_certification_issue($attempt, $certificate, $course);
            }

            $cm = $this->get_cm();
            $cert_record = certificate_get_issue($this->get_course(), $USER, $certificate, $this->cm_info->get_course_module_record());
            $view_generator = view_generator_factory::create($certificate, $cert_record, $course, $cm);
            if ($view_generator) {
                $activity->certificate_content = $view_generator->generate_html();
            }
        }

        return $activity;
    }

    /**
     * @param $attempt
     * @param $certificate
     * @param $course
     *
     * @return stdClass
     */
    private function create_certification_issue($attempt, $certificate, $course): stdClass {
        $issued = new stdClass();
        $issued->issue_date = userdate($attempt->timecreated);
        $issued->grade = '';
        if ($certificate->printgrade) {
            $issued->grade = certificate_get_grade($certificate, $course);
        }

        return $issued;
    }

    /**
     * @inheritDoc
     */
    public function get_total_download_size(): int {
        return $this->get_activity_size_by_area(['intro']);
    }
}