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
 * This is the processing part of the network assign
 * This page is called by the forms defined in single_class.php
 *
 * @package block_group_network
 * @category block
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
use block_group_network\controller\net_controller;

require('../../config.php');
require_once($CFG->dirroot.'/blocks/group_network/single_form.php');

$userid         = required_param('userid', PARAM_INT); // needed for user tabs
$courseid       = required_param('courseid', PARAM_INT); // needed for user tabs
$platformid     = required_param('platformid', PARAM_INT); //this is not the real platform id, it the the field id corresponding to it
$actionid       = required_param('actionid', PARAM_INT); //actionid : 1 for add    //  2 for remove //  3 for group add //  4 for group remove
$fieldid        = required_param('fieldid', PARAM_INT); //id of the custom profile field

if (! $course = $DB->get_record('course', array('id' => $courseid)) ) {
    print_error('invalidcourseid');
}

// Security.

$course = $DB->get_record('course', array('id' => $courseid));
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/group_network:manageaccess', $context);

$straction = get_string('netrole', 'block_group_network');
$user = $DB->get_record('user', array('id' => $userid));
$fullstr = get_string('single_full','block_group_network');

$PAGE->navbar->add($course->fullname,new moodle_url('/course/view.php', array('id' => $courseid)), 'misc');
$PAGE->navbar->add($straction, null, 'misc');

$PAGE->set_title($fullstr);
$PAGE->set_heading($SITE->fullname);
$url = new moodle_url('/blocks/group_network/net.php');
$PAGE->set_url($url, array());

$action = optional_param('action', '', PARAM_TEXT);

if ($action) {
    require_once($CFG->dirroot.'/blocks/group_network/net.controller.php');
    $controller = new net_controller();
    $output = $controller->process($action, '', array('platformid' => $platformid));
}

//Here we are going to switch on the actionid, to do the required actions.

$OUTPUT->header();

echo $OUTPUT->box($OUTPUT->notification(get_string('actionnotification', 'block_group_network', $action)));

if (!empty($output)) {
    echo $OUTPUT->box($output);
}

echo '<div class="butarray" align="center">';
echo $OUTPUT->single_button(new moodle_url('/course/view.php',array('id' => $courseid)),get_string('backtocourse','block_group_network'));
echo '</div>';
$OUTPUT->footer($COURSE);