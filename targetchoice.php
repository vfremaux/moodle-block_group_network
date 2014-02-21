<?php

if (!defined('MOODLE_INTERNAL')) die ("You cannot access this script directly");

//Warning that the changes arent localized to the course
    echo $OUTPUT->box(get_string('globalnetworking', 'block_group_network'));

//Creating the list of available hosts. Self/Deleted and All Hosts are ignored.
	$hosts = $DB->get_records_select_menu('mnet_host', " deleted = 0 AND name != '' AND name != 'All Hosts' ", array(''), 'name', 'id,name');
	
	// preselect a unique platform	
	if ($hosts && count($hosts) == 1){
		$hostids = array_keys($hosts);
		$platformid = $hostids[0];
	}

//Printing the platform adress and the subnet name

	//Printing the dropdown list
	if ($hosts){
	    echo '<div class="group-network-targetchoice" align="center">';
	    echo get_string('wheretoopen', 'block_group_network');
	    echo $OUTPUT->single_select($CFG->wwwroot."/blocks/group_network/single.php?courseid=$courseid&blockid={$blockid}", 'platformid', $hosts, $platformid);
	    echo '</div>';
	} else {
		echo $OUTPUT->notification(get_string('noplatform', 'block_group_network'));
	    echo '<div class="group-network-targetchoice" align="center">';
	    echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse','block_group_network'));
	    echo '</div>';
	    echo $OUTPUT->footer($course);
	    die;
	}
