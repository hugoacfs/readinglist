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
$itemid = required_param('itemid', PARAM_INT);

if (!$cm = get_coursemodule_from_id('readinglist', $cmid)) {
    print_error('invalidcoursemodule');
}

$readinglist = $DB->get_record('readinglist', ['id' => $cm->instance], '*', MUST_EXIST);

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

$item = $DB->get_record('readinglist_item', ['id' => $itemid], '*', MUST_EXIST);
$itemdata = (array) $item;

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

//require_capability('mod/readinglist:view', $context); //TODO: FIX

$PAGE->set_url('/mod/readinglist/remove_item.php', ['cmid' => $cm->id]);

$PAGE->set_title($course->shortname.': '. $readinglist->name);
$PAGE->set_heading($course->fullname);
// $output = $PAGE->get_renderer('mod_readinglist');
echo $OUTPUT->header();
echo $OUTPUT->heading($readinglist->name);

$templatecontext = (object)[
    'readinglistname' => $readinglist->name,
    'item' => $itemdata,
    'addbookurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'book')),
    'addarticleurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'article')),
    'addwebsiteurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'website')),
    'selecturl'  => new moodle_url('/mod/readinglist/find.php', array('id' => $course->id, 'rlid' => $readinglist->id))
];

echo $OUTPUT->render_from_template('mod_readinglist/remove_item', $templatecontext);

echo $OUTPUT->footer();
