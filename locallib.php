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

namespace mod_readinglist;

defined('MOODLE_INTERNAL') || die();

use stdClass;

function create_book_attempt($data) {
    global $CFG, $DB, $USER, $OUTPUT;

    $readinglist = $DB->get_record('readinglist', ['id' => $data->rid]);
    if (!$readinglist) {
        echo $OUTPUT->notification('Reading List does not exist.');
        return false;
    }

    // $newbook = new stdClass();
    $newbook = array();
    $newbook['title'] = $data->title;
    $newbook['isbn'] = $data->isbn;
    // $newbook->timecreated = time();
    // $newbook->timecompleted = time();

    $newbookid = $DB->insert_record('readinglist_book', $newbook);

    if ($newbookid > 0) {//should check if it's not false?? TODO: improve
        $addbook = array();
        $addbook['activityid'] = $data->rid;
        $addbook['bookid'] = $newbookid;
        $addtoreadinglist = $DB->insert_record('readinglist_list', $addbook);
    }

    if (!$newbookid | !$addtoreadinglist){
        return false; //false on failure to add both entries
    }

    return true;
}