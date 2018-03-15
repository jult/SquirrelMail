<?php

/**
 * Unsafe Image Rules plugin - Setup
 *
 * @copyright © 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: setup.php,v 1.11 2006/01/28 09:31:00 jervfors Exp $
 * @package plugins
 * @subpackage unsafe_image_rules
 */

/* Path for SquirrelMail required files. */
if (!defined('SM_PATH')) define('SM_PATH','../../');

function squirrelmail_plugin_init_unsafe_image_rules() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['message_body']['unsafe_image_rules'] =
        'unsafe_image_rules_main';
    //  $squirrelmail_plugin_hooks['read_body_header']['unsafe_image_rules'] =
    //    'unsafe_image_rules_main';
    $squirrelmail_plugin_hooks['optpage_register_block']['unsafe_image_rules'] =
        'unsafe_image_rules_optpage_register_block';
    $squirrelmail_plugin_hooks['read_body_header_right']['unsafe_image_rules'] =
        'unsafe_image_rules_link';
}

function unsafe_image_rules_main() {
    include_once(SM_PATH . 'plugins/unsafe_image_rules/functions.php');
    unsafe_image_rules_main_do();
}

function unsafe_image_rules_optpage_register_block() {
    include_once(SM_PATH . 'plugins/unsafe_image_rules/functions.php');
    unsafe_image_rules_optpage_register_block_do();
}

function unsafe_image_rules_link() {
    include_once(SM_PATH . 'plugins/unsafe_image_rules/functions.php');
    unsafe_image_rules_link_do();
}

/**
 * Return the plugin's version number
 * @return string version number
 */
function unsafe_image_rules_version() {
    return '0.8';
}

?>