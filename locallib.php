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

use stdClass, single_button, moodle_url;

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
    $success = false;
    if ($newitemid > 0) {//should check if it's not false?? TODO: improve
        // If item inserted successfully
        $success = create_data_attempt($newitemid, $data, $data->type);
        if(!$success) {
            $params = ['newitemid' => $newitemid];
            $DB->delete_records_select('readinglist_item',"id = :newitemid", $params);
        }
    }
    if ($success) {
        //link to readinglist TODO: improve this
        link_item_to_readinglist($newitemid, $data->rid);
    }
    return $success;
}

function create_data_attempt(int $itemid, $data, string $type) {
    if (!$type) {
        return true;
    }
    global $DB;
    $newbook = [];
    foreach ($data as $key => $value) {
        $newbook[$key] = $value;
    }
    $newinstanceid = $DB->insert_record('readinglist_' . $type, $newbook);
    $success = false;
    if ($newinstanceid > 0) {//should check if it's not false?? TODO: improve
        $success = update_item_instanceid_attempt($itemid, $newinstanceid);
        if (!$success) {
            $params = ['newinstanceid' => $newinstanceid];
            $DB->delete_records_select('readinglist_' . $type,"id = :newinstanceid", $params);
        }
        return $success;
    }
    return $success;
}

function update_item_instanceid_attempt(int $itemid, int $newinstanceid) {
    global $DB;
    $changeditem = new stdClass();
    $changeditem->id = $itemid;
    $changeditem->instanceid = $newinstanceid;
    return $DB->update_record('readinglist_item', $changeditem);
}

function link_item_to_readinglist(int $itemid, int $readinglistid) {
    global $DB;
    $newlist = new stdClass();
    $newlist->itemid = $itemid;
    $newlist->readinglistid = $readinglistid;
    $newlistid = $DB->insert_record('readinglist_list', $newlist);
    return $newlistid;
}

function confirm_item_removal (int $itemid, int $readinglistid, object $output, string $baseurlstr, array $baseurlparams) {
    global $DB;
    $item = $DB->get_record('readinglist_item', ['id' => $itemid], '*', MUST_EXIST);
    $list = $DB->get_record('readinglist_list', ['readinglistid' => $readinglistid, 'itemid' => $itemid], '*', MUST_EXIST);
    echo $output->header();
    echo $output->heading(get_string('remove_item_confirm', 'mod_readinglist', $item->title));

    $returnurl = new moodle_url($baseurlstr, $baseurlparams);
    $removedurlparams = $baseurlparams;
    $removedurlparams['action'] = 'removed';
    $removedurlparams['listid'] = $list->id;
    $removedurl = new moodle_url($baseurlstr, $removedurlparams);
    $removebutton = new single_button($removedurl, get_string('remove'), 'get');

    echo $output->confirm(get_string('remove_item_confirm_box', 'mod_readinglist', $item->title), $removebutton, $returnurl);
    echo $output->footer();
}

function display_current_list (object $readinglist, array $items, int $cmid, int $instanceid, object $output) {
    $templatecontext = (object)[
        'readinglistname' => $readinglist->name,
        'numberofentries' => count($items),
        'items' => array_values($items),
        'addbookurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'book']),
        'addarticleurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'article']),
        'addwebsiteurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'website']),
        'selecturl'  => new moodle_url('/mod/readinglist/view.php', ['id' => $instanceid, 'cmid' => $cmid, 'action' => 'select', 'rlid' => $readinglist->id])
    ];
    echo $output->render_from_template('mod_readinglist/view', $templatecontext);
    echo $output->footer();
}

function inspect_item_by_id (object $readinglist,int $itemid, int $cmid, int $courseid, object $output) {
    global $DB;
    $item = $DB->get_record('readinglist_item', ['id' => $itemid]);
    $instance = $DB->get_record('readinglist_' . $item->type, ['id' => $item->instanceid]);
    $itemarray = combine_data_item($item, $instance);
    // var_dump($itemarray);die;
    $templatecontext = (object)[
        'readinglistname' => $readinglist->name,
        'item' => $itemarray,
        'addbookurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'book']),
        'addarticleurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'article']),
        'addwebsiteurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'website']),
        'selecturl'  => new moodle_url('/mod/readinglist/view.php', ['id' => $courseid, 'cmid' => $cmid, 'action' => 'select', 'rlid' => $readinglist->id])
    ];
    echo $output->render_from_template('mod_readinglist/inspect', $templatecontext);
    echo $output->footer();
}

function select_item_from_list (object $readinglist, int $cmid, int $courseid, object $output) {
    global $DB;
    $sql =  "SELECT `item`.`id`, `item`.`title`, `item`.`url`, `item`.`type`, `item`.`year`, 
                    `book`.`isbn`, `book`.`edition`, `book`.`chapter`,
                    `article`.`journal`, `article`.`volume`, `article`.`issue`, `article`.`doi`,
                    `website`.`name`,
                        CONCAT(COALESCE(`book`.`pagerange`, ''), COALESCE(`article`.`pagerange`, '')) AS `pagerange`
            FROM {readinglist_item} AS `item`
                LEFT JOIN {readinglist_website} AS `website` 
                    ON `item`.`instanceid` = `website`.`id` 
                    AND `item`.`type` = 'website'
                LEFT JOIN {readinglist_article} AS `article` 
                    ON `item`.`instanceid` = `article`.`id` 
                    AND `item`.`type` = 'article'
                LEFT JOIN {readinglist_book} AS `book` 
                    ON `item`.`instanceid` = `book`.`id` 
                    AND `item`.`type` = 'book';";
    $allitems = (array) $DB->get_records_sql($sql);
    // echo'<pre>';var_dump($allitems);echo'</pre>';die;
    $templatecontext = (object)[
        'readinglistname' => $readinglist->name,
        'allitems' => array_values($allitems),
        'numberofentries' => count($allitems),
        'addbookurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'book']),
        'addarticleurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'article']),
        'addwebsiteurl'  => new moodle_url('/mod/readinglist/add_item.php', ['cmid' => $cmid, 'type' => 'website']),
        'selecturl'  => new moodle_url('/mod/readinglist/view.php', ['id' => $courseid, 'cmid' => $cmid, 'action' => 'select', 'rlid' => $readinglist->id])
    ];
    echo $output->render_from_template('mod_readinglist/showall', $templatecontext);
    echo $output->footer();
}

function combine_data_item (object $item, object $data) : array {
    $item = (array) $item;
    $data = (array) $data;
    foreach ($data as $key => $value) {
        if ($key != 'id') {
            $item[$key] = $value;
        }
    }
    return $item;
}