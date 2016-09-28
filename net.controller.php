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

namespace block_group_network\controller;

defined('MOODLE_INTERNAL') || die();

/**
 * This is the processing part of the network assign
 * This page is called by the forms defined in single_class.php
 *
 * @package block_group_network
 * @category block
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

class net_controller {

    function process($action, $form = null, $environment) {
        global $DB, $OUTPUT;

        $str = '';

        switch($action) {

            //    ADD CASE
            case 1:
                $todo = optional_param_array('local', '', PARAM_INT);
                $fieldid = required_param('fieldid', PARAM_INT); //id of the custom profile field

                //Now the action
                foreach ($todo as $studentid) {
                    $user = $DB->get_record('user', array('id' => $studentid));
                    $uid = new \Stdclass();
                    $uid->userid = $studentid;
                    $uid->fieldid = $fieldid;
                    $uid->data =  1; // we enforce the 1 because the relative field is a checkbox that needs to be checked
                    $DB->insert_record('user_info_data', $uid);
                    $str .= $OUTPUT->box(get_string('usergranted', 'block_group_network', fullname($user)));
                }
                break;

            //    REMOVE CASE
            case 2:
                $todo = optional_param_array('net', array(), PARAM_INT);
                $fieldid = required_param('fieldid', PARAM_INT); //id of the custom profile field

                //Now the action
                foreach ($todo as $studentid) {
                    $user = $DB->get_record('user', array('id' => $studentid));
                    $DB->delete_records('user_info_data', array('userid' => $studentid, 'fieldid' => $fieldid));
                    $str .= $OUTPUT->box(get_string('userrevoked', 'block_group_network', fullname($user)));
                }
                break;

            case 3:
                $todo = optional_param_array('local', array(), PARAM_INT);
                $fieldid = required_param('fieldid', PARAM_INT); //id of the custom profile field
                $groupid = required_param('groupid', PARAM_INT); //id of the custom profile field

                foreach ($todo as $groupid) {

                    $group = groups_get_group($groupid);

                    $record = new \StdClass();
                    $record->groupid = $groupid;
                    $record->platformid = $platformid;
                    $DB->insert_record('block_group_network', $record);

                    $groupmembers = $DB->get_records('groups_members', array('groupid' => $groupid));

                    foreach ($groupmembers as $student) {
                        $studentid = $student->userid;
                        $uid = new \StdClass();
                        $uid->userid = $studentid;
                        $uid->fieldid = $fieldid;
                        $uid->data = 1; // we enforce the 1 because the relative field is a checkbox that needs to be checked
                        $DB->insert_record('user_info_data', $uid);
                        $str .= $OUTPUT->box(get_string('usergranted', 'block_group_network', fullname($user)));
                    }
                    $str .= $OUTPUT->box(get_string('groupcomplete', 'block_group_network', $group->name));
                }
                break;

            case 4:

                $todo = optional_param_array('net', array(), PARAM_INT);
                $fieldid = required_param('fieldid', PARAM_INT); //id of the custom profile field
                $groupid = required_param('groupid', PARAM_INT); //id of the custom profile field

                foreach ($todo as $groupid) {

                    $group = groups_get_group($groupid);

                    $groupmembers = $DB->get_records('groups_members', array('groupid' => $groupid));

                    foreach ($groupmembers as $student) {
                        $studentid = $student->userid;
                        $user = $DB->get_record('user', array('id' => $studentid));
                        $DB->delete_records('user_info_data', array('userid' => $studentid, 'fieldid' => $fieldid));
                        $str .= $OUTPUT->box(get_string('userrevoked', 'block_group_network', fullname($user)));
                    }

                    $DB->delete_records('block_group_network', array('groupid' => $groupid, 'platformid' => $platformid));

                    $str .= $OUTPUT->box(get_string('groupcomplete', 'block_group_network', $group->name));
                }
                break;

            //    NOT TO HAPPEN => Wrong id
            default:
                 print_error('erroraction', 'block_group_network');
        }

        return $str;
   }
}
