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

global $CFG;
require_once($CFG->dirroot . '/mod/adaptivereview/backup/moodle2/backup_adaptivereview_stepslib.php');

class backup_adaptivereview_activity_task extends backup_activity_task {

    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    protected function define_my_steps() {
        // Add the adaptivereview structure step.
        $this->add_step(new backup_adaptivereview_activity_structure_step('adaptivereview_structure', 'adaptivereview.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of adaptivereview.
        $search = "/(" . $base . "\/mod\/adaptivereview\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ADAPTIVEREVIEWINDEX*$2@$', $content);

        // Link to adaptivereview view by moduleid.
        $search = "/(" . $base . "\/mod\/adaptivereview\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ADAPTIVEREVIEWVIEWBYID*$2@$', $content);

        return $content;
    }
}
