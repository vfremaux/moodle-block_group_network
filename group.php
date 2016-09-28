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
 * @package block_group_network
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * This page will print the form to list users enroled in the course
 * The teacher will then be able to give/remove networking possibilities
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/group_network/group_form.php');
require_once($CFG->dirroot.'/blocks/group_network/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$platformid = optional_param('platformid', 0, PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('blockinvalidid');
}
$theblock = block_instance('group_network', $instance);
$context = context_block::instance($instance->id);
$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

if (! $course = $DB->get_record('course', array('id' =>  $courseid)) ) {
    print_error('invalidcourseid');
}

// Security.

require_login($course);
require_capability('block/group_network:manageaccess', context_block::instance($blockid));

$platassign = get_string('listallplatforms', 'block_group_network');
$straction = get_string('netrole', 'block_group_network');
$fullstr = get_string('single_full', 'block_group_network');

$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $courseid)), 'misc');
$PAGE->navbar->add($straction, null, 'misc');

$PAGE->set_title($fullstr);
$PAGE->set_heading($SITE->fullname);
$url = new moodle_url('/blocks/group_network/group.php');
$PAGE->set_url($url, array());

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('netrole', 'block_group_network'));

$choicemode = 'group';
echo '<div id="targetchoice">';
include($CFG->dirroot.'/blocks/group_network/targetchoice.php');
echo '</div>';

$mygroups = groups_get_user_groups($COURSE->id, $USER->id);

if (empty($mygroups) && !has_capability('moodle/site:accessallgroups', context_system::instance())) {
    $OUTPUT->notification(get_string('nogroupaccess', 'block_group_network'));
    echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid), get_string('backtocourse', 'block_group_network')));
    return;
} else {
    $mygroups = groups_get_all_groups($COURSE->id);
    if (empty($mygroups)){
        $OUTPUT->notification(get_string('nogroups', 'block_group_network'));
        echo $OUTPUT->single_button($courseurl, get_string('backtocourse', 'block_group_network')), 'get';
        return;
    }
}

if ($platformid) {
    $form = new block_group_network_group_form('', array('mnethostid' => $platformid, 'groups' => $mygroups, 'instance' => $instance));

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } elseif ($data = $form->get_data()) {
        access_process_data_group($data, $instance);
    }
    $custom = array('mnethostid' => $platformid, 'groups' => $mygroups, 'instance' => $instance);
    $form = new block_group_network_group_form('', $custom); // need renew form
    $form->display();
}

echo '<div class="butarray" align="center"><p><hr/>';
echo $OUTPUT->single_button($courseurl, get_string('backtocourse', 'block_group_network'), 'get');
echo '</p></div>';
echo $OUTPUT->footer($course);
