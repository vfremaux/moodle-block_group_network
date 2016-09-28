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
 * This page will print the form to list users enroled in the course
 * The teacher will then be able to give/remove networking possibilities
 *
 * @package block_group_network
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/group_network/single_form.php');
require_once($CFG->dirroot.'/blocks/group_network/lib.php');

$courseid = required_param('courseid', PARAM_INT); // needed for return
$platformid = optional_param('platformid', 0, PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('blockinvalidid');
}

$theblock = block_instance('group_network', $instance);
$context = context_block::instance($theblock->instance->id);

if (!$course = $DB->get_record('course', array('id' => $courseid)) ) {
    print_error('invalidcourseid');
}

$coursecontext = context_course::instance($course->id);

// Security.

require_login($course);
require_capability('block/group_network:manageaccess', $context);

global $subname;

$platassign = get_string('listallplatforms', 'block_group_network');
$straction = get_string('netrole', 'block_group_network');
$fullstr = get_string('single_full', 'block_group_network');
$shortstr = '';

$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $courseid)), 'misc');
$PAGE->navbar->add($straction, null, 'misc');

$PAGE->set_title($fullstr);
$PAGE->set_heading($SITE->fullname);
$url = new moodle_url('/blocks/group_network/single.php');
$PAGE->set_url($url, array());

$users = get_enrolled_users($coursecontext);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('netrole', 'block_group_network'));

if (empty($users)) {
    echo $OUTPUT->box($OUTPUT->notification(get_string('nousers', 'block_gorup_network')));
    echo '<div class="returntocourse" align="center"><p><hr/>';
    echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse','block_group_network'), 'get');
    echo '</p></div>';
    echo $OUTPUT->footer();
    die;
}

$choicemode = 'single';
echo '<div id="targetchoice">';
include($CFG->dirroot.'/blocks/group_network/targetchoice.php');
echo '</div>';

if ($platformid) {
    $custom['mnethostid'] = $platformid;
    $custom['blockid'] = $blockid;
    $custom['users'] = $users;
    $singleurl = new moodle_url('/blocks/group_network/single.php', array('blockid' => $blockid, 'courseid' => $COURSE->id));
    $form = new block_group_network_single_form($singleurl, $custom);

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } elseif ($data = $form->get_data()) {
        access_process_data_single($data, $instance);
    }

    echo $OUTPUT->box_start('info', 'instructions');
    echo get_string('accessinstructions', 'block_group_network');
    echo $OUTPUT->box_end();

    $form = new block_group_network_single_form($singleurl, $custom); // to reload forms tate after changes

    echo '<center>';
    $form->display();
    echo '</center>';
}

echo '<div class="returntocourse" align="center"><p><hr/>';
echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse','block_group_network'), 'get');
echo '</p></div>';
echo $OUTPUT->footer($course);
