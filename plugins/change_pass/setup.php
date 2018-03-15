<?php
    /* change_pass
     *
     * Licensed under the GNU GPL. For full terms see the file COPYING.
     *
     * $Id: setup.php,v 1.8 2009/05/05 03:09:22 indiri69 Exp $
     */

function squirrelmail_plugin_init_change_pass() {
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['optpage_register_block']['change_pass'] = 'change_pass_opt';
    $squirrelmail_plugin_hooks['optpage_set_loadinfo']['change_pass']   = 'change_pass_loadinfo';
}

function change_pass_info() {
    include_once(SM_PATH . 'plugins/change_pass/functions.php');
    return change_pass_info_real();
}

function change_pass_version() {
    $info = change_pass_info();
    return $info['version'];
}

function change_pass_opt() {
    include_once(SM_PATH . 'plugins/change_pass/functions.php');
    change_pass_option_link_do();
}

function change_pass_loadinfo() {
    include_once(SM_PATH . 'plugins/change_pass/functions.php');
    change_pass_loadinfo_real();
}
