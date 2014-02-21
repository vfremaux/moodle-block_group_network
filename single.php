<?PHP

/**
 * This page will print the form to list users enroled in the course
 * The teacher will then be able to give/remove networking possibilities
 * @package block-groupnet
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

require_once('../../config.php');
require_once('single_class.php');
require_once('lib.php');

    $courseid       = required_param('courseid', PARAM_INT); // needed for return
    $platformid     = optional_param('platformid', 0, PARAM_INT);
    $blockid        = required_param('blockid', PARAM_INT);
    
    if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))){
        print_error('blockinvalidid');
    }
    $theBlock = block_instance('group_network', $instance);
	$context = context_block::instance($theBlock->instance->id);

    if (! $course = $DB->get_record('course', array('id' => $courseid)) ) {
        print_error('invalidcourseid');
    }

/// Security     

	require_login($course);
    require_capability('block/group_network:manageaccess', $context);

    global $subname;

    $platassign = get_string('listallplatforms', 'block_group_network');
    $straction = get_string('netrole', 'block_group_network');
    $fullstr = get_string('single_full', 'block_group_network');
    $shortstr = '';

	$PAGE->navbar->add($course->fullname,"$CFG->wwwroot/course/view.php?id=$courseid", 'misc');
	$PAGE->navbar->add($straction, null, 'misc');
 
	$PAGE->set_title($fullstr);
	$PAGE->set_heading($SITE->fullname);
    $url = new moodle_url('/blocks/group_network/single.php');
    $PAGE->set_url($url, array());
    echo $OUTPUT->header();

	echo '<p>';
	include 'targetchoice.php';
	echo '</p>';

	if ($platformid){
		$custom['mnethostid'] = $platformid;
		$custom['blockid'] = $blockid;
		$singleurl = new moodle_url($CFG->wwwroot.'/blocks/group_network/single.php', array('blockid' => $blockid, 'courseid' => $COURSE->id));
	    $form = new block_group_network_single_form($singleurl, $custom);
	
		if ($form->is_cancelled()){
			redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
		} elseif($data = $form->get_data()){
			access_process_data_single($data, $instance);
		}
	    $form = new block_group_network_single_form($singleurl, $custom); // to reload forms tate after changes

	    echo '<center>';
	    $form->display();
	    echo '</center>';
	}

    echo '<div class="returntocourse" align="center"><p><hr/>';
    echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)),get_string('backtocourse','block_group_network'), 'get');
    echo '</p></div>';
    echo $OUTPUT->footer($course);

