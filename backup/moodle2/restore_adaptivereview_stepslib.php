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
 * @package   mod_adaptivereview
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_adaptivereview_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('adaptivereview', '/activity/adaptivereview');
        $paths[] = new restore_path_element('adaptivereview_item', '/activity/adaptivereview/items/item');
        
        if ($userinfo) {
            $paths[] = new restore_path_element('adaptivereview_mastery', '/activity/adaptivereview/items/item/masteries/mastery');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_adaptivereview($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be happening here.
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the adaptivereview record.
        $newitemid = $DB->insert_record('adaptivereview', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_adaptivereview_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->adaptivereviewid = $this->get_new_parentid('adaptivereview');

        $newitemid = $DB->insert_record('adaptivereview_items', $data);
        $this->set_mapping('adaptivereview_items', $oldid, $newitemid);
    }

    protected function process_adaptivereview_mastery($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Ensure user ID mapping is found
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->userid) {
            return; // Don't restore if the user isn't found
        }

        $data->itemid = $this->get_new_parentid('adaptivereview_items');
        
        $newitemid = $DB->insert_record('adaptivereview_mastery', $data);
        $this->set_mapping('adaptivereview_mastery', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add adaptivereview related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_adaptivereview', 'intro', null);
    }
}
