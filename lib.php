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
 * @package block_group_network
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

define('GROUP_NETWORK_ENABLE_MEMBERS', 2);
define('GROUP_NETWORK_DISABLE_MEMBERS', 1);

function access_process_data_single($data) {
    global $DB;

    if (!empty($data->enableusers) && !empty($data->unauthorized)) {
        foreach ($data->unauthorized as $toenable) {
            $rec = new StdClass;
            $rec->userid = $toenable;
            $rec->fieldid = $data->fieldid;
            $rec->data = 1;

            if (!$oldrec = $DB->get_record('user_info_data', array('userid' => $toenable, 'fieldid' => $data->fieldid))) {
                $DB->insert_record('user_info_data', $rec);
            } else {
                $rec->id = $oldrec->id;
                $DB->update_record('user_info_data', $rec);
            }
        }
    }

    if (!empty($data->disableusers) && !empty($data->authorized)) {
        foreach($data->authorized as $todisable) {
            $DB->delete_records('user_info_data', array('userid' => $todisable, 'fieldid' => $data->fieldid));
        }
    }
}

function access_process_data_group($data, &$theBlock) {
    global $COURSE, $DB;

    $context = context_block::instance($theBlock->id);

    $groupstates = preg_grep('/^group/', array_keys((array)$data));

    foreach($groupstates as $groupkey) {

        $groupid = str_replace('group', '', $groupkey);
        $members = groups_get_members($groupid, 'u.id, firstname');

        if ($data->$groupkey == GROUP_NETWORK_ENABLE_MEMBERS) {

            foreach($members as $member) {
                if (has_capability('block/group_network:manageaccess', $context)) {
                    continue;
                }
                $DB->delete_records('user_info_data', array('userid' => $member->id, 'fieldid' => $data->fieldid));
                $rec = new StdClass;
                $rec->userid = $member->id;
                $rec->fieldid = $data->fieldid;
                $rec->data = 1;
                $DB->insert_record('user_info_data', $rec);
            }

        } elseif ($data->$groupkey == GROUP_NETWORK_DISABLE_MEMBERS) {
            foreach ($members as $member) {
                $DB->delete_records('user_info_data', array('userid' => $member->id, 'fieldid' => $data->fieldid));
            }
        }
    }
}
