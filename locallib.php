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

function create_item_attempt($data) {
    global $CFG, $DB, $USER, $OUTPUT;

    $readinglist = $DB->get_record('readinglist', ['id' => $data->rid]);
    if (!$readinglist) {
        echo $OUTPUT->notification('Reading List does not exist.');
        return false;
    }

    // $newitem is an array for readinglist_item table
    $newitem = [];
    $newitem['title'] = $data->title;
    $newitem['url'] = $data->url;
    $newitem['type'] = $data->type;
    $newitem['year'] = $data->year;
    // $newitem['dataid'] = null; // No need to do this, DB does this already

    $newitemid = $DB->insert_record('readinglist_item', $newitem);

    if ($newitemid > 0) {//should check if it's not false?? TODO: improve
        // If item inserted successfully
        switch ($data->type){
            case 'book':
                create_book_attempt($newitemid, $data);
                break;
            case 'article':
                create_article_attempt($newitemid, $data);
                break;
            case 'website':
                create_website_attempt($newitemid, $data);
                break;
            default:
                break;
        }
    }

    // if (!$newitemid | !$addtoreadinglist){
    //     return false; //false on failure to add both entries
    // }

    return true;
}

function create_book_attempt(int $itemid, $data) {
    global $DB;
    $newbook = [];
    $newbook['isbn'] = $data->isbn;
    $newbook['edition'] = $data->edition;
    $newbook['pagerange'] = $data->pagerange;
    $newbook['chapter'] = $data->chapter;
    $newbookid = $DB->insert_record('readinglist_book', $newbook);
    if ($newbookid > 0) {//should check if it's not false?? TODO: improve
        $success = update_item_dataid_attempt($itemid, $newbookid);
        if (!$success) {
            echo '';
            // if not success here need to:
                //1. remove item
                //2. throw exception or print error
        }
        return $success;
    }
    // add something to handle exception
    return false;
}

function create_article_attempt(int $itemid, $data) {
    return;
}

function create_website_attempt(int $itemid, $data) {
    return;
}

function update_item_dataid_attempt(int $itemid, int $newdataid) {
    global $DB;
    $changeditem = new stdClass();
    $changeditem->id = $itemid;
    $changeditem->dataid = $newdataid;
    return $DB->update_record('readinglist_item', $changeditem);
}