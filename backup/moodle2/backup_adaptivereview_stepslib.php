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

class backup_adaptivereview_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {

        // Define each element separated.
        $adaptivereview = new backup_nested_element('adaptivereview', ['id'], [
            'course', 'name', 'intro', 'introformat', 'cardorder',
            'completion_min_cards', 'completion_min_mastered', 'completion_all_mastered', 'timecreated', 'timemodified'
        ]);

        $cards = new backup_nested_element('cards');
        $card = new backup_nested_element('card', ['id'], [
            'question', 'answer', 'hint', 'category'
        ]);

        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'box_number', 'status', 'count_correct', 'count_wrong', 'last_reviewed'
        ]);

        // Build the tree.
        $adaptivereview->add_child($cards);
        $cards->add_child($card);

        $card->add_child($progresses);
        $progresses->add_child($progress);

        // Define sources.
        $adaptivereview->set_source_table('adaptivereview', ['id' => backup::VAR_ACTIVITYID]);
        $card->set_source_table('adaptivereview_cards', ['adaptivereviewid' => backup::VAR_PARENTID]);

        // Export user progress only if userinfo is included in the backup.
        if ($this->get_setting_value('userinfo')) {
            $progress->set_source_table('adaptivereview_progress', ['cardid' => backup::VAR_PARENTID]);
        }

        // Define id annotations to ensure accurate restoration of users and contexts.
        $progress->annotate_ids('user', 'userid');

        // Annotate file areas (intro text files).
        $adaptivereview->annotate_files('mod_adaptivereview', 'intro', null); // Intro might have embedded images

        return $this->prepare_activity_structure($adaptivereview);
    }
}
