<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_readinglist configuration form.
 *
 * @package     mod_readinglist
 * @copyright   2020 Hugo Soares <h.soares@chi.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/readinglist/lib.php');
require_once($CFG->libdir . '/filelib.php');
/**
 * Module instance settings form.
 *
 * @package    mod_readinglist
 * @copyright  2020 Hugo Soares <h.soares@chi.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_readinglist_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition()
    {
        global $CFG, $COURSE, $DB, $PAGE;
        $mform = $this->_form;
        $current = $this->current;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('readinglistname', 'mod_readinglist'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'readinglistname', 'mod_readinglist');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();


        // Adding the rest of mod_readinglist settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        // $mform->addElement('static', 'label1', 'readinglistsettings', get_string('readinglistsettings', 'mod_readinglist'));
        // $mform->addElement('header', 'readinglistfieldset', get_string('readinglistfieldset', 'mod_readinglist'));

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
