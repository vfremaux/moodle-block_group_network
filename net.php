<?PHP

/**
 * This is the processing part of the network assign
 * This page is called by the forms defined in single_class.php
 *
 * @package block-groupnet
 * @category block
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require_once('../../config.php');
require_once('single_class.php');

    $userid         = required_param('userid', PARAM_INT); // needed for user tabs
    $courseid       = required_param('courseid', PARAM_INT); // needed for user tabs
    $platformid     = required_param('platformid', PARAM_INT); //this is not the real platform id, it the the field id corresponding to it
    $actionid       = required_param('actionid', PARAM_INT); //actionid : 1 for add    //  2 for remove //  3 for group add //  4 for group remove
    $fieldid	    = required_param('fieldid', PARAM_INT); //id of the custom profile field

    $course = $DB->get_record('course', array('id' => $courseid));

    if (! $course = $DB->get_record('course', array('id' => $courseid)) ) {
        print_error('invalidcourseid');
    }

    $straction = get_string('netrole', 'block_group_network');
    $user = $DB->get_record('user', array('id' => $userid));
    $fullstr = get_string('single_full','block_group_network');

	$PAGE->navbar->add($course->fullname,"$CFG->wwwroot/course/view.php?id=$courseid", 'misc');
	$PAGE->navbar->add($straction, null, 'misc');
 
	$PAGE->set_title($fullstr);
	$PAGE->set_heading($SITE->fullname);
    $url = new moodle_url('/blocks/group_network/net.php');
    $PAGE->set_url($url, array());
    $OUTPUT->header($fullstr, $shortstr, $navigation, '', '', false, '');

    $action = optional_param('action', '' PARAM_TEXT);

    //Lets give some feedback to the user so he knows what is going on

    echo $OUTPUT->box($OUTPUT->notification(get_string('actionnotification', 'block_group_network', $action)));

    //Here we are going to switch on the actionid, to do the required actions.

    switch($actionid){
	
//	ADD CASE
	case 1:
	    $todo = optional_param_array('local', '', PARAM_INT);
		
		//Now the action
		foreach($todo as $studentid){
			$user = $DB->get_record('user', array('id' => $studentid));
			$uid->userid = $studentid;
			$uid->fieldid = $fieldid;
			$uid->data =  1; // we enforce the 1 because the relative field is a checkbox that needs to be checked
			$DB->insert_record('user_info_data', $uid);
			echo $OUTPUT->box(get_string('usergranted', 'block_group_network', fullname($user)));
		}
		break;

//	REMOVE CASE
	case 2:
		$todo = optional_param_array('net', array(), PARAM_INT);

		//Now the action
		foreach($todo as $studentid){		
			$user = $DB->get_record('user', array('id' => $studentid));
			$DB->delete_records('user_info_data', array('userid' => $studentid, 'fieldid' => $fieldid));
			echo $OUTPUT->box(get_string('userrevoked', 'block_group_network', fullname($user));			
		}
	    break;

	case 3:
		$todo = optional_param_array('local', array(), PARAM_INT);

		foreach($todo as $groupid){
			
			$group = groups_get_group($groupid);

			$record->groupid = $groupid;
			$record->platformid = $platformid;
 			$DB->insert_record('block_group_network', $record);

			$groupmembers = $DB->get_records('groups_members', array('groupid' => $groupid));

			foreach($groupmembers as $student){
			    $studentid = $student->userid;
			    $uid->userid = $studentid;
			    $uid->fieldid = $fieldid;
			    $uid->data => 1; // we enforce the 1 because the relative field is a checkbox that needs to be checked
			    $DB->insert_record('user_info_data', $uid);
				echo $OUTPUT->box(get_string('usergranted', 'block_group_network', fullname($user)));
			}
			echo $OUTPUT->box(get_string('groupcomplete', 'block_group_network', $group->name);
		  }
	      break;

	case 4:

		$todo = optional_param_array('net', array(), PARAM_INT);

		foreach($todo as $groupid){

			$group = groups_get_group($groupid);

			$groupmembers = $DB->get_records('groups_members', array('groupid' => $groupid));

			foreach($groupmembers as $student){
				$studentid = $student->userid;
				$user = $DB->get_record('user', array('id' => $studentid));
				$DB->delete_records('user_info_data', array('userid' => $studentid, 'fieldid' => $fieldid));
				echo $OUTPUT->box(get_string('userrevoked', 'block_group_network', fullname($user));			
		  	}

			$DB->delete_records('block_group_network', array('groupid' => $groupid, 'platformid' => $platformid));

			echo $OUTPUT->box(get_string('groupcomplete', 'block_group_network', $group->name);			   
		}
		break;

//	NOT TO HAPPEN => Wrong id
	default:
	     print_error('erroraction', 'block_group_network');
    }

    echo '<div class="butarray" align="center">';
    echo $OUTPUT->single_button(new moodle_url('/course/view.php',array('id' => $courseid)),get_string('backtocourse','block_group_network'));
    echo '</div>';
    $OUTPUT->footer($COURSE);

?>