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

// defined('MOODLE_INTERNAL') || die;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/filelib.php');

$url = $_POST['url'] ?? '';
// Curl to fetch HTML
$c = new curl;
$html = $c->get($url);
$html = (string) $html;
// Fetching Metas from HTML
$doc = new DomDocument();
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);

$query = '//meta';
$metas_result = $xpath->query($query);
$metas_array = [];
// Building arrays
foreach ($metas_result as $meta) {
    $property = $meta->getAttribute('property');
    $content = $meta->getAttribute('content');
    $metas_array[$property] = $content;
}
// Getting more tags
$more_tags = get_meta_tags($url, 1);
// Combining tags
$all_tags = array_merge($more_tags, $metas_array);

$title = $all_tags['og:title'] ?? $all_tags['title'] ?? $all_tags['twitter:title'] ?? ''; //Tries to find meta tags, if it fails, fallsback on title tag
$title = trim($title);
$name = $all_tags['og:site_name'] ?? $all_tags['site_name'] ?? '';

$year = '';
$publishdate = $all_tags['article:published_time'] ?? $all_tags['article:published_time'] ?? '';
$timestamp = 0;
if ($publishdate) $timestamp = (new DateTime($publishdate))->getTimestamp();
if ($timestamp) $year = date('Y', $timestamp);

$data_object = new stdClass;
$data_object->title = (string) $title;
$data_object->name = (string) $name;
$data_object->year = (string) $year;
$data_object->alltags = $all_tags;

echo json_encode($data_object);
