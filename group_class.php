<?PHP

/**
 * This form is only accessible by an editing teacher and will display the enroled user in the course.
 * The teacher will then be able to give networking access to selected users by changing a checkbox status
 * that is hidden in their profile
 *
 * @package block-groupnet
 * @category block
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once($CFG->libdir.'/formslib.php');

class block_group_network_group_form extends moodleform {
 
     var $mnethostid;
     var $groups;
     var $blockinstance;

     function __construct($mnethostid, &$groups, &$theBlock) {
         $this->mnethostid = $mnethostid;
         $this->groups = $groups;
         $this->blockinstance = $theBlock;
         parent::__construct();
     }
 
    function definition() {
        global $CFG, $COURSE, $USER, $DB;
 
        $mform =& $this->_form;
        
        // calculating platform field
        
        $mnethost = $DB->get_record('mnet_host', array('id' => $this->mnethostid));
        
        preg_match('/http:\/\/(.*?)\./', $mnethost->wwwroot, $matches);
        $hostradical = str_replace('-', '', $matches[1]);
        $fieldname = 'access'.strtoupper($hostradical);

        if(!($netfield = $DB->get_record('user_info_field', array('shortname' => $fieldname)))){
            print_error('accessnetworknotinitialized', 'block_group_network');
        }

        $mform->addElement('hidden', 'fieldid', $netfield->id);
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->addElement('hidden', 'blockid', $this->blockinstance->id);

        // We select every student in the course and will compare them to the network possibilities

        $coursecontext = context_course::instance($COURSE->id);
        $context = context_block::instance($this->blockinstance->id);
        
        $groupauthorisations = array();
        $authorisedgroups = array();
        $haveauthorisedgroups = array();
        $unauthorisedgroups = array();
        $groupsize = array();
        $groupmembers = array();

        foreach ($this->groups as $group) {

            if (!isset($groupauthorisations[$group->id])) {
                $groupauthorisations[$group->id] = 0;
                $groupsize[$group->id] = 0;
            }
            
            if ($members = groups_get_members($group->id)) {
                foreach ($members as $amember) {
                    if (has_capability('block/group_network:manageaccess', $context, $amember->id)) continue;
                    $tag = $DB->get_record('user_info_data', array('userid' => $amember->id, 'fieldid' => $netfield->id));
                    if($tag && ($tag->data == 1)){
                        $groupauthorisations[$group->id]++;
                        $class = "authorized";
                    } else {
                        $class = "unauthorized";
                    }
                    $groupsize[$group->id]++;
                    $groupmembers[$group->id][] = "<span class=\"$class\">".fullname($amember)."</span>";
                }
            }
        }

        $mform->addElement('header', '', get_string('networkauthorizations', 'block_group_network'));
        foreach ($this->groups as $group) {
            if ($groupauthorisations[$group->id] == $groupsize[$group->id]) {
                // full authorized
                $groupdefault = 2;
            } elseif ($groupauthorisations[$group->id] == 0) {
                // full unauthorized
                $groupdefault = 1;
            } else {
                // some are unauthorized but not all
                $groupdefault = 0;
            }

            $grouppicture = print_group_picture($group, $COURSE->id, false, true);
            $groupmemberlist = implode(', ', $groupmembers[$group->id]);
            $groupmemberlist = (!empty($groupmemberlist)) ? "($groupmemberlist)" : '' ;
            $grouplabel = $grouppicture. ' ' . $group->name.'<br/>'.$groupmemberlist;

            $radioarr = array();
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', get_string('disableall', 'block_group_network'), GROUP_NETWORK_DISABLE_MEMBERS, array());
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', get_string('partial', 'block_group_network'), 0, array('disabled' => 1));
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', get_string('enableall', 'block_group_network'), GROUP_NETWORK_ENABLE_MEMBERS, array());
            $mform->setDefault('group'.$group->id, $groupdefault);
            $mform->addGroup($radioarr, 'groupaccess'.$group->id, $grouplabel, array('&nbsp;&nbsp;&nbsp;'), false);
        }

        $mform->addElement('header', '', '');

        $group2 = array();
        $group2[] = & $mform->createElement('submit', 'perform', get_string('process', 'block_group_network'));
        $group2[] = & $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($group2, 'submits', '', array('&nbsp;&nbsp;'), false);
    }

    function validation($data) {
        $errors = array();
        
        return $errors;
    }
}     


?>