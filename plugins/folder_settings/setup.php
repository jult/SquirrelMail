<?php
/**
 * Folder Settings plugin init script
 * @copyright &copy; 2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: setup.php,v 1.4 2005/09/21 17:50:22 tokul Exp $
 * @package plugins
 * @subpackage folder_settings
 */

/**
 * Init function
 */
function squirrelmail_plugin_init_folder_settings() {
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['right_main_after_header']['folder_settings'] = 'folderSettings';
    $squirrelmail_plugin_hooks['optpage_loadhook_folder']['folder_settings'] = 'folder_settings_optblock';
    $squirrelmail_plugin_hooks['loading_prefs']['folder_settings'] = 'folder_settings_loadprefs';
}

/**
 * Function attached to folder option block hook
 * @since 0.3
 */
function folder_settings_optblock() {
    include_once(SM_PATH . 'plugins/folder_settings/functions.php');
    folder_settings_optblock_do();
}

/**
 * Function attached to folder option block hook
 * @since 0.3
 */
function folder_settings_loadprefs() {
    include_once(SM_PATH . 'plugins/folder_settings/functions.php');
    folder_settings_loadprefs_do();
}

/**
 * Load folder settings
 */
function folderSettings() {
    include_once(SM_PATH . 'plugins/folder_settings/functions.php');
    folder_settings_do();
}

/**
 * Return version number
 * @return string version number
 * @since 0.3
 */
function folder_settings_version() {
    return '0.3';
}

?>