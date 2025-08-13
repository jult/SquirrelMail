<?php

/**
 * Unsafe Image Rules plugin - Add
 *
 * Adds an address to the rules list.
 *
 * @copyright © 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: add.php,v 1.10 2006/01/26 22:27:27 jervfors Exp $
 * @package plugins
 * @subpackage unsafe_image_rules
 */

/* Path for SquirrelMail required files. */
if (!defined('SM_PATH')) define('SM_PATH','../../');

/* SquirrelMail required files. */
include_once(SM_PATH . 'plugins/unsafe_image_rules/functions.php');

/* Start options page display. */
displayPageHeader($color, 'None');

/* Initialize variables */
$location = $startMessage = '';

/* Get globals. */
sqgetGlobalVar('username', $username, SQ_SESSION);

/* Input data. */
sqgetGlobalVar('address', $address, SQ_FORM);
sqgetGlobalVar('mailbox', $mailbox, SQ_FORM);
sqgetGlobalVar('passed_id', $passed_id, SQ_FORM);
sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
sqgetGlobalVar('startMessage', $startMessage, SQ_FORM);
sqgetGlobalVar('sort', $sort, SQ_FORM);
sqgetGlobalVar('how', $how, SQ_FORM);

/* Get preferences. */
$unsafe_image_rules_add_email = getPref($data_dir, $username, 'unsafe_image_rules_add_email');
$unsafe_image_rules_add_domain = getPref($data_dir, $username, 'unsafe_image_rules_add_domain');
$unsafe_image_rules_add_ask = getPref($data_dir, $username, 'unsafe_image_rules_add_ask');
$where = 'From,';

/**
 * This is a bit bad, but if $how is regexp then prefix $where with 'R', to make
 * stuff backwards compatable.
 */
if ($how == 'regexp') {
    $where = 'R' . $where;
}

/* Switch to our locale domain. */
bindtextdomain('unsafe_image_rules', SM_PATH . 'locale');
textdomain('unsafe_image_rules');

if (isset($address)) {
    echo '<br /><table width="95%" align="center" border="0" cellpadding="2" cellspacing="0">' .
        '<tr><td bgcolor="' . $color[0] . '">' .
        '<div style="text-align: center;"><b>' . _("Unsafe Image Rules") . ' - ' . _("Safe Sender") . '</b></div>' .
        '</td></tr></table>';

    if (isset($unsafe_image_rules_add_ask) && $unsafe_image_rules_add_ask) {
        if (!(isset($_POST['user_submit']))) {
            echo '<br /><div style="text-align: center;">' .
                _("Use the form below to modify the address that will be added to the Unsafe Image Rules.") . '<br />' .
                '<form method="post" action="add.php">' .
                '<input type="hidden" name="mailbox" value="' . $mailbox . '" />' .
                '<input type="hidden" name="passed_id" value="' . $passed_id . '" />' .
                '<input type="hidden" name="passed_ent_id" value="' . $passed_ent_id . '" />' .
                '<input type="hidden" name="startMessage" value="' . $startMessage . '" />' .
                '<input type="hidden" name="sort" value="' . $sort . '" />' .
                _("From") . '&nbsp;';

            /* Some duplicated code here from options.php. bleh */
            echo '<select name="how">' .
                '<option value="contains">' . _("Contains") . '</option>' .
                '<option value="regexp">' . _("Reg.Exp.") . '</option>' .
                "</select>&nbsp;" .
                '<input type="text" size="32" name="address" value="' .
                htmlspecialchars($address) .
                '" />&nbsp;&nbsp;<input type="submit" name="user_submit" value="' .
                _("Save") . '" />';
            echo '</div>';
            exit; // TODO: This breaks XHTML compliance
        }
    }

    if (isset($unsafe_image_rules_add_domain) && $unsafe_image_rules_add_domain) {
        $email_address_array = explode("@", trim($address));
        $ii = count($email_address_array)-1;
        $address = $email_address_array[$ii];
    }

    $unsafe_image_rules_list = load_unsafe_image_rules();

    $theid = count($unsafe_image_rules_list);

    setPref($data_dir, $username, "unsafe_image_rules" . $theid, $where . $address);

    $urlMailbox = urlencode($mailbox);
    $location = sqm_baseuri() . 'src/right_main.php?mailbox=' . $urlMailbox .
        '&amp;startMessage=' . $startMessage .
        '&amp;passed_id=' . $passed_id;
    if(isset($passed_ent_id)) {
        $location .= '&amp;passed_ent_id=' . $passed_ent_id;
    }
    if(isset($sort)) {
        $location .= '&amp;sort=' . $sort;
    }
    $msg = _("The following address has been successfully added to the Unsafe Images Rules:") .
        ' <strong>' . $address . '</strong>';
} else {
    $msg = _("I didn't seem to get a valid email address to add. This shouldn't happen!");
}
echo '<meta http-equiv="Refresh" content="5; URL=' . $location . '" />' .
    '<div style="text-align: center;"><br />' . $msg . '<br /><br />' .
    _("Sending you back to the message list in five seconds.") . '<br />' .
    // i18n: The first %s inserts HTML flr starting a link, and the second ends it.
    sprintf(_("If you are not automatically redirected, please click %shere%s."),
            '<a href="' . $location . '">', '</a>') .
    '</div><br />';

?>
</body>
</html>