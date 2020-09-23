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

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Instance ID
$id = required_param('id', PARAM_INT);

if (!$cm = get_coursemodule_from_id('readinglist', $id)) {
    print_error('invalidcoursemodule');
}
$readinglist = $DB->get_record('readinglist', ['id' => $cm->instance], '*', MUST_EXIST);

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
// Events not working
// $event = \mod_readinglist\event\course_module_viewed::create(array(
//     'objectid' => $moduleinstance->id,
//     'context' => $modulecontext
// ));
// $event->add_record_snapshot('course', $course);
// $event->add_record_snapshot('readinglist', $moduleinstance);
// $event->trigger();

$PAGE->set_url('/mod/readinglist/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($readinglist->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$items = [];
$items = $DB->get_records('readinglist_item');
// Making things upper case - might want to remove TODO: review
foreach ($items as $index=>$object) {
    foreach ($object as $key=>$value) {
        if (is_string($value) && (strlen($value) > 0)) {
            $object->$key = ucfirst($value);
        }
    }
}

echo $OUTPUT->header();
$templatecontext = (object)[
    'readinglistname' => $readinglist->name,
    'numberofentries' => count($items),
    'items' => array_values($items),//replace array with array of books
    'addbookurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'book')),
    'addarticleurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'article')),
    'addwebsiteurl'  => new moodle_url('/mod/readinglist/add_item.php', array('cmid' => $cm->id, 'type' => 'website')),
    'selecturl'  => new moodle_url('/mod/readinglist/find.php', array('id' => $course->id, 'rlid' => $readinglist->id))
];

echo $OUTPUT->render_from_template('mod_readinglist/view', $templatecontext);

echo $OUTPUT->footer();
