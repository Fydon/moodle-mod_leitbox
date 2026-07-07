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

function xmldb_adaptivereview_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026010103) {
        $table = new xmldb_table('adaptivereview');
        $field = new xmldb_field('completion_min_mastered', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'completion_min_cards');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026010103, 'adaptivereview');
    }

    if ($oldversion < 2026022705) {
        $table = new xmldb_table('adaptivereview');
        $field = new xmldb_field('cardorder', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'introformat');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026022705, 'adaptivereview');
    }

    if ($oldversion < 2026030100) {
        $table = new xmldb_table('adaptivereview');
        $field = new xmldb_field('completion_all_mastered', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'completion_min_mastered');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026030100, 'adaptivereview');
    }

    return true;
}

