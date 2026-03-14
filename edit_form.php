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
 * Instance configuration form for block_miscursosdashboard.
 *
 * @package   block_miscursosdashboard
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_miscursosdashboard_edit_form extends block_edit_form {
    /**
     * Creates specific block instance settings.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('configheader', 'block_miscursosdashboard'));

        $mform->addElement('text', 'config_title', get_string('customtitle', 'block_miscursosdashboard'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->addHelpButton('config_title', 'customtitle', 'block_miscursosdashboard');

        $layoutoptions = [
            'list' => get_string('layoutlist', 'block_miscursosdashboard'),
            'grid' => get_string('layoutgrid', 'block_miscursosdashboard'),
        ];
        $mform->addElement('select', 'config_layoutmode', get_string('layoutmode', 'block_miscursosdashboard'), $layoutoptions);
        $mform->setDefault('config_layoutmode', 'list');

        $mform->addElement(
            'advcheckbox',
            'config_showteachers',
            get_string('showteachers', 'block_miscursosdashboard')
        );
        $mform->setDefault('config_showteachers', 0);

        $mform->addElement(
            'advcheckbox',
            'config_showenrolstart',
            get_string('showenrolstart', 'block_miscursosdashboard')
        );
        $mform->setDefault('config_showenrolstart', 1);

        $mform->addElement(
            'advcheckbox',
            'config_showenrolend',
            get_string('showenrolend', 'block_miscursosdashboard')
        );
        $mform->setDefault('config_showenrolend', 1);
    }
}
