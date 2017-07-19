<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package     local
 * @subpackage  filecleaner
 * @copyright   2017 benIT
 * @author      benIT <benoit.works@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('classes/FileCleaner.php');
defined('MOODLE_INTERNAL') || die();
$performDateUpdate = optional_param('performDateUpdate', false, PARAM_BOOL);
$context = context_system::instance();
require_login();
require_capability('local/filecleaner:manage', $context);
global $PAGE;
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_filecleaner'));
$PAGE->set_heading(get_string('title', 'local_filecleaner'));
$PAGE->set_pagelayout("standard");
$PAGE->set_url("/local/filecleaner");
echo $OUTPUT->header();
$fileCleaner = new FileCleaner();
echo html_writer::tag('p', get_string('sentence', 'local_filecleaner'));
if ($performDateUpdate) {
    $fileCleaner->resetFileslastcleanup(time() - 60 * 60 * 24 * 365);
    echo html_writer::tag('p', get_string('done', 'local_filecleaner'));

} else {
    echo '<a class="btn btn-primary" href="?performDateUpdate=true">'.get_string('button', 'local_filecleaner').'</a>';
}
echo html_writer::tag('p', get_string('lastCleanUpSetting', 'local_filecleaner', $fileCleaner->getFileslastcleanup(true)));
echo $OUTPUT->footer();