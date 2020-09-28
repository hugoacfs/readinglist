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
// \mod_readinglist\create_item_attempt // Implement BOOK DB https://openlibrary.org/isbn/xxxxxxxxxxx.json ?
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Preparing Environment -> Gathering objects and making db queries
$id = required_param('id', PARAM_INT); // Instance ID
$action = optional_param('action', null, PARAM_TEXT);
$itemid = optional_param('itemid', null, PARAM_INT);
$readinglistid = optional_param('readinglistid', null, PARAM_INT);
$listid = optional_param('listid', null, PARAM_INT);
if (!$cm = get_coursemodule_from_id('readinglist', $id)) {
    print_error('invalidcoursemodule');
}
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$readinglist = $DB->get_record('readinglist', ['id' => $cm->instance], '*', MUST_EXIST);
$thisurlstr = '/mod/readinglist/view.php';
$baseurlparams = ['id' => $id, 'cmid' => $cm->id, 'readinglistid' => $readinglist->id];
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
$pagenotifications = []; // Format ['timecreated' => ['success' => 'message']]
// $pagenotifications[time()] = (object) ['type' => 'success', 'message' => 'this is great'];
// var_dump($pagenotifications);die;
// Avoid code above this line ----------------------------------------------------------------

if ($action == 'removed' and $listid) {
    $removedsuccess = $DB->delete_records("readinglist_list", ['id' => $listid]);
    $messagetype = 'success';
    $messagestr = get_string('remove_item_successful', 'mod_readinglist');
    if (!$removedsuccess) {
        $messagetype = 'warning';
        $messagestr = get_string('remove_item_couldnot', 'mod_readinglist');
    }
    $pagenotifications[time()] = (object) [
        'type' => 'success',
        'message' => get_string('remove_item_successful', 'mod_readinglist')
    ];
}

if ($action == 'remove' and $itemid and $readinglistid) {
    \mod_readinglist\confirm_item_removal($itemid, $readinglistid, $OUTPUT, $thisurlstr, $baseurlparams);
    die;
}

$list = $DB->get_records('readinglist_list', ['readinglistid' => $readinglist->id]);
$params = [];
foreach ($list as $link) {
    $params[] = $link->itemid;
}
$items = [];
$items = $DB->get_records_list('readinglist_item', 'id', $params);

foreach ($items as $key => $value) {
    $value->removeurl = new moodle_url($viewurlstr,  ['id' => $id, 'cmid' => $cm->id, 'action' => 'remove', 'itemid' => $value->id, 'readinglistid' => $readinglist->id]);
    $value->inspecturl = new moodle_url($viewurlstr,  ['id' => $id, 'cmid' => $cm->id, 'action' => 'inspect', 'itemid' => $value->id, 'readinglistid' => $readinglist->id]);
}

$PAGE->set_url('/mod/readinglist/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($readinglist->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->js_call_amd('mod_readinglist/readinglist', 'init');

echo $OUTPUT->header();
// display notifications first
foreach ($pagenotifications as $key => $object) {
    echo $OUTPUT->notification($object->message, $object->type);
}

if ($action != 'inspect' and $action != 'select') {
    \mod_readinglist\display_current_list($readinglist, $items, $cm->id, $id, $OUTPUT);
    die;
} elseif ($action == 'inspect' and $itemid) {
    \mod_readinglist\inspect_item_by_id($readinglist, $itemid, $cm->id, $id, $OUTPUT);
    die;
} elseif ($action == 'select') {
    \mod_readinglist\select_item_from_list($readinglist, $cm->id, $id, $OUTPUT);
    die;
}