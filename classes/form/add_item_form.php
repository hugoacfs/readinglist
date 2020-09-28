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

namespace mod_readinglist\form;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

use MoodleQuickForm;

class add_item_form extends \moodleform
{

    function definition()
    {
        global $OUTUT;
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // error_log("DEBUG: " . print_r($options, true));
        $addnewstr = get_string('form_add' . $customdata['type'], 'mod_readinglist');
        $mform->addElement('header', 'general', $addnewstr);
        // Item title for mdl_readinglist_item
        $mform->addElement(
            'text',
            'title',
            get_string('form_title', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        // Item url for mdl_readinglist_item
        $mform->addElement(
            'text',
            'url',
            get_string('form_url', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $mform->setType('url', PARAM_URL);
        // Item publish year for mdl_readinglist_item
        $mform->addElement(
            'text',
            'year',
            get_string('form_yearofpublish', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $mform->setType('year', PARAM_INT);
        // Add extra form stuff
        switch ($customdata['type']) {
            case 'book':
                $this->display_book_form($mform);
                break;
            case 'article':
                $this->display_article_form($mform);
                break;
            case 'website':
                $this->display_website_form($mform);
                break;
            default:
                break;
        }

        // Move to function for BOOK
        // $mform->addElement('text', 'isbn', 'ISBN');
        // $mform->setType('isbn', PARAM_TEXT);
        // // $mform->addRule('isbn', get_string('maximumchars', '', 11), 'maxlength', 11, 'client');
        // $mform->addRule('isbn', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'rid', $customdata['rid']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'cmid', $customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'type', $customdata['type']);
        $mform->setType('type', PARAM_TEXT);

        $this->add_action_buttons(true);

        $this->set_data($formdata);
    }

    function display_book_form($form)
    {
        $form->addElement('header', 'additional_info', get_string('form_additionalinfo', 'mod_readinglist'));
        // ISBN
        $form->addElement(
            'text',
            'isbn',
            get_string('form_isbn', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('isbn', PARAM_TEXT);
        // ISBN BUTTON
        $form->addElement('button', 'isbn_btn', get_string("form_isbnbutton", 'mod_readinglist'));
        $form->setType('isbn_btn', PARAM_TEXT);
        // Edition
        $form->addElement(
            'text',
            'edition',
            get_string('form_edition', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('edition', PARAM_TEXT);
        // Page Range
        $form->addElement(
            'text',
            'pagerange',
            get_string('form_pagerange', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('pagerange', PARAM_TEXT);
        // Chapter
        $form->addElement(
            'text',
            'chapter',
            get_string('form_chapter', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('chapter', PARAM_TEXT);
    }

    function display_article_form($form)
    {
        $form->addElement('header', 'additional_info', get_string('form_additionalinfo', 'mod_readinglist'));
        // journal
        $form->addElement(
            'text',
            'journal',
            get_string('form_journal', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('journal', PARAM_TEXT);
        // volume
        $form->addElement(
            'text',
            'volume',
            get_string('form_volume', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('volume', PARAM_TEXT);
        // issue
        $form->addElement(
            'text',
            'issue',
            get_string('form_issue', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('issue', PARAM_TEXT);
        // pagerange
        $form->addElement(
            'text',
            'pagerange',
            get_string('form_pagerange', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('pagerange', PARAM_TEXT);
        // doi
        $form->addElement(
            'text',
            'doi',
            get_string('form_doi', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('doi', PARAM_TEXT);
    }
    function display_website_form($form)
    {

        $form->addElement('header', 'additional_info', get_string('form_additionalinfo', 'mod_readinglist'));
        // name
        $form->addElement(
            'text',
            'name',
            get_string('form_websitename', 'mod_readinglist'),
            ['size' => '30', 'maxlength' => '255']
        );
        $form->setType('name', PARAM_TEXT);
        $form->addRule('url', null, 'required', null, 'client');
        $form->addElement('button', 'url_btn', get_string("form_urlbutton", 'mod_readinglist'));
        $form->setType('url_btn', PARAM_TEXT);
    }
}
