<?php
namespace mod_adaptivereview\local;

defined('MOODLE_INTERNAL') || die();

class scheduler {

    public static function process_review(object $mastery, int $rating): object {
        $now = time();

        $score = isset($mastery->masteryscore) ? (float)$mastery->masteryscore : 0.0;

        if ($rating === 0) {
            // Red: missed / struggled.
            $score = max(0.0, $score - 0.4);
            $interval = 1;

        } else if ($rating === 1) {
            // Yellow: partially remembered.
            $score = min(5.9, $score + 0.3);
            $interval = self::interval_from_score($score);

        } else if ($rating === 2) {
            // Green: remembered well.
            $score = min(5.9, $score + 0.7);
            $interval = self::interval_from_score($score);

        } else {
            throw new \invalid_parameter_exception('Invalid rating.');
        }

        $mastery->masteryscore = $score;
        $mastery->requiredintervaldays = $interval;
        $mastery->lastreviewdate = $now;
        $mastery->nextreviewdate = $now + ($interval * DAYSECS);

        // Temporary compatibility so the current frontend still works.
        $mastery->box_number = self::box_from_score($score);

        return $mastery;
    }

    private static function interval_from_score(float $score): int {
        if ($score < 1.0) {
            return 1;
        } else if ($score < 2.0) {
            return 2;
        } else if ($score < 3.0) {
            return 4;
        } else if ($score < 4.0) {
            return 7;
        } else if ($score < 5.0) {
            return 14;
        }

        return 30;
    }

    private static function box_from_score(float $score): int {
        if ($score < 1.0) {
            return 1;
        } else if ($score < 2.0) {
            return 2;
        } else if ($score < 3.0) {
            return 3;
        } else if ($score < 4.0) {
            return 4;
        }

        return 5;
    }
}