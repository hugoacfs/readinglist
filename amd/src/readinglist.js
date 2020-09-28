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

define(['jquery', 'core/str', 'core/log'], function($, str) {

    var init = function() {
        reset();
    };

    var reset = function() {
        var noselection = str.get_string('noselection', 'mod_readinglist');

        $.when(noselection).done(function(localstring) {
            var noselectionstring = localstring;
            $('.filter_form [type="reset"]').on('click', function() {
                var autocompletes = $('[data-fieldtype=autocomplete]');
                autocompletes.each(function(i, autocomplete) {
                    $(autocomplete).find('select option').each(function(ai, option) {
                        $(option).removeAttr('selected');
                    });
                    $(autocomplete).find('span.tag').each(function(ti, t) {
                        $(t).attr('data-value', '').text(noselectionstring);
                    });
                });
            });
        });
    };

    return {
        init: init
    };
 });