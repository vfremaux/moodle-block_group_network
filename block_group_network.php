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
 * Declare the group_network block
 * The purpose of this block is to give a wizard to the teacher to
 * give his students access to other mnet platforms, using the protocols
 * from the user_mnet_hosts.
 * 
 * The wizard has 2 possible actions: either giving the required profil field to selected student
 * or give that field to a whole group of students inside the course.
 *
 * This block doesn't have any cron need.
 *
 * @package block_group_network
 * @category blocks
 * @author Edouard Poncelet (edouard.poncelet@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class block_group_network extends block_list {

    function init() {
        $this->title = get_string('group_network','block_group_network') ;
    }

/**
 * This block is not supposed to be configurable. It automatically provides the teacher with the two options.
 */
    function has_config() {
        return false;
    }

/**
 * This block is VERY specific. It can only be placed in a course by an editing teacher.
 */
    function applicable_formats() {
           return array('admin' => true, 'course-view' => true);
    }

/**
 * This block will display two links, wich will trigger the single or group wizards.
 */

    function get_content() {
        global $CFG, $COURSE, $USER, $DB, $OUTPUT;

        $context = context_course::instance($COURSE->id);

        // only for logged in users!
        if (!isloggedin() || isguestuser()) {
            return false;
        }

        if (!is_enabled_auth('mnet')) {
            // no need to query anything remote related
            debugging('mnet authentication plugin is not enabled', DEBUG_ALL);
            return '';
        }

        // Ignore deleted peers.
        $peers = $DB->get_records('mnet_host', array('deleted' => 0));

        // You need to have at least 3 hosts: You, AllHost and 1 Peer.
        if (!count($peers) >= 3) {
            print_error('errorpeernum', 'block_group_network');
            return '';
        }

        if ($this->content !== null) {
            return $this->content;
        }

        // The teacher is the ONLY ONE who should see content in this block.
        if (has_capability('block/group_network:manageaccess', $context)) {

            $this->content = new stdClass;
            $this->content->items = array();
            $this->content->icons = array();
            $this->content->footer = '';

            $contextid = $context->id;
            $userid = $USER->id;
            $courseid = $COURSE->id;

            // Creating the first link for "single" user networking.
            $singlewizard = get_string('single_netwizard', 'block_group_network');
            $icon  = '<img src="'.$OUTPUT->pix_url('/t/edit').'" class="icon" />';
            $singleurl = new moodle_url('/blocks/group_network/single.php', array('courseid' => $courseid, 'blockid' => $this->instance->id));
            $this->content->items[] = '<a href="'.$singleurl.'">'.$singlewizard.'</a><br/>';
            $this->content->icons[] = $icon;

            // Creating the link for the group version.
            $groups = groups_get_all_groups($COURSE->id);
            if (!empty($groups)) {
                $groupwizard = get_string('group_netwizard', 'block_group_network');
                $icon  = '<img src="'.$OUTPUT->pix_url('/i/users').'" class="icon" />';
                $groupurl = new moodle_url('/blocks/group_network/group.php', array('courseid' => $courseid, 'blockid' => $this->instance->id));
                $this->content->items[] = '<a href="'.$groupurl.'">'.$groupwizard.'</a><br/>';
                $this->content->icons[] = $icon;
            }

            $this->content->footer = '';
            return $this->content;
        } else {
            $this->content = new stdClass;
            $this->content->items = array();
            $this->content->icons = array();
            $this->content->footer = '';
            return $this->content;
        }
    }
}
