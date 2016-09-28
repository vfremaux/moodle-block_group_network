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

defined('MOODLE_INTERNAL') || die();

/**
 * This form is only accessible by an editing teacher and will display the enroled user in the course.
 * The teacher will then be able to give networking access to selected users by changing a checkbox status
 * that is hidden in their profile
 *
 * @package block_group_network
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php');

class block_group_network_single_form extends moodleform {

    function definition() {
        global $CFG, $COURSE, $DB;
 
        $mform =& $this->_form;

        // calculating platform field.

        $mnethost = $DB->get_record('mnet_host', array('id' => $this->_customdata['mnethostid']));

        $fieldname = user_mnet_hosts_get_accesskey($mnethost->wwwroot, true);

        if (!($netfield = $DB->get_record('user_info_field', array('shortname' => $fieldname)))) {
            print_error('accessnetworknotinitialized', 'block_group_network');
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

        if ($this->_customdata['users']) {

            $authorisedusers = array();
            $unauthorisedusers = array();
            foreach ($this->_customdata['users'] as $auser) {
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
            $mform->addGroup($group1, 'accessess', '', '&nbsp;&nbsp;', false);

            $group2 = array();
            $group2[0] = & $mform->createElement('submit', 'disableusers', get_string('disable', 'block_group_network'));
            $group2[1] = & $mform->createElement('submit', 'enableusers', get_string('enable', 'block_group_network'));
            $group2[2] = & $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
            $mform->addGroup($group2, 'submits', '', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', false);

        } else {
            $mform->addElement('static', 'static1', get_string('nostudents', 'block_group_network'));
        }
    }

    function validation($data, $files = array()) {
        $errors = array();

        return $errors;
    }
}
