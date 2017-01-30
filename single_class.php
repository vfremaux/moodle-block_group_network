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
 * @package     block_groupnetwork
 * @category    block
 * @author      Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright   valery.fremaux (http://www.mylearningfactory.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');

class block_group_network_single_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE, $DB;
 
        $mform =& $this->_form;

        // Calculating platform field.

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

        // We select every student in the course and will compare them to the network possibilities.

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

            $elementhtml = '<center>';
            $elementhtml .= '<table width="400" class="accessformlabels">';
            $elementhtml .= '<tr>';
            $elementhtml .= '<td align="center">';
            $elementhtml .= '<b>'.get_string('enabled', 'block_group_network'). '</b>';
            $elementhtml .= '</td>';
            $elementhtml .= '<td align="center">';
            $elementhtml .= '<b>'.get_string('disabled', 'block_group_network').'</b>';
            $elementhtml .= '</td>';
            $elementhtml .= '</tr>'
            $elementhtml .= '</table>';
            $elementhtml .= '</center>';

            $mform->addElement('html', $elementhtml);

            $group1 = array();
            $label = get_string('authorized', 'block_group_network');
            $attrs = array('size' => 10, 'style' => 'width:200px');
            $group1[0] = & $mform->createElement('select', 'authorized', $label, $authorisedusers, $attrs);

            $label = get_string('unauthorized', 'block_group_network');
            $attrs = array('size' => 10, 'style' => 'width:200px');
            $group1[1] = & $mform->createElement('select', 'unauthorized', $label, $unauthorisedusers, $attrs);

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
}
