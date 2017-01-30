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
 * This form is only accessible by an editing teacher and will display the enroled user in the course.
 * The teacher will then be able to give networking access to selected users by changing a checkbox status
 * that is hidden in their profile
 *
 * @package     block_group_network
 * @category    blocks
 * @author      Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright   Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php');

class block_group_network_group_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE, $USER, $DB;

        $mform =& $this->_form;

        // Calculating platform field.

        $mnethost = $DB->get_record('mnet_host', array('id' => $this->_customdata['mnethostid']));
        $fieldname = user_mnet_hosts_get_accesskey($mnethost->wwwroot, true);

        if (!($netfield = $DB->get_record('user_info_field', array('shortname' => $fieldname)))) {
            print_error('accessnetworknotinitialized', 'block_group_network');
        }

        $mform->addElement('hidden', 'platformid', $this->_customdata['mnethostid']);
        $mform->setType('platformid', PARAM_INT);

        $mform->addElement('hidden', 'fieldid', $netfield->id);
        $mform->setType('fieldid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'blockid', $this->_customdata['instance']->id);
        $mform->setType('blockid', PARAM_INT);

        // We select every student in the course and will compare them to the network possibilities.

        $coursecontext = context_course::instance($COURSE->id);
        $context = context_block::instance($this->_customdata['instance']->id);

        $groupauthorisations = array();
        $authorisedgroups = array();
        $haveauthorisedgroups = array();
        $unauthorisedgroups = array();
        $groupsize = array();
        $groupmembers = array();

        foreach ($this->_customdata['groups'] as $group) {

            if (!isset($groupauthorisations[$group->id])) {
                $groupauthorisations[$group->id] = 0;
                $groupsize[$group->id] = 0;
            }

            if ($members = groups_get_members($group->id)) {
                foreach ($members as $amember) {
                    if (has_capability('block/group_network:manageaccess', $context, $amember->id)) {
                        continue;
                    }
                    $tag = $DB->get_record('user_info_data', array('userid' => $amember->id, 'fieldid' => $netfield->id));
                    if ($tag && ($tag->data == 1)) {
                        $groupauthorisations[$group->id]++;
                        $class = "authorized";
                    } else {
                        $class = "unauthorized";
                    }
                    $groupsize[$group->id]++;
                    $groupmembers[$group->id][] = '<span class="'.$class.'">'.fullname($amember).'</span>';
                }
            }
        }

        $mform->addElement('header', '', get_string('networkauthorizations', 'block_group_network'));
        foreach ($this->_customdata['groups'] as $group) {
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
            $groupmemberlist = (!empty($groupmemberlist)) ? "($groupmemberlist)" : '';
            $grouplabel = $grouppicture.' '.$group->name.'<br/>'.$groupmemberlist;

            $radioarr = array();
            $label = get_string('disableall', 'block_group_network');
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', $label, GROUP_NETWORK_DISABLE_MEMBERS, array());
            $label = get_string('partial', 'block_group_network');
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', $label, 0, array('disabled' => 1));
            $label = get_string('enableall', 'block_group_network');
            $radioarr[] = & $mform->createElement('radio', 'group'.$group->id, '', $label, GROUP_NETWORK_ENABLE_MEMBERS, array());
            $mform->setDefault('group'.$group->id, $groupdefault);
            $mform->addGroup($radioarr, 'groupaccess'.$group->id, $grouplabel, array('&nbsp;&nbsp;&nbsp;'), false);
        }

        $mform->addElement('header', '', '');

        $group2 = array();
        $group2[] = & $mform->createElement('submit', 'perform', get_string('process', 'block_group_network'));
        $group2[] = & $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($group2, 'submits', '', array('&nbsp;&nbsp;'), false);
    }

    /**
     * Here we can check the available groups access state
     */
    public function set_data($defaults) {
        global $DB;

        $mnethost = $DB->get_record('mnet_host', array('id' => $this->_customdata['mnethostid']));

        if (!empty($this->_customdata['groups'])) {
            foreach($this->_customdata['groups'] as $group) {
                $defaults['group'.$groupid] = user_mnet_hosts_check_group_access($group->id, $mnethost->wwwroot);
            }
        }
    }
}
