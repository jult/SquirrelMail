<?php
/**
 * Folder Settings plugin functions
 * @copyright &copy; 2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: functions.php,v 1.3 2005/09/25 14:42:33 tokul Exp $
 * @package plugins
 * @subpackage folder_settings
 */

/**
 * Adds option blocks
 * @since 0.3
 */
function folder_settings_optblock_do() {
    global $optpage_data;

    bindtextdomain ('folder_settings', SM_PATH . 'locale');
    textdomain ('folder_settings');

    $optpage_data['grps']['folder_settings'] = _("Folder Sorting Options");
    $optionValues = array();
    $optionValues[] = array(
         'name'    => 'perfolder_sort',
         'caption' => _("Enable per-folder sorting"),
         'type'    => SMOPT_TYPE_BOOLEAN,
         'refresh' => SMOPT_REFRESH_NONE
         );
    $optionValues[] = array(
         'name'    => 'default_sort',
         'caption' => _("Default sort_order"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => array(0 => _("Date (descending)"),
                            1 => _("Date (ascending)"),
                            2 => _("From (descending)"),
                            3 => _("From (ascending)"),
                            4 => _("Subject (descending)"),
                            5 => _("Subject (ascending)"),
                            6 => _("Unsorted"))
         );
    $optpage_data['vals']['folder_settings'] = $optionValues;

    bindtextdomain ('squirrelmail', SM_PATH . 'locale');
    textdomain ('squirrelmail');
}

/**
 * Loads plugin preferences
 * @since 0.3
 */
function folder_settings_loadprefs_do() {
    global $data_dir, $username;
    global $perfolder_sort, $default_sort;

    $perfolder_sort = getPref($data_dir, $username, 'perfolder_sort', SMPREF_OFF);
    $default_sort = getPref($data_dir, $username, 'default_sort', SMPREF_NONE);
}

/**
 * Manages folder sorting changes
 * @since 0.3
 */
function folder_settings_do() {
    if (check_sm_version(1,5,1)) {
        // load error_box() function
        include_once(SM_PATH . 'functions/display_messages.php');
        // switch domain
        bindtextdomain ('folder_settings', SM_PATH . 'locale');
        textdomain ('folder_settings');
        // set error message
        $error_message = _("Folder Settings plugin should not be used with this SquirrelMail version.");
        // revert domain
        bindtextdomain ('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');
        // display error
        error_box($error_message,$color);
        // close html and break script
        echo '</body></html>';
        exit;
    }

    global $perfolder_sort, $default_sort, $mailbox, $sort, $data_dir, $username;

    // sorting is not enabled
    if (! $perfolder_sort) return;

    if (sqgetGlobalVar('newsort',$newsort,SQ_FORM)) {
        $newsort = (int) $newsort;

        if ($newsort == $default_sort) {
            removePref($data_dir, $username, "fsort_$mailbox");
        } else {
            setPref($data_dir, $username, "fsort_$mailbox", $newsort);
        }

        $sort = $newsort;

        // 
        unset($newsort);
    } else {
        // overrides sorting with saved value
        $sort = getPref($data_dir, $username, "fsort_$mailbox", $default_sort);
    }
}

?>