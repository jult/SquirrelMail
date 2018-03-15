<?php

/*
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: options.php,v 1.8 2009/12/09 00:21:11 indiri69 Exp $
 */

define('SM_PATH', '../../');
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');

$debug = false;

if(!sqgetGlobalVar('smtoken', $submitted_token, SQ_POST)) {
    $submitted_token = '';
}

// Make sure the plugin is activated
global $plugins;
if(!in_array('change_pass', $plugins)) {
    exit;
}

$min_pass_length = 0;
$max_pass_length = 99999999;

if (!@include(SM_PATH . 'config/config_change_pass.php')) {
     @include(SM_PATH . 'plugins/change_pass/config.php');
}

include_once(SM_PATH . 'plugins/change_pass/functions.php');

global $color;
sqgetGlobalVar('change_pass_form', $change_pass_form, SQ_POST);

$error_messages = array();
$showform = true;

if (isset($change_pass_form)) {
    sm_validate_security_token($submitted_token, 3600, TRUE);
    sq_change_text_domain('change_pass');
    sqgetGlobalVar('change_pass_old', $change_pass_old, SQ_POST);
    sqgetGlobalVar('change_pass_new', $change_pass_new, SQ_POST);
    sqgetGlobalVar('change_pass_verify', $change_pass_verify, SQ_POST);

    if(!isset($change_pass_old) || $change_pass_old == '') {
        $error_messages['cp_no_old'] = _("You must type in your current password.");
    }

    if(!isset($change_pass_new) || $change_pass_new == '') {
        $error_messages['cp_no_new'] = _("You must type in a new password.");
    }

    if(!isset($change_pass_verify) || $change_pass_verify == '') {
        $error_messages['cp_no_verify'] = _("You must also type in your new password in the verify box.");
    }

    if(!isset($error_messages['cp_no_new']) && !isset($error_messages['cp_no_verify'])) {
        if($change_pass_new != $change_pass_verify) {
            $error_messages['cp_new_mismatch'] = _("Your new password does not match the verify password.");
        } else {
            if (strlen($change_pass_new) < $min_pass_length ||
                strlen($change_pass_new) > $max_pass_length) {
                    $error_messages[] = sprintf(_("Your new password should be %s to %s characters long."),
                                                $min_pass_length, $max_pass_length);
            }
        }
    }

    $old_pass = sqauth_read_password();
    if(!isset($error_messages['cp_no_old']) && $change_pass_old != $old_pass) {
        $error_messages['cp_wrong_old'] = _("Your current password is not correct.");
    }

    if(count($error_messages) == 0) {
        $error_messages = change_pass_dochange($change_pass_old, $change_pass_new, $debug);
        if(count($error_messages) == 0) {
            $showform = false;
        }
    }
    sq_change_text_domain('squirrelmail');
}
displayPageHeader($color, '');
sq_change_text_domain('change_pass');

echo
    html_tag('table', "\n" .
        html_tag('tr', "\n" .
            html_tag('td', '<b>' . _("Change Password") . '</b>', 'center', $color[0])
        ),
        'center', $color[9], 'width="95%" border="0" cellpadding="1" cellspacing="0"') . "<br>\n";

if(count($error_messages) > 0) {
    echo html_tag('table', '', 'center', '', 'width="100%" border="0" cellpadding="1" cellspacing="0"');
    echo html_tag('tr');
    echo html_tag('td', '', 'center');
    echo html_tag('ul');
    foreach($error_messages as $line) {
        echo html_tag('li', htmlspecialchars($line), '', '', 'style="color: ' . $color[2] . '"');
    }
    echo html_tag('/ul');
    echo html_tag('tr', html_tag('td', '&nbsp;')) . "\n";
    echo html_tag('/table');
}

if($showform) {
    echo addForm(sqm_baseuri() . 'plugins/change_pass/options.php');
    echo addHidden('smtoken', sm_generate_security_token());
    echo
        html_tag('table', "\n" .
            html_tag('tr', "\n" .
                html_tag('td', _("Current Password:"), 'right') .
                html_tag('td', addPwField('change_pass_old', ''), 'left')
            ) .
            html_tag('tr', "\n" .
                html_tag('td', _("New Password:"), 'right') .
                html_tag('td', addPwField('change_pass_new', ''), 'left')
            ) .
            html_tag('tr', "\n" .
                html_tag('td', _("Verify New Password:"), 'right') .
                html_tag('td', addPwField('change_pass_verify', ''), 'left')
            ) .
            html_tag('tr', "\n" .
                html_tag('td', addSubmit(_('Change Password'), 'change_pass_form'), 'center', '', 'colspan="2"')
            ),
            'center', '', 'border="0" cellpadding="1" cellspacing="0"') . "\n";
    echo html_tag('/form');
    echo html_tag('/body');
    echo html_tag('/html');
}
sq_change_text_domain('squirrelmail');
