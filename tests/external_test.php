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
 * External functions unit tests for mod_adaptivereview.
 *
 * @package   mod_adaptivereview
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_adaptivereview\external;

use external_api;
use externallib_advanced_testcase;
use mod_adaptivereview\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/adaptivereview/classes/external.php');

/**
 * External functions unit tests for mod_adaptivereview.
 */
class external_test extends externallib_advanced_testcase {

    /**
     * Set up for every test
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Helper to create a basic adaptivereview activity with some cards.
     */
    protected function create_adaptivereview_activity() {
        global $DB;

        // Create course and user.
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create adaptivereview instance.
        $adaptivereview = $this->getDataGenerator()->create_module('adaptivereview', ['course' => $course->id]);

        // Clear default demo cards inserted by the mod_form/lib.php to start fresh.
        $DB->delete_records('adaptivereview_cards', ['adaptivereviewid' => $adaptivereview->id]);

        // Insert a test card.
        $card = new \stdClass();
        $card->adaptivereviewid = $adaptivereview->id;
        $card->question = 'What is the capital of France?';
        $card->answer = 'Paris';
        $card->hint = 'City of light';
        $card->id = $DB->insert_record('adaptivereview_cards', $card);

        return [$course, $student, $adaptivereview, $card];
    }

    /**
     * Test getting cards by box.
     */
    public function test_get_cards_by_box() {
        list($course, $student, $adaptivereview, $card) = $this->create_adaptivereview_activity();

        // Must be logged in as the student.
        $this->setUser($student);

        // Call the external function for box 0 (New cards).
        $result = external::get_cards_by_box($adaptivereview->id, 0);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(external::get_cards_by_box_returns(), $result);

        $this->assertCount(1, $result);
        $this->assertEquals($card->question, $result[0]['question']);
        $this->assertEquals($card->answer, $result[0]['answer']);
    }

    /**
     * Test submitting an answer (Spaced Repetition Logic).
     */
    public function test_submit_answer() {
        global $DB;
        list($course, $student, $adaptivereview, $card) = $this->create_adaptivereview_activity();

        $this->setUser($student);

        // 1. Test "Green" rating (Rating = 2)
        $result = external::submit_answer($card->id, 2);
        $result = external_api::clean_returnvalue(external::submit_answer_returns(), $result);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['new_box']); // Should move from Box 0 to Box 1

        // Check DB
        $progress = $DB->get_record('adaptivereview_progress', ['userid' => $student->id, 'cardid' => $card->id]);
        $this->assertEquals(1, $progress->box_number);
        $this->assertEquals(1, $progress->count_correct);

        // 2. Test "Red" rating on Box 1 (Rating = 0) -> Should stay in Box 1 (minimum bound)
        $result = external::submit_answer($card->id, 0);
        $result = external_api::clean_returnvalue(external::submit_answer_returns(), $result);
        
        $progress = $DB->get_record('adaptivereview_progress', ['userid' => $student->id, 'cardid' => $card->id]);
        $this->assertEquals(1, $progress->box_number); // Cannot drop below 1
        $this->assertEquals(1, $progress->count_wrong);
    }

    /**
     * Test resetting progress.
     */
    public function test_reset_progress() {
        global $DB;
        list($course, $student, $adaptivereview, $card) = $this->create_adaptivereview_activity();

        $this->setUser($student);

        // Submit an answer to create a progress record.
        external::submit_answer($card->id, 2);
        
        // Ensure progress exists.
        $this->assertEquals(1, $DB->count_records('adaptivereview_progress', ['userid' => $student->id]));

        // Call reset.
        $result = external::reset_progress($adaptivereview->id);
        $result = external_api::clean_returnvalue(external::reset_progress_returns(), $result);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['reset_count']);

        // Progress should be completely gone for this user/instance.
        $this->assertEquals(0, $DB->count_records('adaptivereview_progress', ['userid' => $student->id]));
    }
    /**
     * Test that invalid box number throws exception.
     */
    public function test_get_cards_by_box_invalid_box() {
        list($course, $student, $adaptivereview, $card) = $this->create_adaptivereview_activity();
        $this->setUser($student);
        $this->expectException(\moodle_exception::class);
        external::get_cards_by_box($adaptivereview->id, 99);
    }

    /**
     * Test completion state: min_cards condition uses DISTINCT card count.
     */
    public function test_completion_min_cards_counts_unique_cards() {
        global $DB;
        list($course, $student, $adaptivereview, $card) = $this->create_adaptivereview_activity();
        $this->setUser($student);

        // Answer the same card 5 times
        for ($i = 0; $i < 5; $i++) {
            external::submit_answer($card->id, 2);
        }

        // Despite 5 interactions, only 1 unique card was learned
        $progress_count = $DB->count_records('adaptivereview_progress', ['userid' => $student->id]);
        $this->assertEquals(1, $progress_count); // Only 1 unique progress record (UNIQUE INDEX)
    }
}
