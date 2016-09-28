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

// Warning that the changes arent localized to the course.
echo $OUTPUT->box(get_string('globalnetworking', 'block_group_network'));

// Creating the list of available hosts. Self/Deleted and All Hosts are ignored.

$sql = "
    SELECT
        h.id,
        h.name as hostname,
        h.wwwroot,
        h2idp.publish as idppublish,
        h2idp.subscribe as idpsubscribe,
        idp.name as idpname,
        h2sp.publish as sppublish,
        h2sp.subscribe as spsubscribe,
        sp.name as spname
    FROM
        {mnet_host} h
    LEFT JOIN
        {mnet_host2service} h2idp
    ON
       (h.id = h2idp.hostid AND
       (h2idp.publish = 1 OR
        h2idp.subscribe = 1))
    INNER JOIN
        {mnet_service} idp
    ON
       (h2idp.serviceid = idp.id AND
        idp.name = 'sso_idp')
    LEFT JOIN
        {mnet_host2service} h2sp
    ON
       (h.id = h2sp.hostid AND
       (h2sp.publish = 1 OR
        h2sp.subscribe = 1))
    INNER JOIN
        {mnet_service} sp
    ON
       (h2sp.serviceid = sp.id AND
        sp.name = 'sso_sp')
    WHERE
       ((h2idp.publish = 1 AND h2sp.subscribe = 1) OR
       (h2sp.publish = 1 AND h2idp.subscribe = 1)) AND
        h.id != ?
    ORDER BY
        h.name ASC";

$id_providers       = array();
$service_providers  = array();
if ($resultset = $DB->get_records_sql($sql, array($CFG->mnet_localhost_id))) {
    foreach($resultset as $hostservice) {
        if (!empty($hostservice->idppublish) && !empty($hostservice->spsubscribe)) {
            $service_providers[$hostservice->id] = array('id' => $hostservice->id, 'name' => $hostservice->hostname, 'wwwroot' => $hostservice->wwwroot);
            $hostlist[$hostservice->id] = $hostservice->hostname;
        }
        if (!empty($hostservice->idpsubscribe) && !empty($hostservice->sppublish)) {
            $id_providers[]= array('id' => $hostservice->id, 'name' => $hostservice->hostname, 'wwwroot' => $hostservice->wwwroot);
        }
    }
}

// Preselect a unique platform.
if ($service_providers && count($service_providers) == 1) {
    $hostids = array_keys($service_providers);
    $platformid = $hostids[0];
}

// Printing the platform adress and the subnet name.

// Printing the dropdown list.
if (!empty($service_providers)) {
    echo '<div class="group-network-targetchoice" align="center">';
    echo get_string('wheretoopen', 'block_group_network');
    echo $OUTPUT->single_select(new moodle_url('/blocks/group_network/'.$choicemode.'.php', array('courseid' => $courseid, 'blockid' => $blockid)), 'platformid', $hostlist, $platformid);
    echo '</div>';
} else {
    echo $OUTPUT->notification(get_string('noplatform', 'block_group_network'));
    echo '<div class="group-network-targetchoice" align="center">';
    echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('backtocourse', 'block_group_network'));
    echo '</div>';
    echo $OUTPUT->footer($course);
    die;
}
