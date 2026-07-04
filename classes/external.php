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
namespace mod_adaptivereview;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use mod_adaptivereview\local\scheduler;

class external extends external_api {

    public static function get_box_counts_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The leitbox instance id'),
        ]);
    }

    public static function get_box_counts($instanceid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_box_counts_parameters(), [
            'instanceid' => $instanceid,
        ]);

        $cm = get_coursemodule_from_instance('adaptivereview', $params['instanceid']);
        if (!$cm) {
            throw new \moodle_exception('invalidcoursemodule');
        }

        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        $userid = $USER->id;
        $instanceid = $params['instanceid'];
        $now = time();

        // Frontend dashboard category IDs:
        // 0 = Due Today
        // 1 = New
        // 2 = Recently Learned
        // 3 = All Cards

        $count_due = $DB->count_records_sql(
            "SELECT COUNT(*)
               FROM {adaptivereview_items} c
               JOIN {adaptivereview_mastery} p
                 ON c.id = p.cardid
              WHERE c.adaptivereviewid = :instanceid
                AND p.userid = :userid
                AND p.lastreviewdate > 0
                AND p.nextreviewdate > 0
                AND p.nextreviewdate <= :now",
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
                'now' => $now,
            ]
        );

        $count_new = $DB->count_records_sql(
            "SELECT COUNT(*)
               FROM {adaptivereview_items} c
          LEFT JOIN {adaptivereview_mastery} p
                 ON c.id = p.cardid AND p.userid = :userid
              WHERE c.adaptivereviewid = :instanceid
                AND p.id IS NULL",
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
            ]
        );

        $count_recent = $DB->count_records_sql(
            "SELECT COUNT(*)
               FROM {adaptivereview_items} c
               JOIN {adaptivereview_mastery} p
                 ON c.id = p.cardid
              WHERE c.adaptivereviewid = :instanceid
                AND p.userid = :userid
                AND p.lastreviewdate > 0
                AND p.nextreviewdate > :now",
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
                'now' => $now,
            ]
        );

        $count_all = $DB->count_records('adaptivereview_items', [
            'adaptivereviewid' => $instanceid,
        ]);

        return [
            ['box_number' => 0, 'count' => $count_due],
            ['box_number' => 1, 'count' => $count_new],
            ['box_number' => 2, 'count' => $count_recent],
            ['box_number' => 3, 'count' => $count_all],
        ];
    }

    public static function get_box_counts_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'box_number' => new external_value(PARAM_INT, 'The review category number'),
                'count' => new external_value(PARAM_INT, 'The number of cards in this category'),
            ])
        );
    }

    public static function get_cards_by_box_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The adaptivereview instance id'),
            'boxnumber' => new external_value(PARAM_INT, 'The review category number'),
        ]);
    }

    public static function get_cards_by_box($instanceid, $boxnumber) {
    global $DB, $USER;

    $params = self::validate_parameters(self::get_cards_by_box_parameters(), [
        'instanceid' => $instanceid,
        'boxnumber' => $boxnumber,
    ]);

    $cm = get_coursemodule_from_instance('adaptivereview', $params['instanceid']);
    if (!$cm) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    $context = \context_module::instance($cm->id);
    self::validate_context($context);
    require_capability('mod/adaptivereview:view', $context);

    $box = $params['boxnumber'];
    if ($box < 0 || $box > 3) {
        throw new \moodle_exception('invalidparameter');
    }

    $userid = $USER->id;
    $instanceid = $params['instanceid'];
    $now = time();

    $adaptivereview = $DB->get_record('adaptivereview', ['id' => $instanceid], 'cardorder', MUST_EXIST);
    $order_by = ($adaptivereview->cardorder == 1) ? "ORDER BY c.id ASC" : "";

    $select = "SELECT c.*,
                      COALESCE(p.masteryscore, 0) AS masteryscore,
                      COALESCE(p.requiredintervaldays, 0) AS requiredintervaldays,
                      COALESCE(p.nextreviewdate, 0) AS nextreviewdate,
                      COALESCE(p.lastreviewdate, 0) AS lastreviewdate";

    if ($box == 0) {
        $sql = "$select
                  FROM {adaptivereview_items} c
                  JOIN {adaptivereview_mastery} p
                    ON c.id = p.cardid
                 WHERE c.adaptivereviewid = :instanceid
                   AND p.userid = :userid
                   AND p.lastreviewdate > 0
                   AND p.nextreviewdate > 0
                   AND p.nextreviewdate <= :now
                   $order_by";

        $cards = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'instanceid' => $instanceid,
            'now' => $now,
        ]);

    } else if ($box == 1) {
        $sql = "$select
                  FROM {adaptivereview_items} c
             LEFT JOIN {adaptivereview_mastery} p
                    ON c.id = p.cardid AND p.userid = :userid
                 WHERE c.adaptivereviewid = :instanceid
                   AND p.id IS NULL
                   $order_by";

        $cards = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'instanceid' => $instanceid,
        ]);

    } else if ($box == 2) {
        $sql = "$select
                  FROM {adaptivereview_items} c
                  JOIN {adaptivereview_mastery} p
                    ON c.id = p.cardid
                 WHERE c.adaptivereviewid = :instanceid
                   AND p.userid = :userid
                   AND p.lastreviewdate > 0
                   AND p.nextreviewdate > :now
                   $order_by";

        $cards = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'instanceid' => $instanceid,
            'now' => $now,
        ]);

    } else {
        $sql = "$select
                  FROM {adaptivereview_items} c
             LEFT JOIN {adaptivereview_mastery} p
                    ON c.id = p.cardid AND p.userid = :userid
                 WHERE c.adaptivereviewid = :instanceid
                 $order_by";

        $cards = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'instanceid' => $instanceid,
        ]);
    }

    $result = [];

    foreach ($cards as $card) {
        $resolve = function($text) {
            if (preg_match('/^##(demo_[a-z0-9]+)##$/', $text, $m)) {
                return get_string($m[1], 'mod_adaptivereview');
            }
            return $text;
        };

        $result[] = [
            'id' => $card->id,
            'question' => $resolve($card->question),
            'answer' => $resolve($card->answer),
            'hint' => $resolve($card->hint ? $card->hint : ''),
            'category' => $card->category ? $card->category : '',
            'masteryscore' => round((float)$card->masteryscore, 1),
            'requiredintervaldays' => (int)$card->requiredintervaldays,
            'nextreviewdate' => (int)$card->nextreviewdate,
            'lastreviewdate' => (int)$card->lastreviewdate,
        ];
    }

    if ($adaptivereview->cardorder == 0 && $box != 3) {
        shuffle($result);
    }

    return array_values($result);
    }

    public static function get_cards_by_box_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Card ID'),
                'question' => new external_value(PARAM_CLEANHTML, 'Question text'),
                'answer' => new external_value(PARAM_CLEANHTML, 'Answer text'),
                'hint' => new external_value(PARAM_CLEANHTML, 'Optional hint', VALUE_OPTIONAL),
                'category' => new external_value(PARAM_TEXT, 'Optional category', VALUE_OPTIONAL),
                'masteryscore' => new external_value(PARAM_FLOAT, 'Current mastery score'),
                'requiredintervaldays' => new external_value(PARAM_INT, 'Required interval days'),
                'nextreviewdate' => new external_value(PARAM_INT, 'Next review timestamp'),
                'lastreviewdate' => new external_value(PARAM_INT, 'Last review timestamp'),
            ])
        );
    }

    public static function submit_answer_parameters() {
        return new external_function_parameters([
            'cardid' => new external_value(PARAM_INT, 'The card ID'),
            'rating' => new external_value(PARAM_INT, 'Rating 0=Red, 1=Yellow, 2=Green'),
        ]);
    }

    public static function submit_answer($cardid, $rating) {
        global $DB, $USER;

        $params = self::validate_parameters(self::submit_answer_parameters(), [
            'cardid' => $cardid,
            'rating' => $rating,
        ]);

        $card = $DB->get_record('adaptivereview_items', ['id' => $params['cardid']], '*', MUST_EXIST);
        $adaptivereview = $DB->get_record('adaptivereview', ['id' => $card->adaptivereviewid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $adaptivereview->course], '*', MUST_EXIST);

        $cm_raw = get_coursemodule_from_instance('adaptivereview', $adaptivereview->id, $course->id, false, MUST_EXIST);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cm_raw->id);
        $context = \context_module::instance($cm->id);

        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        $userid = $USER->id;
        $progress = $DB->get_record('adaptivereview_mastery', [
            'userid' => $userid,
            'cardid' => $card->id,
        ]);

        if (!$progress) {
            $progress = new \stdClass();
            $progress->userid = $userid;
            $progress->cardid = $card->id;
            $progress->box_number = 0;
            $progress->status = 0;
            $progress->count_correct = 0;
            $progress->count_wrong = 0;
            $progress->last_reviewed = 0;
            $progress->masteryscore = 0.0;
            $progress->requiredintervaldays = 1;
            $progress->nextreviewdate = 0;
            $progress->lastreviewdate = 0;
            $progress->id = $DB->insert_record('adaptivereview_mastery', $progress);
        }

        $progress = scheduler::process_review($progress, $params['rating']);

        $DB->update_record('adaptivereview_mastery', $progress);

        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }

        return [
            'success' => true,
            'new_box' => $progress->box_number,
        ];
    }

    public static function submit_answer_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success indicator'),
            'new_box' => new external_value(PARAM_INT, 'The new review category for this card'),
        ]);
    }

    public static function reset_progress_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The adaptivereview instance id'),
        ]);
    }

    public static function reset_progress($instanceid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::reset_progress_parameters(), [
            'instanceid' => $instanceid,
        ]);

        $adaptivereview = $DB->get_record('adaptivereview', ['id' => $params['instanceid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $adaptivereview->course], '*', MUST_EXIST);

        $cm_raw = get_coursemodule_from_instance('adaptivereview', $params['instanceid'], $course->id, false, MUST_EXIST);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cm_raw->id);
        $context = \context_module::instance($cm->id);

        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        $cardids = $DB->get_fieldset_select(
            'adaptivereview_items',
            'id',
            'adaptivereviewid = ?',
            [$params['instanceid']]
        );

        $resetcount = 0;

        if (!empty($cardids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($cardids, SQL_PARAMS_QM);

            $select = "userid = ? AND cardid $insql";
            $deleteparams = array_merge([$USER->id], $inparams);

            $resetcount = $DB->count_records_select('adaptivereview_mastery', $select, $deleteparams);
            $DB->delete_records_select('adaptivereview_mastery', $select, $deleteparams);
        }

        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }

        return [
            'success' => true,
            'reset_count' => $resetcount,
        ];
    }

    public static function reset_progress_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success indicator'),
            'reset_count' => new external_value(PARAM_INT, 'Number of cards reset'),
        ]);
    }
}
