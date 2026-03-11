<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Data service for dashboard courses.
 *
 * @package   block_miscursosdashboard
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_miscursosdashboard\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Retrieves enrolled courses and enrolment dates for one user.
 */
class course_service {
    /**
     * Returns enrolled courses with enrollment start/end dates.
     *
     * If a user has multiple enrolments in the same course, this method uses:
     * - The earliest non-empty start date as enrolment start.
     * - The latest non-empty end date as enrolment end.
     *
     * This is a practical summary for dashboard display and sorting.
     *
     * @param int $userid
     * @return array
     */
    public static function get_user_courses_with_enrolment_data(int $userid): array {
        global $DB;

        $courses = enrol_get_users_courses($userid, true, 'id,fullname,shortname');
        if (empty($courses)) {
            return [];
        }

        $courseids = array_keys($courses);
        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');

        $params = array_merge(['userid' => $userid], $inparams);
        $sql = "SELECT e.courseid,
                       MIN(CASE WHEN ue.timestart > 0 THEN ue.timestart ELSE NULL END) AS enrolstart,
                       MAX(CASE WHEN ue.timeend > 0 THEN ue.timeend ELSE NULL END) AS enrolend
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE ue.userid = :userid
                   AND e.courseid {$insql}
              GROUP BY e.courseid";

        $enrolmentrows = $DB->get_records_sql($sql, $params);

        $results = [];
        foreach ($courses as $course) {
            $row = (object)[
                'course' => $course,
                'enrolstart' => null,
                'enrolend' => null,
            ];

            if (isset($enrolmentrows[$course->id])) {
                $enrolment = $enrolmentrows[$course->id];
                if (!empty($enrolment->enrolstart)) {
                    $row->enrolstart = (int)$enrolment->enrolstart;
                }
                if (!empty($enrolment->enrolend)) {
                    $row->enrolend = (int)$enrolment->enrolend;
                }
            }

            $results[] = $row;
        }

        return $results;
    }
}
