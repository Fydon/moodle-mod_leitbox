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
namespace mod_adaptivereview\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\core_userlist_provider;

defined('MOODLE_INTERNAL') || die();

class provider implements metadata_provider, plugin_provider, core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'adaptivereview_mastery',
            [
                'userid' => 'privacy:metadata:adaptivereview_mastery:userid',
                'cardid' => 'privacy:metadata:adaptivereview_mastery:cardid',
                'box_number' => 'privacy:metadata:adaptivereview_mastery:box_number',
                'count_correct' => 'privacy:metadata:adaptivereview_mastery:count_correct',
                'count_wrong' => 'privacy:metadata:adaptivereview_mastery:count_wrong',
                'last_reviewed' => 'privacy:metadata:adaptivereview_mastery:last_reviewed',
            ],
            'privacy:metadata:adaptivereview_mastery'
        );
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.name = :modname AND m.id = cm.module
                  JOIN {adaptivereview} r ON r.id = cm.instance
                  JOIN {adaptivereview_items} rc ON rc.adaptivereviewid = r.id
                  JOIN {adaptivereview_mastery} rp ON rp.itemid = rc.id
                 WHERE rp.userid = :userid";
        $params = [
            'modname' => 'adaptivereview',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $sql = "SELECT rp.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.name = :modname AND m.id = cm.module
                  JOIN {adaptivereview} r ON r.id = cm.instance
                  JOIN {adaptivereview_items} rc ON rc.adaptivereviewid = r.id
                  JOIN {adaptivereview_mastery} rp ON rp.itemid = rc.id
                 WHERE cm.id = :cmid";
        
        $params = [
            'modname' => 'adaptivereview',
            'cmid' => $context->instanceid
        ];
        
        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $cm = get_coursemodule_from_id('adaptivereview', $context->instanceid);
                if (!$cm) {
                    continue;
                }

                $sql = "SELECT rp.*, rc.question, rc.answer
                          FROM {adaptivereview_mastery} rp
                          JOIN {adaptivereview_items} rc ON rc.id = rp.itemid
                         WHERE rc.adaptivereviewid = ? AND rp.userid = ?";
                $progress_records = $DB->get_records_sql($sql, [$cm->instance, $userid]);

                if (!empty($progress_records)) {
                    $exportdata = [];
                    foreach ($progress_records as $rec) {
                        $exportdata[] = (object)[
                            'question' => format_text($rec->question),
                            'answer' => format_text($rec->answer),
                            'box_number' => $rec->box_number,
                            'count_correct' => $rec->count_correct,
                            'count_wrong' => $rec->count_wrong,
                            'last_reviewed' => date('Y-m-d H:i:s', $rec->last_reviewed)
                        ];
                    }
                    
                    \core_privacy\local\request\writer::with_context($context)->export_data(
                        [get_string('pluginname', 'mod_adaptivereview'), get_string('cards', 'mod_adaptivereview')],
                        (object)['progress' => $exportdata]
                    );
                }
            }
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        if ($cm = get_coursemodule_from_id('adaptivereview', $context->instanceid)) {
            $sql = "SELECT p.id 
                      FROM {adaptivereview_mastery} p
                      JOIN {adaptivereview_items} c ON c.id = p.itemid
                     WHERE c.adaptivereviewid = ?";
            $DB->delete_records_select('adaptivereview_mastery', "id IN ($sql)", [$cm->instance]);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                if ($cm = get_coursemodule_from_id('adaptivereview', $context->instanceid)) {
                    $sql = "SELECT p.id
                              FROM {adaptivereview_mastery} p
                              JOIN {adaptivereview_items} c ON c.id = p.itemid
                             WHERE c.adaptivereviewid = ? AND p.userid = ?";
                    $DB->delete_records_select('adaptivereview_mastery', "id IN ($sql)", [$cm->instance, $userid]);
                }
            }
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        if ($cm = get_coursemodule_from_id('adaptivereview', $context->instanceid)) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids);
            
            $sql = "SELECT p.id 
                      FROM {adaptivereview_mastery} p
                      JOIN {adaptivereview_items} c ON c.id = p.itemid
                     WHERE c.adaptivereviewid = ? AND p.userid $insql";
            
            $params = array_merge([$cm->instance], $inparams);
            
            $DB->delete_records_select('adaptivereview_mastery', "id IN ($sql)", $params);
        }
    }
}
