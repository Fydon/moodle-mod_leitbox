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
$string['modulename'] = 'Adaptive Review';
$string['modulename_help'] = 'The Adaptive Review activity allows students to practice flashcards using a spaced repetition system based on the Leitner method. The UI uses a modern single-page application approach.';
$string['modulenameplural'] = 'Adaptive Reviews';
$string['pluginadministration'] = 'Adaptive Review administration';
$string['pluginname'] = 'Adaptive Review';
$string['adaptivereview:addinstance'] = 'Add a new Adaptive Review';
$string['adaptivereview:view'] = 'View Adaptive Review';

$string['adaptivereviewname'] = 'Activity Name';
$string['settings'] = 'Adaptive Review Settings';
$string['completion_min_cards'] = 'Minimum cards answered correctly';
$string['completion_min_cards_desc'] = 'Student must answer at least this many cards correctly at least once:';
$string['completion_min_cards_help'] = 'A card counts as "correctly answered" once it has been rated green at least once and is in Box 1 or higher. Cards that were only rated red or yellow do not count.';

$string['completion_min_mastered'] = 'Mastered cards (Box 5)';
$string['completion_min_mastered_desc'] = 'Minimum number of flashcards students must master (reach Box 5):';
$string['completion_min_mastered_help'] = 'Specifies how many cards students must promote to the Expert deck (Box 5). Important: This value must not exceed the total number of cards in the activity – otherwise completion becomes unreachable.';

$string['completion_all_mastered'] = 'All cards mastered';
$string['completion_all_mastered_desc'] = 'Students must master ALL flashcards (move every card to the Expert deck)';
$string['completion_all_mastered_help'] = 'If enabled, the activity is only marked complete when students have moved every single flashcard into the final Expert box (Box 5).';

$string['box0'] = 'New';
$string['box1'] = 'Beginner';
$string['box2'] = 'Learner';
$string['box3'] = 'Advanced';
$string['box4'] = 'Experienced';
$string['box5'] = 'Expert';

$string['cardorder'] = 'Card Order';
$string['cardorder_random'] = 'Random';
$string['cardorder_sequential'] = 'Sequential';
$string['cardorder_help'] = 'Determines whether cards are presented in a random order or sequentially as they were added.';

$string['question'] = 'Question';
$string['answer'] = 'Answer';
$string['hint'] = 'Hint';

// Progress bar
$string['progress_label'] = 'cards at Expert level';
$string['progress_aria'] = 'Progress: {mastered} of {total} cards mastered';



// Privacy metadata.
$string['privacy:metadata:adaptivereview_progress'] = 'Information about user progress on Adaptive Review.';
$string['privacy:metadata:adaptivereview_progress:userid'] = 'The user ID.';
$string['privacy:metadata:adaptivereview_progress:cardid'] = 'The card ID.';
$string['privacy:metadata:adaptivereview_progress:box_number'] = 'The Leitner box the card is currently in.';
$string['privacy:metadata:adaptivereview_progress:count_correct'] = 'The number of times the user answered the card correctly.';
$string['privacy:metadata:adaptivereview_progress:count_wrong'] = 'The number of times the user answered the card incorrectly.';
$string['privacy:metadata:adaptivereview_progress:last_reviewed'] = 'The timestamp when the card was last reviewed.';

// Management Interface
$string['demo_q1'] = 'Welcome to Adaptive Review.<br><br>What is the goal of this learning system?';
$string['demo_a1'] = 'The goal is to move all flashcards to the final deck ("Expert") by answering them correctly.<br><br>The system uses the method of <b>spaced repetition</b> for this purpose.';
$string['demo_h1'] = 'Think about how you remember things best long-term.';
$string['demo_q2'] = 'How do I move a card to the next deck?';
$string['demo_a2'] = 'By clicking the <b>green</b> button <b>Got it</b>.<br><br>This indicates that the answer was known. The card advances one deck and will be queried less frequently in the future.';
$string['demo_h2'] = 'It has to do with the green checkmark.';
$string['demo_q3'] = 'What happens when clicking the <b>red</b> button <b>Hard</b>?';
$string['demo_a3'] = 'The card moves back exactly <b>one deck</b>.<br><br>Select this option if you did not know the answer. The card will be repeated more frequently without completely resetting your previous learning progress.';
$string['demo_h3'] = 'Did not know it!';
$string['demo_q4'] = 'What is the <b>yellow</b> button <b>Again</b> used for?';
$string['demo_a4'] = 'This option is intended for cases where your answer was uncertain or incomplete.<br><br>The card remains in its current deck. It will be presented again within the same learning session, but your overall progress is maintained.';
$string['demo_h4'] = 'The sweet spot between easy and hard.';
$string['demo_q5'] = 'Usage note: Can these demo cards be deleted?';
$string['demo_a5'] = 'Yes. As soon as you import or create your own flashcards, the system will remove these demo cards automatically.';
$string['demo_h5'] = '';

$string['managecards'] = 'Manage Cards';
$string['backtoactivity'] = 'Back to activity';
$string['addsinglecard'] = 'Add single card';
$string['editsinglecard'] = 'Edit card';
$string['addcard'] = 'Save Card';
$string['updatecard'] = 'Save changes';
$string['cardadded'] = 'Card added successfully.';
$string['cardupdated'] = 'Card updated successfully.';
$string['cancel'] = 'Cancel';

$string['cardsdeleted'] = 'Successfully deleted {$a} cards.';
$string['deleteselected'] = 'Delete selected';
$string['confirmbulkdelete'] = 'Are you sure you want to delete the selected cards? This action cannot be undone.';
$string['error_limit_reached'] = 'Limit reached: For didactic reasons, a maximum of 200 cards per set is allowed.';
$string['error_limit_exceeded_import'] = 'Import failed: The limit of 200 cards would be exceeded. You can only add {$a} more cards to this set.';
$string['didactic_limit_notice'] = '<strong>Learning Tip:</strong> To ensure optimal learning success and avoid cognitive overload, each activity is limited to <strong>200 cards</strong>. For larger topics, we highly recommend splitting the material across multiple Adaptive Review activities (e.g., "Chapter 1", "Chapter 2") in your course.';
$string['bulkimport'] = 'Bulk Import (AI / Text)';
$string['bulkimportdesc'] = 'Paste a formatted text block to import multiple cards at once.';
$string['exportcards'] = 'Export cards (.txt)';
$string['prompt_instruction'] = 'Copy this prompt and use it with any AI to generate cards from your material:';
$string['prompt_type_selection'] = 'Select a learning method / question type:';
$string['prompt_type_standard'] = 'Standard (Q&A)';
$string['prompt_type_tf'] = 'True or False';
$string['prompt_type_vocab'] = 'Vocabulary / Terms';
$string['prompt_type_cloze'] = 'Fill-in-the-blank';
$string['prompt_type_jeopardy'] = 'Jeopardy (Answer-Question)';
$string['prompt_type_transfer'] = 'Transfer & Everyday Context';

$string['prompt_template_standard'] = 'Create flashcards from the following text for studying. 
Format each card EXACTLY like this, with no markdown (no asterisks, no hashes), and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: What is the capital of France?
A: Paris
H: City of Love

===CARD===
Q: Who formulated the theory of relativity?
A: Albert Einstein

Here is the text you should create the cards from:';

$string['prompt_template_tf'] = 'Create True/False flashcards from the following text for studying. 
Format each card EXACTLY like this, with no markdown, and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: True or False: Paris is the capital of Spain.
A: False. Paris is the capital of France.
H: Think of the Eiffel Tower.

===CARD===
Q: True or False: The Earth is flat.
A: False.

Here is the text you should create the cards from:';

$string['prompt_template_vocab'] = 'Extract the most important vocabulary or terms from the following text and create flashcards for studying. 
Format each card EXACTLY like this, with no markdown, and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: The house (German)
A: Das Haus
H: Building for living

===CARD===
Q: Mitosis
A: Cell nucleus division

Here is the text you should create the cards from:';

$string['prompt_template_cloze'] = 'Create fill-in-the-blank flashcards from the following text for studying. 
Format each card EXACTLY like this, with no markdown, and separate all cards with ===CARD===. The blank in the question is marked with [...], the required answer goes into the A field.
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: The capital of France is [...].
A: Paris
H: City of Love

===CARD===
Q: Albert Einstein formulated the [...].
A: Theory of Relativity

Here is the text you should create the cards from:';

$string['prompt_template_jeopardy'] = 'Create Jeopardy-style flashcards based on the following topic or text for studying. 
The question (Q) describes an effect, term, or phenomenon, and the answer (A) must strictly be formulated as a counter-question (e.g., "What is...?").
Format each card EXACTLY like this, with no markdown, and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: Without this gas from the air, the plant could not build sugar.
A: What is Carbon Dioxide (CO2)?
H: It is a greenhouse gas.

===CARD===
Q: The plant produces this substance as an energy carrier for itself.
A: What is Glucose?

Here is the text you should create the cards from:';

$string['prompt_template_transfer'] = 'Create flashcards based on the following topic or text focusing on transfer of learning and everyday context. 
The questions should encourage out-of-the-box thinking (why-questions, what-if scenarios).
Format each card EXACTLY like this, with no markdown, and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: Why do plants "breathe" oxygen at night even though they produce it during the day?
A: Because they cannot photosynthesize at night but consume energy through cellular respiration.

===CARD===
Q: What would happen if a plant were only exposed to green light?
A: It would die because it reflects green light and cannot use it to generate energy.
H: Think about why leaves look green.

Here is the text you should create the cards from:';
$string['importcards'] = 'Import Cards';
$string['cardsimported'] = '{$a} cards were imported successfully.';
$string['existingcards'] = 'Existing cards';
$string['nocards'] = 'No cards found in this activity.';
$string['carddeleted'] = 'Card deleted.';
$string['confirmdeletecard'] = 'Are you sure you want to delete this card?';

// Vue Frontend App Strings
$string['dashboardtitle'] = 'Your Adaptive Review Cards';
$string['dashboardsbtitle'] = 'Select a learning deck to practice';
$string['howitworks'] = 'How does this work?';
$string['cards'] = 'Cards';
$string['systemtitle'] = 'The Spaced Repetition System';
$string['systemintro'] = 'This plugin is based on the Leitner System – invented in 1972 by the Austrian scientist Sebastian Leitner and globally recognized in learning research today. The goal is to move cards from left to right into the final deck ("Graduated").';
$string['known_btn'] = 'Got it';
$string['known_desc'] = 'Easy! The card moves one stack to the right.';
$string['again_btn'] = 'Again';
$string['again_desc'] = 'Not sure. The card stays in the current stack.';
$string['hard_btn'] = 'Hard';
$string['hard_desc'] = 'Did not know it! The card moves back one stack.';
$string['systemtip'] = '<strong>Tip:</strong> Review the topics behind the cards you did not know – before you start a new attempt.';
$string['gotit'] = 'Got it, let\'s go!';
$string['showhint'] = 'Show hint';
$string['taptoflip'] = 'Tap to flip';
$string['action_back'] = 'Back';
$string['action_stay'] = 'Stay';
$string['action_next'] = 'Next';
$string['backtodashboard'] = 'Back to dashboard';
$string['cardxofy_x'] = 'Card';
$string['cardxofy_y'] = 'of';
$string['loadingcards'] = 'Shuffling cards...';
$string['sessiondone'] = 'All done!';
$string['sessiondonedesc'] = 'Good job. Return to the dashboard for the next deck.';
$string['feedback_grand_title'] = 'Masterpiece!';
$string['feedback_grand_desc'] = 'Congratulations! You successfully moved all cards into the Expert deck and reviewed them flawlessly. You have truly mastered this subject!';
$string['feedback_perfect_title'] = 'Strong Result';
$string['feedback_perfect_desc'] = 'You correctly Adaptive Reviewed all of the content. You have a solid grasp of this material.';
$string['feedback_good_title'] = 'Solid Performance';
$string['feedback_good_desc'] = 'You have a good overview. Consistent practice will close the remaining gaps.';
$string['feedback_okay_title'] = 'On the Right Track';
$string['feedback_okay_desc'] = 'The foundation is there. Another review session will reinforce your knowledge.';
$string['feedback_learn_title'] = 'Review Recommended';
$string['feedback_learn_desc'] = 'Some answers were difficult to Adaptive Review. Use the next round to train these specific topics.';
$string['completed'] = 'Completed';
$string['error_loading_cards'] = 'Could not load cards. Please check your connection.';
$string['reset_progress'] = 'Reset Learning Progress';
$string['reset_progress_confirm_title'] = 'Reset Learning Progress?';
$string['reset_progress_confirm_msg'] = 'Warning: This will reset all your learning progress. All cards will be moved back to "New". This cannot be undone!';
$string['reset_progress_btn'] = 'Yes, Reset';
$string['reset_progress_cancel'] = 'Cancel';
$string['reset_progress_done'] = 'Learning progress has been reset!';

$string['event_course_module_viewed'] = 'Adaptive Review activity viewed';
