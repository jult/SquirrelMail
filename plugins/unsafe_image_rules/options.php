<?php

/**
 * Unsafe Image Rules plugin - Options
 *
 * Handles options for the plugin. It is a shamless snatch of code from the
 * filters page - thanks guys!
 *
 * @copyright © 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: options.php,v 1.15 2006/01/26 22:27:29 jervfors Exp $
 * @package plugins
 * @subpackage unsafe_image_rules
 */

/* Path for SquirrelMail required files. */
if (!defined('SM_PATH')) define('SM_PATH','../../');

/* SquirrelMail required files. */
include_once(SM_PATH . 'plugins/unsafe_image_rules/functions.php');

/* Start options page display. */
displayPageHeader($color, 'None');

/* Get globals. */
sqgetGlobalVar('username', $username, SQ_SESSION);

/* Input data. */
sqgetGlobalVar('action', $action, SQ_GET);
sqgetGlobalVar('theid', $theid, SQ_GET);
sqgetGlobalVar('how', $how, SQ_POST);
sqgetGlobalVar('where', $where, SQ_POST);
sqgetGlobalVar('what', $what, SQ_POST);

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

/* If the user saves the preferences, act on it. */
if (isset($_POST['unsafe_image_rules_submit'])) {
    $theid = isset($_POST['theid']) ? $_POST['theid'] : 0;
    setPref($data_dir, $username, 'unsafe_image_rules'.$theid, $where.','.$what);
} elseif (isset($action) && $action == 'delete') {
    remove_trusted_unsafe_image($theid);
} elseif (isset($_POST['user_submit'])) {
    setPref($data_dir, $username, 'unsafe_image_rules_all',
            isset($_POST['uir_all']) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_addr',
            isset($_POST['uir_addr']) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_ids',
            isset($_POST['uir_ids']) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_trusted',
            isset($_POST['uir_trusted']) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_add_email',
            (isset($_POST['safe_sender']) && ($_POST['safe_sender'] == 'uir_add_email')) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_add_domain',
            (isset($_POST['safe_sender']) && ($_POST['safe_sender'] == 'uir_add_domain')) ? '1' : '0');
    setPref($data_dir, $username, 'unsafe_image_rules_add_ask',
            (isset($_POST['safe_sender']) && ($_POST['safe_sender'] == 'uir_add_ask')) ? '1' : '0');
    echo '<br /><div style="text-align: center;"><b>'._("Saved Trusted Options")."</b></div>\n";
}

/* Get the preferences. */
$unsafe_image_rules_list = load_unsafe_image_rules();
$unsafe_image_rules_all = getPref($data_dir, $username, 'unsafe_image_rules_all');
$unsafe_image_rules_addr = getPref($data_dir, $username, 'unsafe_image_rules_addr');
$unsafe_image_rules_ids = getPref($data_dir, $username, 'unsafe_image_rules_ids');
$unsafe_image_rules_trusted = getPref($data_dir, $username, 'unsafe_image_rules_trusted');
$unsafe_image_rules_add_email = getPref($data_dir, $username, 'unsafe_image_rules_add_email');
$unsafe_image_rules_add_domain = getPref($data_dir, $username, 'unsafe_image_rules_add_domain');
$unsafe_image_rules_add_ask = getPref($data_dir, $username, 'unsafe_image_rules_add_ask');

echo '<br />' .
    '<table width="95%" align="center" border="0" cellpadding="2" cellspacing="0">' .
    '<tr><td bgcolor="' . $color[0] . '">' .
    '<div style="text-align: center;"><b>' . _("Options") . ' -  ' . _("Unsafe Image Rules") . '</b></div>' .
    '</td></tr></table>' .
    '<br /><form method="post" action="options.php">' .
    '<div style="text-align: center;">' .
    '<table width="80%" cellpadding="2" cellspacing="0" border="0">';

/* Trust all images option. This might be disabled by admin. */
if (!(isset($unsafe_image_rules_disable_all) && $unsafe_image_rules_disable_all)) {
    echo '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Trust all sources:") . '</th>' .
        '<td valign="top"><input type="checkbox" name="uir_all"' .
        ((isset($unsafe_image_rules_all) && $unsafe_image_rules_all) ? ' checked="checked"' : '') .
        ' /></td><td><small>' .
        '<b>' . _("WARNING!") . '</b> ' .
        _("This is not recommended by the SquirrelMail Project Team.") . '<br />' .
        // i18n: %s inserts a complete link which doesn't need to be i18n.
        sprintf(_("Read %s for more details. There is a risk you may receive more spam if you check this option."),
                '<a href="http://www.squirrelmail.org/wiki/UnsafeImages">http://www.squirrelmail.org/wiki/UnsafeImages</a>') .
        '<br />' . _("If checked unsafe images are always seen.") . '<br />' .
        _("If this option is checked, the state of other options is irrelevant.") .
        "</small></td></tr>\n";
}

/* Trust addresses option. */
echo '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Trust sources in address book:") . '</th>' .
    '<td valign="top"><input type="checkbox" name="uir_addr"' .
    ((isset($unsafe_image_rules_addr) && $unsafe_image_rules_addr) ? ' checked="checked"' : '') .
    ' /></td><td><small>' .
    _("If checked, unsafe images are shown for messages from anyone in your address book.") .
    "</small></td></tr>\n";

/* Trust IDs option. */
echo '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Trust anything I send:") . '</th>' .
    '<td valign="top"><input type="checkbox" name="uir_ids"' .
    ((isset($unsafe_image_rules_ids) && $unsafe_image_rules_ids) ? ' checked="checked"' : '') .
    ' /></td><td><small>' .
    _("If checked, unsafe images are shown for messages sent by any of your identities.") . '<br />' .
    _("You might not want to set this if you forward Spam to other people, and then go back and read from your 'Sent' folder.") .
    "</small></td></tr>\n";

/* Trust only some option. */
echo '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Trust defined sources:") . '</th>' .
    '<td valign="top"><input type="checkbox" name="uir_trusted"' .
    ((isset($unsafe_image_rules_trusted) && $unsafe_image_rules_trusted) ? ' checked="checked"' : '') .
    ' /></td><td><small>' .
    _("If checked, unsafe images are shown for the sources shown below.") .
    "</small></td></tr>\n";

/* Quick Add options. */
echo '<tr><td colspan="3" align="center">&nbsp;</td></tr>' .
    '<tr><td colspan="3" align="center">' .
    _("When viewing a message with unsafe images, you can quickly add the sender's email address or domain to the Unsafe Image Rules by clicking the 'Always trust images from this sender' link. The following options set the default behavior when the address is being added.") .
    "</td></tr>\n" .
    '<tr><td colspan="3" align="center">&nbsp;</td></tr>' .
    '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Always add full email address:") . '</th>' .
    '<td valign="top"><input type="radio" value="uir_add_email" name="safe_sender"' .
    ((isset($unsafe_image_rules_add_email) && $unsafe_image_rules_add_email) ? ' checked="checked"' : '') .
    ' /></td><td><small>' . _("If selected the full email address of the sender will be added to the Unsafe Image Rules list.") .
    "</small></td></tr>\n" .
    '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Always add full domain:") . '</th>' .
    '<td valign="top"><input type="radio" value="uir_add_domain" name="safe_sender"' .
    ((isset($unsafe_image_rules_add_domain) && $unsafe_image_rules_add_domain) ? ' checked="checked"' : '') .
    ' /></td><td><small>' . _("If selected only the domain of the sender will be added to the Unsafe Image Rules list.") .
    '</small></td></tr>' .
    '<tr><th align="right" nowrap="nowrap" valign="top">' . _("Always ask:") . '</th>' .
    '<td valign="top"><input type="radio" value="uir_add_ask" name="safe_sender"' .
    ((isset($unsafe_image_rules_add_ask) && $unsafe_image_rules_add_ask) ? ' checked="checked"' : '') .
    ' /></td><td><small>' . _("Ask what to add to the Unsafe Image Rules list.") .
    "</small></td></tr>\n";

/* Submit for top changes. */
echo '<tr><td colspan="3" align="center"><input type="submit" name="user_submit" value="' .
    _("Save") . '" /></td></tr></table></div></form>';

/* New, done, then list existing ones. */
echo '<div style="text-align: center;">[<a href="options.php?action=add">' . _("Add New Rule") .
    '</a>] - [<a href="' . sqm_baseuri() . 'src/options.php">' . _("Done") . '</a>]</div><br />';

if (isset($action) && ($action == 'add' || $action == 'edit')) {
    if ( !isset($theid) ) {
        $theid = count($unsafe_image_rules_list);
    }
    echo '<form action="options.php" method="post">' .
        '<table width="95%"><tr><td align="center">' .
        _("Trust source if ") .
        '<select name="where">';

    $where_set = isset($unsafe_image_rules_list[$theid]['where']);

    $sel = (($where_set && $unsafe_image_rules_list[$theid]['where'] == 'From') ? ' selected="selected"' : '');
    echo "<option value=\"From\"$sel>" . _("From") . '</option>';

    $sel = (($where_set && $unsafe_image_rules_list[$theid]['where'] == 'To') ? ' selected="selected"' : '');
    echo "<option value=\"To\"$sel>" . _("To") . '</option>';

    $sel = (($where_set && $unsafe_image_rules_list[$theid]['where'] == 'Cc') ? ' selected="selected"' : '');
    echo "<option value=\"Cc\"$sel>" . _("Cc") . '</option>';

    $sel = (($where_set && $unsafe_image_rules_list[$theid]['where'] == 'To or Cc') ? ' selected="selected"' : '');
    echo "<option value=\"To or Cc\"$sel>" . _("To or Cc") . '</option>';

    $sel = (($where_set && $unsafe_image_rules_list[$theid]['where'] == 'Subject') ? ' selected="selected"' : '');
    echo "<option value=\"Subject\"$sel>" . _("Subject") . '</option>';

    echo '</select><select name="how">';

    $how_set = isset($unsafe_image_rules_list[$theid]['how']);

    $sel = (!$how_set ||
            ($how_set && $unsafe_image_rules_list[$theid]['how'] == 'contains') ? ' selected="selected"' : '');
    echo "<option value=\"contains\"$sel>" . _("Contains") . '</option>';

    $sel = (($how_set && $unsafe_image_rules_list[$theid]['how'] == 'regexp') ? ' selected="selected"' : '');
    echo "<option value=\"regexp\"$sel>" . _("Reg.Exp.") . '</option>';

    echo '</select>' .
        '<input type="text" size="32" name="what" value="';
    if (isset($unsafe_image_rules_list[$theid]['what'])) {
        echo htmlspecialchars($unsafe_image_rules_list[$theid]['what']);
    }
    echo '" /></td></tr><tr><td align="center">' .
        '<table><tr><td valign="center" align="right"><b><small>' . _("NB") . ':</small></b></td><td align="left">' .
        '<small>' .
        _("When specifying a Reg.Exp you must include delimeters.") . '<br />' .
        _("The 'Contains' check will be done with case-insensitive match.") .
        '</small></td></tr></table>' .
        '<input type="submit" name="unsafe_image_rules_submit" value="' . _("Submit") . '" />' .
        '<input type="hidden" name="theid" value="' . $theid . '" />' .
        '</td></tr></table></form></div>';
}

echo '<table border="0" cellpadding="3" cellspacing="0" align="center">';

for ($lp = 0; $lp < count($unsafe_image_rules_list); $lp++) {
    $clr = (($lp % 2)?$color[0]:$color[9]);
    echo '<tr bgcolor="' . $clr . '"><td><small>' .
        "[<a href=\"options.php?theid=$lp&action=edit\">" . _("Edit") . '</a>]' .
        '</small></td><td><small>' .
        "[<a href=\"options.php?theid=$lp&action=delete\">" . _("Delete") . '</a>]' .
        '</small></td><td>-</td><td>';
    // i18n: If [field] [Contains|Reg.Exp.] [user defined data]
    printf( _("If %s %s %s"),
            '<b>' . _($unsafe_image_rules_list[$lp]['where']) . '</b>',
            ($unsafe_image_rules_list[$lp]['how'] == 'regexp' ? _("Reg.Exp.") : _("Contains")),
            '<b>' . htmlspecialchars($unsafe_image_rules_list[$lp]['what']) . '</b>');
    if ($unsafe_image_rules_list[$lp]['how'] == 'regexp' &&
            !is_good_unsafe_image_regexp($unsafe_image_rules_list[$lp]['what'])) {
        echo '<br /><b>' . _("WARNING:") . '</b> ' . _("This doesn't look like a valid Reg.Exp.");
    }
    echo '</td></tr>';

}
echo '</table>';

?>
</body>
</html>