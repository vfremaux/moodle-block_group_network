<?php

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

require_once ($CFG->libdir.'/formslib.php');

class block_group_network_single_form extends moodleform {

    function definition() {
        global $CFG, $COURSE, $DB;
 
        $mform =& $this->_form;

        // calculating platform field

        $mnethost = $DB->get_record('mnet_host', array('id' => $this->_customdata['mnethostid']));

        preg_match('/http:\/\/(.*?)\./', $mnethost->wwwroot, $matches);
        $hostradical = str_replace('-', '', $matches[1]);
        $fieldname = 'access'.strtoupper($hostradical);

        if (!($netfield = $DB->get_record('user_info_field', array('shortname' => $fieldname)))) {
            print_error('accessnetworknotinitialized','block_group_network');
        }

        $mform->addElement('hidden', 'fieldid', $netfield->id);
        $mform->setType('fieldid', PARAM_INT);
        $mform->addElement('hidden', 'platformid', $this->_customdata['mnethostid']);
        $mform->setType('platformid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        // We select every student in the course and will compare them to the network possibilities

        $context = context_block::instance($this->_customdata['blockid']);
        $coursecontext = context_course::instance($COURSE->id);

        if ($users = get_enrolled_users($coursecontext)) {

            $authorisedusers = array();
            $unauthorisedusers = array();
            foreach ($users as $auser) {
                if (has_capability('block/group_network:manageaccess', $coursecontext, $auser->id, false)) {
                    continue;
                }

                $tag = $DB->get_record('user_info_data', array('userid' => $auser->id, 'fieldid' => $netfield->id));
                if ($tag && ($tag->data == 1)) {
                    $authorisedusers[$auser->id] = fullname($auser);
                } else {
                    $unauthorisedusers[$auser->id] = fullname($auser);
                }
            }

            $mform->addElement('html', '<center><table width="400" class="accessformlabels"><tr><td align="center"><b>'.get_string('enabled', 'block_group_network'). '</b></td><td align="center"><b>'.get_string('disabled', 'block_group_network').'</b></td></tr></table></center>');

            $group1 = array();
            $group1[0] = & $mform->createElement('select', 'authorized', get_string('authorized', 'block_group_network'), $authorisedusers, array('size' => 10, 'style' => 'width:200px'));
            $group1[1] = & $mform->createElement('select', 'unauthorized', get_string('unauthorized', 'block_group_network'), $unauthorisedusers, array('size' => 10, 'style' => 'width:200px'));
            $group1[0]->setMultiple(true);
            $group1[1]->setMultiple(true);
            $mform->addGroup($group1, 'accessess', get_string('accessinstructions', 'block_group_network'), '', false);

            $group2 = array();
            $group2[0] = & $mform->createElement('submit', 'disableusers', get_string('disable', 'block_group_network'));
            $group2[1] = & $mform->createElement('submit', 'enableusers', get_string('enable', 'block_group_network'));
            $group2[2] = & $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
            $mform->addGroup($group2, 'submits', '', '', false);

        } else {
            $mform->addElement('static', 'static1', get_string('nostudents', 'block_group_network'));
        }

    }

    function validation($data, $files = array()) {
        $errors = array();

        return $errors;
    }
}
