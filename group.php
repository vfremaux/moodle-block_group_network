<?php

/**
 * This page will print the form to list users enroled in the course
 * The teacher will then be able to give/remove networking possibilities
 * @package block-groupnet
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @version Moodle 2
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

	require_once('../../config.php');
	require_once('group_class.php');
	require_once('lib.php');

    $courseid       = required_param('courseid', PARAM_INT);
    $platformid     = optional_param('platformid', 0, PARAM_INT);
    $blockid       = required_param('blockid', PARAM_INT);

    if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))){
        print_error('blockinvalidid');
    }
    $theBlock = block_instance('group_network', $instance);
	$context = context_block::instance($instance->id);

    if (! $course = $DB->get_record('course', array('id' =>  $courseid)) ) {
        print_error('invalidcourseid');
    }
    
/// Security     

	require_login($course);
    require_capability('block/group_network:manageaccess', context_block::instance($blockid));

    global $subname;

    $platassign = get_string('listallplatforms', 'block_group_network');
    $straction = get_string('netrole', 'block_group_network');
    $fullstr = get_string('single_full', 'block_group_network');

	$PAGE->navbar->add($course->fullname,"$CFG->wwwroot/course/view.php?id=$courseid", 'misc');
	$PAGE->navbar->add($straction, null, 'misc');
 
	$PAGE->set_title($fullstr);
	$PAGE->set_heading($SITE->fullname);
    $url = new moodle_url('/blocks/group_network/group.php');
    $PAGE->set_url($url, array());
    echo $OUTPUT->header();

	echo '<p>';
	include 'targetchoice.php';
	echo '</p>';

	$mygroups = groups_get_user_groups($COURSE->id, $USER->id);
	
	if (empty($mygroups) && !has_capability('moodle/site:accessallgroups', context_system::instance())){
		$OUTPUT->notification(get_string('nogroupaccess', 'block_group_network'));
		echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid), get_string('backtocourse','block_group_network')));
		return;
	} else {
		$mygroups = groups_get_all_groups($COURSE->id);
		if (empty($mygroups)){
    		$OUTPUT->notification(get_string('nogroups', 'block_group_network'));
    		echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse','block_group_network')), 'get';
    		return;
		}
	}

	if ($platformid){
	    $form = new block_group_network_group_form($platformid, $mygroups, $instance);
	
		if ($form->is_cancelled()){
			redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
		} elseif($data = $form->get_data()){
			access_process_data_group($data, $instance);
		}
	    $form = new block_group_network_group_form($platformid, $mygroups, $instance); // need renew form
	    $form->display();
	}

    echo '<div class="butarray" align="center"><p><hr/>';
	echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse', 'block_group_network'), 'get');
    echo '</p></div>';
    echo $OUTPUT->footer($course);

?>