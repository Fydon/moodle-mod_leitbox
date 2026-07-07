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

class external extends external_api {

    public static function get_box_counts_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The adaptivereview instance id'),
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
        $results = [];

        // Box 0: Cards with NO progress entry OR box_number = 0
        $sql_new = "SELECT COUNT(*) AS cnt
                      FROM {adaptivereview_cards} c
                 LEFT JOIN {adaptivereview_progress} p ON c.id = p.cardid AND p.userid = :userid
                     WHERE c.adaptivereviewid = :instanceid
                       AND (p.id IS NULL OR p.box_number = 0)";
        $count_new = $DB->count_records_sql($sql_new, ['instanceid' => $params['instanceid'], 'userid' => $userid]);
        if ($count_new > 0) {
            $results[] = ['box_number' => 0, 'count' => $count_new];
        }

        // Boxes 1-5: Aggregate query
        $sql_boxes = "SELECT p.box_number, COUNT(*) AS cnt
                        FROM {adaptivereview_cards} c
                        JOIN {adaptivereview_progress} p ON c.id = p.cardid
                       WHERE c.adaptivereviewid = :instanceid
                         AND p.userid   = :userid
                         AND p.box_number > 0
                    GROUP BY p.box_number";
        $box_counts = $DB->get_records_sql($sql_boxes, ['instanceid' => $params['instanceid'], 'userid' => $userid]);
        
        foreach ($box_counts as $box_number => $data) {
            $results[] = ['box_number' => $box_number, 'count' => $data->cnt];
        }

        return $results;
    }

    public static function get_box_counts_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'box_number' => new external_value(PARAM_INT, 'The Leitner box number (0-5)'),
                'count'      => new external_value(PARAM_INT, 'The number of cards in this box'),
            ])
        );
    }

    public static function get_cards_by_box_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'The adaptivereview instance id'),
            'boxnumber'  => new external_value(PARAM_INT, 'The Leitner box number (0-5)'),
        ]);
    }

    public static function get_cards_by_box($instanceid, $boxnumber) {
        global $DB, $USER;
        
        $params = self::validate_parameters(self::get_cards_by_box_parameters(), [
            'instanceid' => $instanceid,
            'boxnumber' => $boxnumber
        ]);
        
        $cm = get_coursemodule_from_instance('adaptivereview', $params['instanceid']);
        if (!$cm) {
            throw new \moodle_exception('invalidcoursemodule');
        }
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        $box = $params['boxnumber'];
        if ($box < 0 || $box > 5) {
            throw new \moodle_exception('invalidparameter');
        }

        $userid = $USER->id;
        $instanceid = $params['instanceid'];
        
        // Fetch instance to read cardorder setting
        $adaptivereview = $DB->get_record('adaptivereview', ['id' => $instanceid], 'cardorder', MUST_EXIST);
        $order_by = ($adaptivereview->cardorder == 1) ? "ORDER BY c.id ASC" : "";

        if ($box == 0) {
            // New cards: box_number = 0 or no progress record yet
            $sql = "SELECT c.*
                      FROM {adaptivereview_cards} c
                 LEFT JOIN {adaptivereview_progress} p ON c.id = p.cardid AND p.userid = :userid
                     WHERE c.adaptivereviewid = :instanceid
                       AND (p.id IS NULL OR p.box_number = 0)
                       $order_by";
            $cards = $DB->get_records_sql($sql, ['userid' => $userid, 'instanceid' => $instanceid]);
        } else {
            // Existing cards in a specific box
            $sql = "SELECT c.*
                      FROM {adaptivereview_cards} c
                      JOIN {adaptivereview_progress} p ON c.id = p.cardid
                     WHERE c.adaptivereviewid = :instanceid
                       AND p.userid = :userid
                       AND p.box_number = :boxnumber
                       $order_by";
            $cards = $DB->get_records_sql($sql, [
                'userid' => $userid, 
                'instanceid' => $instanceid, 
                'boxnumber' => $box
            ]);
        }

        $result = [];
        foreach ($cards as $card) {
            // Resolve language-neutral demo card markers (e.g. ##demo_q1##) to
            // the user's current Moodle language. This allows demo cards to be
            // multilingual even though they are stored as plain keys in the DB.
            $resolve = function($text) {
                if (preg_match('/^##(demo_[a-z0-9]+)##$/', $text, $m)) {
                    return get_string($m[1], 'mod_adaptivereview');
                }
                return $text;
            };
            $result[] = [
                'id'       => $card->id,
                'question' => $resolve($card->question),
                'answer'   => $resolve($card->answer),
                'hint'     => $resolve($card->hint ? $card->hint : ''),
                'category' => $card->category ? $card->category : '',
            ];
        }

        // Apply random shuffle if cardorder is 0 (Random)
        if ($adaptivereview->cardorder == 0) {
            shuffle($result);
        }

        // Always force the very first tutorial demo card to be strictly the first card if it's in this set
        foreach ($result as $index => $c) {
            if ($c['category'] === 'demo') {
                // Move it to the very front of the array
                $demo_card = $result[$index];
                unset($result[$index]);
                array_unshift($result, $demo_card);
                break;
            }
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
            'rating' => $rating
        ]);

        // Security check: get card and ensure it exists and user can access its module.
        $card = $DB->get_record('adaptivereview_cards', ['id' => $params['cardid']], '*', MUST_EXIST);
        $adaptivereview = $DB->get_record('adaptivereview', ['id' => $card->adaptivereviewid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $adaptivereview->course], '*', MUST_EXIST);
        
        // get_coursemodule_from_instance() returns the CM-ID.
        // get_fast_modinfo()->get_cm() requires CM-ID (not Instance-ID!).
        $cm_raw = get_coursemodule_from_instance('adaptivereview', $adaptivereview->id, $course->id, false, MUST_EXIST);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cm_raw->id); // cm_info object with customdata
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        $userid = $USER->id;
        $progress = $DB->get_record('adaptivereview_progress', ['userid' => $userid, 'cardid' => $card->id]);
        
        $now = time();

        if (!$progress) {
            $progress = new \stdClass();
            $progress->userid = $userid;
            $progress->cardid = $card->id;
            $progress->box_number = 0;
            $progress->status = 0;
            $progress->count_correct = 0;
            $progress->count_wrong = 0;
            $progress->last_reviewed = $now;
            $progress->id = $DB->insert_record('adaptivereview_progress', $progress);
        } else {
            $progress->last_reviewed = $now;
        }

        if ($params['rating'] == 2) { // Green
            $progress->count_correct++;
            if ($progress->box_number < 5) {
                $progress->box_number++;
            }
        } elseif ($params['rating'] == 1) { // Yellow
            // Stays in current box, just updates timestamp
        } else { // Red
            $progress->count_wrong++;
            if ($progress->box_number > 1) {
                $progress->box_number--; // Go back one box
            } else {
                $progress->box_number = 1; // Stay in box 1 as minimum
            }
        }

        $DB->update_record('adaptivereview_progress', $progress);

        // Trigger Moodle's completion API to re-evaluate conditions
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }

        return [
            'success' => true,
            'new_box' => $progress->box_number
        ];
    }

    public static function submit_answer_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success indicator'),
            'new_box' => new external_value(PARAM_INT, 'The new box number for this card'),
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
        $cm = $modinfo->get_cm($cm_raw->id); // cm_info object with customdata
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/adaptivereview:view', $context);

        // Get all card IDs belonging to this instance.
        $cardids = $DB->get_fieldset_select('adaptivereview_cards', 'id', 'adaptivereviewid = ?', [$params['instanceid']]);

        if (!empty($cardids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($cardids);
            $inparams[] = $USER->id;
            $DB->delete_records_select('adaptivereview_progress', "cardid $insql AND userid = ?", $inparams);
        }

        // Ensure completion triggers also run for progress resets
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }

        return ['success' => true, 'reset_count' => count($cardids)];
    }

    public static function reset_progress_returns() {
        return new external_single_structure([
            'success'     => new external_value(PARAM_BOOL, 'Success indicator'),
            'reset_count' => new external_value(PARAM_INT,  'Number of cards reset'),
        ]);
    }
}
