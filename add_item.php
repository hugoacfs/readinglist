<?php
// This file is part of Readinglist Plugin
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
 * Readinglist plugin version info
 *
 * @package    mod_readinglist
 * @author     Hugo Soares <h.soares@chi.ac.uk> {@link www.github.com/hugoacfs}
 * @copyright  2020 University of Chichester {@link www.chi.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
use mod_readinglist\form\add_item_form;

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/mod/readinglist/lib.php');

// Course_module ID, or
$cmid = required_param('cmid', PARAM_INT);
$type = required_param('type', PARAM_TEXT);

if (!$cm = get_coursemodule_from_id('readinglist', $cmid)) {
    print_error('invalidcoursemodule');
}

$readinglist = $DB->get_record('readinglist', ['id' => $cm->instance], '*', MUST_EXIST);

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

//require_capability('mod/readinglist:view', $context); //TODO: FIX

$PAGE->set_url('/mod/readinglist/add_item.php', ['cmid' => $cm->id]);

$PAGE->set_title($course->shortname.': '. $readinglist->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($readinglist);
$PAGE->requires->js_call_amd('mod_readinglist/readinglist', 'init');

// $output = $PAGE->get_renderer('mod_readinglist');
echo $OUTPUT->header();
echo $OUTPUT->heading($readinglist->name);

$form = new add_item_form(null, ['rid'=>$readinglist->id, 'cmid'=>$cm->id, 'type'=>$type], 'post', '', ['class' => 'readinglist_form']);
if ($form->is_cancelled()) {
    // If it's cancelled, do nothing
}else if ($formdata = $form->get_data()) { //If data from form exists, do something
    // var_dump($formdata);
    $saved = \mod_readinglist\create_item_attempt($formdata); //TODO: Create this functionality
    $link = new moodle_url('/mod/readinglist/view.php', ['id' => $cm->id]);
    if ($saved) {// TODO: CHANGE link->out for actual string
        echo $OUTPUT->notification(get_string('add_item_successful', 'mod_readinglist', $formdata->title), 'success'); //TODO: Find out why link doesn't work?
    } else {
        // All warnings have already been printed.
        // Perhaps a redirect link.
        echo html_writer::link($link, "Return to view.");
    } //TODO: make this work maybe?
} else if (!empty($formdata->isbn_btn)) {
    echo 'success!';die;
}
else { // else, then do something else
    $form->display();
}

echo $OUTPUT->footer();
