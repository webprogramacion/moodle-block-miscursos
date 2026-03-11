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
 * Main block class for Mis cursos.
 *
 * @package   block_miscursosdashboard
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_miscursosdashboard extends block_base {
    /**
     * Initializes the block metadata.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_miscursosdashboard');
    }

    /**
     * Restricts where the block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'my' => true,
        ];
    }

    /**
     * Allows more than one instance if needed.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Allows per-instance configuration.
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Applies a custom title if the admin saved one.
     *
     * @return void
     */
    public function specialization() {
        if (!empty($this->config) && !empty($this->config->title)) {
            $this->title = format_string($this->config->title);
        } else {
            $this->title = get_string('pluginname', 'block_miscursosdashboard');
        }
    }

    /**
     * Returns block content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $OUTPUT, $PAGE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = html_writer::div(
                get_string('noguestview', 'block_miscursosdashboard'),
                'miscursosdashboard__empty'
            );
            return $this->content;
        }

        $sort = optional_param('bmdsort', 'name', PARAM_ALPHA);
        if (!in_array($sort, ['name', 'enrolment'], true)) {
            $sort = 'name';
        }

        $direction = optional_param('bmddir', 'asc', PARAM_ALPHA);
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $visibility = optional_param('bmdvisibility', 'active', PARAM_ALPHA);
        if (!in_array($visibility, ['active', 'all'], true)) {
            $visibility = 'active';
        }

        $layoutmode = isset($this->config->layoutmode) ? (string)$this->config->layoutmode : 'list';
        if (!in_array($layoutmode, ['list', 'grid'], true)) {
            $layoutmode = 'list';
        }

        $showteachers = isset($this->config->showteachers) ? (bool)$this->config->showteachers : false;
        $showstart = isset($this->config->showenrolstart) ? (bool)$this->config->showenrolstart : true;
        $showend = isset($this->config->showenrolend) ? (bool)$this->config->showenrolend : true;

        $records = \block_miscursosdashboard\local\course_service::get_user_courses_with_enrolment_data($USER->id);
        $records = $this->filter_courses_by_completion($records, $visibility);
        $courses = $this->sort_courses($records, $sort, $direction);

        $params = $PAGE->url->params();
        unset($params['bmdsort'], $params['bmddir'], $params['bmdvisibility']);

        $hiddenparams = [];
        foreach ($params as $name => $value) {
            if (is_scalar($value)) {
                $hiddenparams[] = [
                    'name' => $name,
                    'value' => (string)$value,
                ];
            }
        }

        $isgridlayout = $layoutmode === 'grid';

        $templatedata = [
            'header' => get_string('mycoursesheader', 'block_miscursosdashboard'),
            'formaction' => $PAGE->url->out_omit_querystring(),
            'hiddenparams' => $hiddenparams,
            'sortlabel' => get_string('sortby', 'block_miscursosdashboard'),
            'visibilitylabel' => get_string('coursevisibility', 'block_miscursosdashboard'),
            'directionlabel' => get_string('direction', 'block_miscursosdashboard'),
            'applylabel' => get_string('applysort', 'block_miscursosdashboard'),
            'sortid' => 'bmdsort-' . $this->instance->id,
            'visibilityid' => 'bmdvisibility-' . $this->instance->id,
            'dirid' => 'bmddir-' . $this->instance->id,
            'sortoptions' => [
                [
                    'value' => 'name',
                    'label' => get_string('sortbyname', 'block_miscursosdashboard'),
                    'selected' => $sort === 'name',
                ],
                [
                    'value' => 'enrolment',
                    'label' => get_string('sortbyenrolment', 'block_miscursosdashboard'),
                    'selected' => $sort === 'enrolment',
                ],
            ],
            'visibilityoptions' => [
                [
                    'value' => 'active',
                    'label' => get_string('showactivecourses', 'block_miscursosdashboard'),
                    'selected' => $visibility === 'active',
                ],
                [
                    'value' => 'all',
                    'label' => get_string('showallcourses', 'block_miscursosdashboard'),
                    'selected' => $visibility === 'all',
                ],
            ],
            'directionoptions' => [
                [
                    'value' => 'asc',
                    'label' => get_string('ascending', 'block_miscursosdashboard'),
                    'selected' => $direction === 'asc',
                ],
                [
                    'value' => 'desc',
                    'label' => get_string('descending', 'block_miscursosdashboard'),
                    'selected' => $direction === 'desc',
                ],
            ],
            'isenrolmentsort' => $sort === 'enrolment',
            'showteachers' => $showteachers,
            'showenrolstart' => $showstart,
            'showenrolend' => $showend,
            'isgridlayout' => $isgridlayout,
            'layoutclass' => $isgridlayout ? 'miscursosdashboard__list--grid' : 'miscursosdashboard__list--list',
            'enrolstartlabel' => get_string('enrolstartdate', 'block_miscursosdashboard'),
            'enrolendlabel' => get_string('enrolenddate', 'block_miscursosdashboard'),
            'coursecompletedlabel' => get_string('coursecompletedlabel', 'block_miscursosdashboard'),
            'nodata' => get_string('nodata', 'block_miscursosdashboard'),
            'nocourses' => empty($courses),
            'nocoursesmessage' => get_string('nocourses', 'block_miscursosdashboard'),
            'courses' => $this->export_courses_for_template($courses, $isgridlayout, $showteachers, $visibility === 'all'),
        ];

        $this->content->text = $OUTPUT->render_from_template('block_miscursosdashboard/content', $templatedata);
        return $this->content;
    }

    /**
     * Sorts course records according to selected options.
     *
     * @param array $courses
     * @param string $sort
     * @param string $direction
     * @return array
     */
    private function sort_courses(array $courses, string $sort, string $direction): array {
        usort($courses, function($a, $b) use ($sort, $direction): int {
            if ($sort === 'enrolment') {
                $atime = $a->enrolstart;
                $btime = $b->enrolstart;

                if ($atime === null && $btime !== null) {
                    return 1;
                }
                if ($atime !== null && $btime === null) {
                    return -1;
                }
                if ($atime !== null && $btime !== null) {
                    $comparison = $atime <=> $btime;
                    if ($direction === 'desc') {
                        $comparison *= -1;
                    }
                    if ($comparison !== 0) {
                        return $comparison;
                    }
                }
            }

            $namecomparison = strcmp(
                core_text::strtolower($a->course->fullname),
                core_text::strtolower($b->course->fullname)
            );
            if ($sort === 'name' && $direction === 'desc') {
                $namecomparison *= -1;
            }
            return $namecomparison;
        });

        return $courses;
    }

    /**
     * Filters courses by course end date according to selected visibility.
     *
     * @param array $courses
     * @param string $visibility
     * @return array
     */
    private function filter_courses_by_completion(array $courses, string $visibility): array {
        if ($visibility === 'all') {
            return $courses;
        }

        $now = time();

        return array_values(array_filter($courses, function($record) use ($now): bool {
            $enddate = isset($record->course->enddate) ? (int)$record->course->enddate : 0;
            return $enddate <= 0 || $enddate >= $now;
        }));
    }

    /**
     * Converts internal records to Mustache-friendly values.
     *
     * @param array $courses
     * @param bool $includeimages
     * @param bool $showteachers
     * @param bool $showcompletionbadge
     * @return array
     */
    private function export_courses_for_template(
        array $courses,
        bool $includeimages,
        bool $showteachers,
        bool $showcompletionbadge
    ): array {
        $results = [];
        $imagesbycourse = [];
        $teachersbycourse = [];
        $now = time();

        $courseids = [];
        foreach ($courses as $record) {
            $courseids[] = (int)$record->course->id;
        }

        if ($includeimages) {
            $imagesbycourse = $this->get_course_images_for_courses($courseids);
        }

        if ($showteachers) {
            $teachersbycourse = $this->get_course_teachers_for_courses($courseids);
        }

        foreach ($courses as $record) {
            $courseid = (int)$record->course->id;
            $fullname = format_string($record->course->fullname);
            $enddate = isset($record->course->enddate) ? (int)$record->course->enddate : 0;
            $iscoursefinished = $enddate > 0 && $enddate < $now;
            $imageurl = '';
            $hasrealimage = false;
            if ($includeimages && !empty($imagesbycourse[$courseid])) {
                $imageurl = $imagesbycourse[$courseid];
                $hasrealimage = true;
            }

            $teacherstext = '';
            $teacherslabel = '';
            $hasteachers = false;
            if ($showteachers && !empty($teachersbycourse[$courseid])) {
                $teacherstext = implode(', ', $teachersbycourse[$courseid]);
                $teacherslabel = count($teachersbycourse[$courseid]) === 1
                    ? get_string('teacherslabelsingular', 'block_miscursosdashboard')
                    : get_string('teacherslabelplural', 'block_miscursosdashboard');
                $hasteachers = true;
            }

            $results[] = [
                'fullname' => $fullname,
                'url' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),
                'showcoursecompleted' => $showcompletionbadge && $iscoursefinished,
                'hasimage' => $includeimages,
                'hasrealimage' => $hasrealimage,
                'imageurl' => $imageurl,
                'imagealt' => get_string('courseimagealt', 'block_miscursosdashboard', $fullname),
                'teacherstext' => $teacherstext,
                'teacherslabel' => $teacherslabel,
                'hasteachers' => $hasteachers,
                'hasenrolstart' => $record->enrolstart !== null,
                'hasenrolend' => $record->enrolend !== null,
                'enrolstartformatted' => $record->enrolstart !== null
                    ? userdate($record->enrolstart, get_string('strftimedatetime', 'langconfig'))
                    : '',
                'enrolendformatted' => $record->enrolend !== null
                    ? userdate($record->enrolend, get_string('strftimedatetime', 'langconfig'))
                    : '',
            ];
        }

        return $results;
    }

    /**
     * Returns first overview image URLs keyed by course id.
     *
     * @param int[] $courseids
     * @return array
     */
    private function get_course_images_for_courses(array $courseids): array {
        global $DB;

        $courseids = array_values(array_unique(array_map('intval', $courseids)));
        if (empty($courseids)) {
            return [];
        }

        [$coursesql, $courseparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
        $contextparams = ['contextlevel' => CONTEXT_COURSE] + $courseparams;

        $contextsql = "SELECT id, instanceid
                         FROM {context}
                        WHERE contextlevel = :contextlevel
                          AND instanceid {$coursesql}";
        $contexts = $DB->get_records_sql($contextsql, $contextparams);
        if (empty($contexts)) {
            return [];
        }

        $contextids = [];
        $coursebycontext = [];
        foreach ($contexts as $context) {
            $contextids[] = (int)$context->id;
            $coursebycontext[(int)$context->id] = (int)$context->instanceid;
        }

        [$contextsqlin, $contextinparams] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
        $fileparams = [
            'component' => 'course',
            'filearea' => 'overviewfiles',
            'itemid' => 0,
            'filepath' => '/',
        ] + $contextinparams;

        $filesql = "SELECT id, contextid, filename, mimetype
                      FROM {files}
                     WHERE contextid {$contextsqlin}
                       AND component = :component
                       AND filearea = :filearea
                       AND itemid = :itemid
                       AND filepath = :filepath
                       AND filename <> '.'
                  ORDER BY sortorder, id";
        $files = $DB->get_records_sql($filesql, $fileparams);

        $result = [];
        foreach ($files as $file) {
            $contextid = (int)$file->contextid;
            $courseid = $coursebycontext[$contextid] ?? null;

            if (!$courseid || isset($result[$courseid])) {
                continue;
            }

            if (strpos((string)$file->mimetype, 'image/') !== 0) {
                continue;
            }

            $url = moodle_url::make_pluginfile_url(
                $contextid,
                'course',
                'overviewfiles',
                null,
                '/',
                $file->filename
            );
            $result[$courseid] = $url->out(false);
        }

        return $result;
    }

    /**
     * Returns teachers keyed by course id.
     *
     * @param int[] $courseids
     * @return array
     */
    private function get_course_teachers_for_courses(array $courseids): array {
        global $DB;

        $courseids = array_values(array_unique(array_map('intval', $courseids)));
        if (empty($courseids)) {
            return [];
        }

        [$coursesql, $courseparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cidt');
        $contextparams = ['contextlevel' => CONTEXT_COURSE] + $courseparams;

        $contextsql = "SELECT id, instanceid
                         FROM {context}
                        WHERE contextlevel = :contextlevel
                          AND instanceid {$coursesql}";
        $contexts = $DB->get_records_sql($contextsql, $contextparams);
        if (empty($contexts)) {
            return [];
        }

        $contextids = [];
        $coursebycontext = [];
        foreach ($contexts as $context) {
            $contextid = (int)$context->id;
            $contextids[] = $contextid;
            $coursebycontext[$contextid] = (int)$context->instanceid;
        }

        $roles = $DB->get_records_list('role', 'shortname', ['editingteacher', 'teacher'], '', 'id');
        if (empty($roles)) {
            return [];
        }

        $roleids = array_map('intval', array_keys($roles));
        [$contextsqlin, $contextinparams] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ct');
        [$rolesqlin, $roleinparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'rt');
        $params = $contextinparams + $roleinparams;

        $sql = "SELECT ra.contextid,
                       u.id,
                       u.firstname,
                       u.lastname,
                       u.middlename,
                       u.alternatename,
                       u.firstnamephonetic,
                       u.lastnamephonetic,
                       r.sortorder
                  FROM {role_assignments} ra
                  JOIN {user} u ON u.id = ra.userid
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE ra.contextid {$contextsqlin}
                   AND ra.roleid {$rolesqlin}
                   AND u.deleted = 0
              ORDER BY r.sortorder, u.lastname, u.firstname, u.id";
        $rows = $DB->get_records_sql($sql, $params);

        $result = [];
        $seen = [];
        foreach ($rows as $row) {
            $contextid = (int)$row->contextid;
            $courseid = $coursebycontext[$contextid] ?? null;
            if (!$courseid) {
                continue;
            }

            $userid = (int)$row->id;
            if (isset($seen[$courseid][$userid])) {
                continue;
            }

            if (!isset($result[$courseid])) {
                $result[$courseid] = [];
            }
            if (!isset($seen[$courseid])) {
                $seen[$courseid] = [];
            }

            $result[$courseid][] = fullname($row);
            $seen[$courseid][$userid] = true;
        }

        return $result;
    }
}
