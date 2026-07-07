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
 * AMD module for the Adaptive Review manage cards page.
 *
 * Handles:
 * - AI prompt type selector (updates pre-formatted prompt display)
 * - Select-all checkbox toggle for bulk deletion
 * - Delete confirmation dialogs (single and bulk)
 *
 * @module     mod_adaptivereview/manage
 * @package    mod_adaptivereview
 * @copyright  2026 Peter Pleimfeldner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    return {
        /**
         * Initialise all interactive behaviours on the manage cards page.
         *
         * @param {Object} params
         * @param {Object} params.prompts          - Map of prompt type keys to template strings.
         * @param {string} params.confirmDelete    - Confirmation message for single card deletion.
         * @param {string} params.confirmBulkDelete - Confirmation message for bulk deletion.
         */
        init: function(params) {

            // Defensive guard: bail out if params or prompts are not available.
            if (!params || !params.prompts) {
                return;
            }

            // --- Prompt type selector ---
            var selector = document.getElementById('adaptivereview-prompt-selector');
            var display  = document.getElementById('adaptivereview-prompt-display');
            if (selector && display) {
                selector.addEventListener('change', function() {
                    var key = selector.value;
                    if (params.prompts[key] !== undefined) {
                        display.textContent = params.prompts[key];
                    }
                });

                // Trigger initial display update to sync <pre> with currently selected option.
                var initialKey = selector.value;
                if (params.prompts[initialKey] !== undefined) {
                    display.textContent = params.prompts[initialKey];
                }
            }

            // --- Select-all checkbox ---
            var selectAll = document.getElementById('adaptivereview-selectall');
            if (selectAll) {
                selectAll.addEventListener('change', function(e) {
                    var checkboxes = document.querySelectorAll('.adaptivereview-cardcheckbox');
                    checkboxes.forEach(function(cb) {
                        cb.checked = e.target.checked;
                    });
                });
            }

            // --- Single card delete confirmation ---
            // Uses window.confirm() (synchronous, modal-blocking) to guarantee the dialog
            // stays open until the user responds, independent of Moodle's async modal stack.
            document.querySelectorAll('.adaptivereview-delete-card').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.confirm(params.confirmDelete)) {
                        window.location.href = link.getAttribute('href');
                    }
                });
            });

            // --- Bulk delete confirmation ---
            var bulkForm = document.getElementById('adaptivereview-bulkdelete-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (window.confirm(params.confirmBulkDelete)) {
                        bulkForm.submit();
                    }
                });
            }
        }
    };
});
