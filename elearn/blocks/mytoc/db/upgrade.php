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
 * This file keeps track of upgrades to the MYTOC block
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_mytoc_upgrade($oldversion, $block) {
    global $DB;

    if ($oldversion < 2023040111) {
        
        if ($systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => 1))) {
            $page = new moodle_page();
            $page->set_context(context_system::instance());
            // Check to see if this block is already on the default /my page.
            $criteria = array(
                'blockname' => 'mytoc',
                'parentcontextid' => $page->context->id,
                'pagetypepattern' => 'my-index',
                'subpagepattern' => $systempage->id,
            );
        
            if (!$DB->record_exists('block_instances', $criteria)) {
                // Add the block to the default /my.
                $page->blocks->add_region('content');
                $page->blocks->add_block('mytoc', 'content', 0, false, 'my-index', $systempage->id);
            }
        }

        //add user allow moodle/my:manageblocks
        $cap = new stdClass();
        $cap->contextid    = $context->id;
        $cap->roleid       = 7;
        $cap->capability   = 'moodle/my:manageblocks';
        $cap->permission   = 1;
        $cap->timemodified = time();
        $cap->modifierid   = 0;
        $DB->insert_record('role_capabilities', $cap);

        upgrade_block_savepoint(true, 2023040111, 'mytoc');
    }

    if ($oldversion < 2023040114) {
        //my_reset_page_for_all_users
        $DB->delete_records_select('my_pages', 'userid IS NOT NULL AND private = 1');

        upgrade_block_savepoint(true, 2023040114, 'mytoc');
    }

    

    return true;
}
