<?php

// Mocking Moodle DB counts for the "Min Mastered" condition specifically to pen-test the SQL logic

// Scenario 1: User has to master 2 cards.
$min_mastered_setting = 2;

// User's current DB state (Progress table simulation)
$progress_table = [
    ['id' => 1, 'cardid' => 101, 'userid' => 2, 'box_number' => 1],
    ['id' => 2, 'cardid' => 102, 'userid' => 2, 'box_number' => 5], // 1 Mastered
    ['id' => 3, 'cardid' => 103, 'userid' => 2, 'box_number' => 4],
];

// Replicate lib.php logic
$total_mastered = 0;
foreach($progress_table as $p) {
    if ($p['box_number'] == 5) {
        $total_mastered++;
    }
}

echo "Pen Test 1 - Target: 2 Mastered\n";
echo "Current Mastered: $total_mastered\n";
if ($total_mastered < $min_mastered_setting) {
    echo "Result: NOT COMPLETED (Correct)\n";
} else {
    echo "Result: COMPLETED (Incorrect!)\n";
}

// Scenario 2: What happens if a user masters the SAME card twice? 
// Moodle DB setup: a user only has ONE progress row per card.
// So a count of Box 5 records *must* equal distinct mastered cards.
$sql_mastered_logic_test = "SELECT COUNT(*) FROM {adaptivereview_progress} WHERE userid = :userid AND box_number = 5";
echo "Pen Test 2 - SQL Logic: $sql_mastered_logic_test\n";
echo "SQL test relies on DB constraint: UNIQUE(userid, cardid). Does install.xml enforce this?\n";

?>
