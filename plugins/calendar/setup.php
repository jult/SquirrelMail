<?php

/**
 * Calendar plugin activation script
 *
 * @copyright 2002-2020 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: setup.php 14840 2020-01-07 07:42:38Z pdontthink $
 * @package plugins
 * @subpackage calendar
 */

/**
 * Initialize the plugin
 * @return void
 */
function squirrelmail_plugin_init_calendar() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['calendar'] = 'calendar';
}

/**
 * Adds Calendar link to upper menu
 * @return void
 */
function calendar() {
    displayInternalLink('plugins/calendar/calendar.php',_("Calendar"),'right');
    echo "&nbsp;&nbsp;\n";
}

?>