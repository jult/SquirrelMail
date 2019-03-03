<?php
/**
 * init.mod
 * ---------
 * Squirrelspell module
 *
 * Copyright (c) 1999-2019 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Initial loading of the popup window interface.
 *
 * @author Konstantin Riabitsev <icon@duke.edu>
 * @version $Id: init.mod 14800 2019-01-08 04:27:15Z pdontthink $
 * @package plugins
 * @subpackage squirrelspell
 */

/**
 * See if we need to give user the option of choosing which dictionary
 * s/he wants to use to spellcheck his message.
 */
$langs=sqspell_getSettings(null);
$msg = '<form method="post">'
  . '<input type="hidden" name="MOD" value="check_me" />'
  . '<input type="hidden" name="sqspell_text" />'
  . '<p align="center">';
if (sizeof($langs)==1){
  /**
   * Only one dictionary defined by the user. Submit the form
   * automatically.
   */
  $onload="sqspell_init(true)";
  $msg .= _("Please wait, communicating with the server...")
    . '</p>'
    . "<input type=\"hidden\" name=\"sqspell_use_app\" value=\"$langs[0]\" />";
} else {
  /**
   * More than one dictionary. Let the user choose the dictionary first
   * then manually submit the form.
   */
  $onload="sqspell_init(false)";
  // this is a hack to avoid having to change the strings
  // in all our translations for this misspelled word
  global $squirrelmail_language;
  if (strpos($squirrelmail_language, 'en_') === 0)
    $msg .= 'Please choose which dictionary you would like to use to spell check this message:';
  else
    $msg .= _("Please choose which dictionary you would like to use to spellcheck this message:");
  $msg .= '</p><p align="center">'
    . '<select name="sqspell_use_app">';
  for ($i=0; $i<sizeof($langs); $i++){
    $msg .= "<option";
    if (!$i) {
      $msg .= ' selected="selected"';
    }
    $msg .= " value=\"$langs[$i]\"> " . _($langs[$i]) . "</option>\n";
  }
  $msg .= ' </select>'
    . '<input type="submit" value="' . _("Go") . '" />'
    . '</p>';
}
$msg .="</form>\n";
sqspell_makeWindow($onload, _("SquirrelSpell Initiating"), "init.js", $msg);

/**
 * For the Emacs weenies:
 * Local variables:
 * mode: php
 * End:
 * vim: syntax=php
 */

