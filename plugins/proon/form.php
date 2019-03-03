<?php

if (!defined('SM_PATH')) {
  define('SM_PATH', '../../');
}

include_once(SM_PATH . 'include/validate.php');

include_once(SM_PATH . 'plugins/proon/utils.php');
include_once(SM_PATH . 'plugins/proon/action.php');

if ($proon_site_eall) $old = error_reporting(E_ALL);
proon_form_gather_and_blat();
if ($proon_site_eall) error_reporting($old);

/**
 * Basic entry point into the form.  Sorts out form data.
 */
function proon_form_gather_and_blat() {
  $target = null;
  $anchor = null;
  $action = PROON_ACT_HELLO;
  $datelist = array();
  $sizelist = array();
  $countlist = array();
  $dateorsizeorcountlist = array();
  $unseenlist = array();
  $manuallist = array();
  $proonopts = array();

  foreach ($_POST as $key => $value) {
    $value = trim($value);
    if ($key == PROON_ACT_SAVE) {
      $action = $key;
      $anchor = $key;
    }
    else if ($key == PROON_ACT_PRUNE_ALL) {
      $action = $key;
      $anchor = $key;
    }
    else if ($key == PROON_ACT_SHOW_ALL_EFFECTS) {
      $action = $key;
      $anchor = $key;
    }
    else if (substr($key, 0, strlen(PROON_PRE_DATE)) == PROON_PRE_DATE) {
      if ($value != '') {
	$fname = proon_pull_foldername($key);
	$datelist[$fname] = $value;
	$dateorsizeorcountlist[$fname] = $value;
      }
    }
    else if (substr($key, 0, strlen(PROON_PRE_SIZE)) == PROON_PRE_SIZE) {
      if ($value != '') {
	$fname = proon_pull_foldername($key);
	$sizelist[$fname] = $value;
	$dateorsizeorcountlist[$fname] = $value;
      }
    }
    else if (substr($key, 0, strlen(PROON_PRE_COUNT)) == PROON_PRE_COUNT) {
      if ($value != '') {
	$fname = proon_pull_foldername($key);
	$countlist[$fname] = $value;
	$dateorsizeorcountlist[$fname] = $value;
      }
    }
    else if (substr($key, 0, strlen(PROON_PRE_UNSEEN)) == PROON_PRE_UNSEEN) {
      if ($value != '') {
	$fname = proon_pull_foldername($key);
	$unseenlist[$fname] = $value;
      }
    }
    else if (substr($key, 0, strlen(PROON_PRE_MANUAL)) == PROON_PRE_MANUAL) {
      if ($value != '') {
	$fname = proon_pull_foldername($key);
	$manuallist[$fname] = $value;
      }
    }
    else if (substr($key, 0, strlen(PROON_PRE_SHOW_EFFECT)) == PROON_PRE_SHOW_EFFECT) {
      $action = PROON_ACT_SHOW_EFFECT;
      $target = proon_pull_foldername($key);
      $anchor = $key;
    }
    else if (substr($key, 0, strlen(PROON_PRE_PRUNE_THIS)) == PROON_PRE_PRUNE_THIS) {
      $action = PROON_ACT_PRUNE_THIS;
      $target = proon_pull_foldername($key);
      $anchor = $key;
    }
    else if (substr($key, 0, strlen(PROON_PRE_OPTION)) == PROON_PRE_OPTION) {
      if ($value != '') {
	$proonopts[$key] = $value;
      }
    }
    // else forget about it ... not ours (shouldn't happen)
  }
  $p[PROON_O_F_DATE_SPAN] = $datelist;
  $p[PROON_O_F_SIZE_SPAN] = $sizelist;
  $p[PROON_O_F_COUNT_SPAN] = $countlist;
  $p[PROON_F_DATE_OR_SIZE_OR_COUNT] = $dateorsizeorcountlist;
  $p[PROON_O_F_TOSS_UNSEEN] = $unseenlist;
  $p[PROON_O_F_MANUAL_ONLY] = $manuallist;
  $p[PROON_P_OPTIONS] = $proonopts;
  $p[PROON_P_SITE] = array();

  proon_take_action_and_blat_page($action, $target, $p, $anchor);
}

/**
 * Utility method isolating the display of a one-time message for new users
 * and users having their auto_prune_sent preferences converted
 */
function proon_blat_onetime_box($onetime) {
  proon_locale_on();
  $howcome = 
    _("Unless you just clicked on a 'Pruning...' link, you have been automatically brought to this page because your site has installed a SquirrelMail plugin which provides automatic pruning of folders.  By default, no automatic pruning action will happen for you.");
  if ($onetime == 'apst') $howcome = 
    '<strong>' . _("ATTENTION!") . '</strong>  ' . 
    _("You have been brought to this page because one of your SquirrelMail preference items has been automatically converted.  (This is due to a change on this site from using the 'auto_prune_sent' plugin to using the upwardly compatible 'proon' plugin.)  See the entry for the 'Sent' folder in the Folder Table below (scroll down).  Your preferences have already been updated and saved, reflecting the settings as shown.  If you leave things as-is, some messages in your 'Sent' folder may be deleted on future sign-ons to SquirrelMail.");
  echo '<br><center><table width="70%" align=center border=2 cellpadding=2 cellspacing=0>';
  echo '<tr><td>'.
    '<em>' . $howcome . '</em>' .
    '<p 2>' .
    '<em>' . _("You may, of course, change any settings on this page and 'Save All'.  You can return to this page in the future by selecting the 'Pruning...' button (below your list of folders in the left-hand frame) or by selecting a similar link from the 'Options->Folder Preferences' page.  You should not be automatically brought to this page on future logins.") . '</em>';
  echo '</th></tr></table><br>' . "\n" .
    '</center>';
  proon_locale_off();
}

/**
 * Take indicated action (from form) and do the steps to redisplay the
 * form page.
 */
function proon_take_action_and_blat_page($action, $target, $p, $anchor) {

  $dirty = false;
  $notice = null;
  $imapStr = null;
  $onetime = false;

  proon_login($imapStr);
  global $delimiter;
  // For reasons not known to me, when we come here via the
  // one-time hook, the mailbox delimiter isn't yet set, which
  // leads to harmless warnings.  This gets rid of them.
  if (! $delimiter) {
    $delimiter = sqimap_get_delimiter($imapStr);
  }
  
  if ($action != PROON_ACT_HELLO) {
    // HELLO reads the prefs and gets the site prefs as a side effect.
    // Otherwise, fetch the site prefs and throw the rest away.
    $other_p = proon_get_prefs();
    $p[PROON_P_SITE] = $other_p[PROON_P_SITE];
  }
  if ($action == PROON_ACT_HELLO) {
    $p = proon_get_prefs();
    if ($p[PROON_P_PREFS_SEEN] == 0) {
      // No matter how we got here, make a mark so that we don't
      // bother with the one-time behavior again.
      proon_set_prefs($p);
      $onetime = 'all';
      global $proon_log_verbose;
      $proon_log_verbose && proon_log('proon form first time');

      global $proon_convert_apst;
      if ($proon_convert_apst) {
	global $data_dir, $username;
	$apst = getPref($data_dir, $username, 'auto_prune_sent_threshold');
	if (is_numeric($apst)  &&  $apst > 0) {
	  $onetime = 'apst';
	  include_once(SM_PATH . 'plugins/proon/hook.php');
	  proon_actually_convert_apst($apst);
	}
      }
      $p = proon_get_prefs();
    }
  }
  else if ($action == PROON_ACT_SAVE) {
    $dirty = proon_action_save($p, $outcome, $notice);
  }
  else {

    $dirty = proon_is_dirty($p);

    if ($action == PROON_ACT_SHOW_EFFECT) {
      proon_action_effect($p, $target, $outcome, $imapStr);
    }
    else if ($action == PROON_ACT_SHOW_ALL_EFFECTS) {
      proon_action_alleffects($p, $outcome, $notice, $imapStr);
    }
    else if ($action == PROON_ACT_PRUNE_THIS) {
      proon_action_now($p, $target, $outcome, $imapStr);
    }
    else if ($action == PROON_ACT_PRUNE_ALL) {
      proon_action_pruneall($p, $outcome, $notice, $imapStr, true);
    }
  }

  proon_blat_top_of_page($notice, $anchor, $onetime);
  proon_blat_anchor(PROON_ANCHOR_OPTS);
  proon_blat_save_button($dirty, true);
  proon_blat_proon_options($p, $help);
  proon_blat_anchor(PROON_ANCHOR_FOLDERS);
  proon_blat_doall_buttons(true);
  proon_blat_folder_table($action, $p, $outcome, $imapStr);
  proon_blat_doall_buttons();
  proon_blat_save_button($dirty);
  proon_blat_anchor(PROON_ANCHOR_HELP);
  proon_blat_help($help);
  proon_blat_save_button($dirty);
  proon_blat_anchor(PROON_ANCHOR_BOTTOM);
  proon_blat_bottom_of_page($anchor);

  proon_logout($imapStr);
}

/**
 * As the name implies, this takes care of things at the top of
 * the proon form.
 */
function proon_blat_top_of_page($notice, $anchor, $onetime) {
  global $color;
  displayPageHeader($color, 'None');

  // This is a little UI trickery to get repositioned on the button
  // that was just clicked.  Alas, it puts this refresh in the body
  // of the HTML, which isn't exactly legit.  The redirect to the anchor
  // works anyhow in IE but doesn't work in Firefox.
  if ($anchor) {
    $redir = '<meta http-equiv="Refresh" content="0; url=#'.$anchor.'">';
    echo $redir;
  }

  if ($onetime !== false) {
    proon_blat_onetime_box($onetime);
  }
  if ($notice) error_box($notice, $color);

  proon_locale_on();
  echo 
    '<br>' .
    '<center>' .
    '<table width="95%" align=center border=0 cellpadding=2 cellspacing=0>'.
    "<tr><th bgcolor=\"$color[0]\">".
    PROON_UI_PAGE_TITLE .
    '</th></tr></table>' . "\n" .
    '</center>';

  echo '<br><center><table width="70%" align=center border=0 cellpadding=2 cellspacing=0>';
  echo '<tr><td>'.
    _("This page allows you to conveniently prune messages from any or all folders by using a variety of criteria.  Messages can be pruned manually from this page, or they can be pruned automatically at sign-on and every so often.  Before using the automatic pruning, it would be a good idea to test your settings manually from this page to be sure they do what you want them to do.  Automatic pruning is enabled by giving an appropriate value for the 'Recurring Prune Interval' option, though sign-on pruning is done even if you don't give a value for that.") .
    '<p 2>' .
    '<strong>' . _("CAUTION!") . '</strong><em>  ' . _("When misconfigured, this tool can delete a lot of messages in a hurry.  If you haven't used it before, you should read through the help and explanations given in the bottom part of this page before you do use it.  Configured properly, it's a safe and convenient tool.") . '</em>';
  echo '</th></tr></table><br>' . "\n" .
    '</center>';

  echo '<br><center><form method=post action="form.php">'. "\n";
  proon_locale_off();
}

/**
 * Bottom matter for proon form.
 */
function proon_blat_bottom_of_page($anchor) {
  include_once(SM_PATH . 'plugins/proon/setup.php');
  echo '</center></form>' . "\n";
  echo '<hr><center><small><a href="http://www.squirrelmail.org/plugin_view.php?id=251" target=_blank>' . proon_name() . ' v' . proon_version() . '</a></small></center>'."\n";
  // This is a little UI trickery to get repositioned on the button
  // that was just clicked.
  // The redirect to the anchor
  // works in IE but doesn't work in Firefox.
  echo "</body>\n";
  $re = ($anchor) ? '<meta http-equiv="Refresh" content="0; url=#'.$anchor.'">' : '';
  //echo $re;
  echo "\n</html>\n";
}

/**
 * Some common logic factored out of rendering user option help items.
 */
function proon_blat_single_help_item(&$item, &$description, $do_anchor=false) {
  echo '<tr>';

  echo '<td align="right" valign="top"><hr>';

  if ($do_anchor) {
    $b64item = base64_encode($item);
    echo '<sup><a name="help_' . $b64item . '" href="#doit_' . $b64item . '">' .
      PROON_UI_BACKFROM_HELP .
      '</a></sup>&nbsp;';
  }

  echo '<em>' . $item . '</em>';
  echo '</td>';

  echo '<td align="left" valign="top"><hr>' .
    $description .
    '</td></tr>' . "\n";

}

/**
 * A grand round-up of all the lower-part-of-the-page help for proon.  The
 * verbose stuff here is all about help for the folder table.  The individual
 * user preference items are dumped out in the small loop near the end of
 * the function.
 */
function proon_blat_help(&$help) {
  proon_locale_on();
  echo '<table width="80%" align=center border=0 cellpadding=6 cellspacing=0>' . "\n";
  echo '<tr><th colspan=99 align="center"><hr></th></tr>';
  echo '<tr><th colspan=99 align="center">' . PROON_UI_HELP_ALL . '</th></tr>';
  echo '<tr><th colspan=99 align="center"><hr></th></tr>';
  echo '<tr><td colspan=99 align="left">' .
    _("Your local SquirrelMail administrator may have specified site settings for one or more options or per-folders items.  In such a case where there is a site setting, it supersedes the user setting (except as noted for particular items below).  Since the site settings are administered separately, your user settings are shown and can be edited even if site settings supersede them.  The site settings, if any, are shown immediately below the corresponding user setting in the User Preferences table and the Folder Table.");
  echo '<hr></td></tr>';
  echo '<tr><th colspan=99 align="center">' . PROON_UI_HELP_FOLDER . '</th></tr>';
  echo '<tr><td colspan=99 align="left">' .
    _("This is an explanation for the user preferences and per-folder data which control selective automatic pruning of folders.  Pruning means the deletion of messages either because they are older than a certain date or to bring a folder to within a certain total size limit or number of messages.<ul><li>Pruning first considers message dates (if there is a user-specified date span value for that folder).  A message's date is the time since it was received by the mail server (this so-called 'internal date' is preserved if you move a message between folders).  Messages are deleted if they have an internal date older than the age indicated by the date span value.</li><li>Pruning next considers total folder size (if there is a user-specified folder size span).  If the folder is over that size limit, additional messages are pruned until the folder is at or below it.</li><li>Pruning finally considers the number of messages in the folder (if there is a user-specified count span).  If a folder has more than that many messages, additional messages are pruned until the folder is at or below the limit.</li></ul><p>In all those pruning cases, unread messages are normally protected and not pruned.  That protection can be removed on a folder-by-folder basis.  Pruning behavior may be flexibly controlled using a variety of other user preferences, each of which is described more fully below.  Unsubscribed and non-existent folders are listed if there is any user preference or site preference given for that folder; this is to avoid a surprise if you suddenly start using a folder of some name and would not otherwise realize that it had pruning options.") .
    '<p 2>' .
    _("Here are some examples of fairly typical settings.") .
    '<ul><li>' . _("Set a recurring pruning interval of 24 hours, just in case you stay logged on for a long time.") .
    '</li><li>' . _("For the 'Sent' folder, prune messages older than a week, including unseen messages.  This assumes you don't use your 'Sent' folder as a general collecting area.  If you haven't needed to retrieve something from there in a week (because you forgot to save a copy elsewhere), it can be tossed out.") .
    '</li><li>' . _("For the 'Trash' folder, prune messages older than 3 days.  Prune the 'Trash' folder to no more than 500 kilobytes or 20 total messages.  Include unseen messages in the pruning.") .
    '</li><li>' . _("For the 'Drafts' folder, prune anything older than 6 months on the grounds that if you haven't gotten around to finishing a note in that amount of time, you're never going to.") .
    '</li><li>' . _("For a 'might be spam' quarantine folder, prune messages older than 30 days, and prune the folder to no more than 2 megabytes.  Again, do not protect unseen messages.") .
    '</li><li>' . _("For a high-traffic mailing list folder, which you only skim from time to time, prune messages older than a week, including unseen messages.") .
    '</li></ul>';
  echo '<hr></td></tr><tr>' .
    '<th align="right">' . PROON_UI_ITEM . '</th>' . 
    '<th align="left">' . PROON_UI_DESCRIPTION . '</th>' . 
    '</tr>' . "\n";

  $item = PROON_UI_DATE_SPAN;
  $description = _("A date span is a relative time duration and is specified as a combination of days and hours.  The overall time duration may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  A simple number is interpreted as a number of days.  If there is a slash ('<code>/</code>'), the number after the slash is interpreted as a number of hours. If days and hours are both specified, they are simply added together.  Some examples are shown in the table below.") .
    '<br><p><table align="center" border=1>' .
    '<tr><td align="center"><code>6</code></td><td>'._("6 days").'</td></tr>' .
    '<tr><td align="center"><code>6/</code></td><td>'._("6 days").'</td></tr>' .
    '<tr><td align="center"><code>6/2</code></td><td>'._("6 days plus 2 hours").'</td></tr>' .
    '<tr><td align="center"><code>/2</code></td><td>'._("2 hours").'</td></tr>' .
    '<tr><td align="center"><code>6.25</code></td><td>'._("6 and one quarter days").'</td></tr>' .
    '<tr><td align="center"><code>/3.3333</code></td><td>'._("3 and one third hours").'</td></tr>' .
    '<tr><td align="center"><code>6.25/3.3333</code></td><td>'._("6 and one quarter days plus 3 and one third hours").'</td></tr>' .
    '<tr><td align="center"><code>0</code></td><td>'._("Same as blank").'</td></tr>' .
    '</table>';
  $description .= '<p>' . _("If there is both a site setting and a user setting for a given folder, the minimum of the two values is used.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_SIZE_SPAN;
  $description = _("A size span counts total bytes in a folder.  The size may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  A size consists of a number and an optional suffix character.  The suffix character indicates a multiplier, as shown in the table below.  A number without a suffix gets a default suffix of 'm'.") .
    '<br><p><table align="center" border=1>' .
    '<tr><td align="center"><code>B</code> <i>or</i> <code>b</code></td><td>'._("uppercase or lowercase, 1 (bytes)").'</td></tr>' .
    '<tr><td align="center"><code>k</code></td><td>'._("lowercase, 1000 (the layman's kilobytes)").'</td></tr>' .
    '<tr><td align="center"><code>K</code></td><td>'._("uppercase, 1024 (the geek's kilobytes)").'</td></tr>' .
    '<tr><td align="center"><code>m</code></td><td>'._("lowercase, 1,000,000 (the layman's megabytes)").'</td></tr>' .
    '<tr><td align="center"><code>M</code></td><td>'._("uppercase, 1024*1024 (the geek's megabytes)").'</td></tr>' .
    '<tr><td align="center"><i>'._("(none)").'</i></td><td>'._("same as 'm'").'</td></tr>' .
    '</table>';
  $description .= '<p>' . _("If there is both a site setting and a user setting for a given folder, the minimum of the two values is used.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_COUNT_SPAN;
  $description = _("A count span counts messages in a folder.  The count may not be negative.  For safety, a value of 0 is treated the same as no value being specified.  Unlike a date span or a size span, a count span is always just a simple numeric value with no additional type of notation.");
  $description .= '<p>' . _("If there is both a site setting and a user setting for a given folder, the minimum of the two values is used.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_UNSEEN_TOO;
  $description = _("If this item is selected for a given folder, unseen (i.e., unread) messages have no special protection against pruning.  If not selected (the default), then the pruning process will not prune any unseen messages in the corresponding folder.  You might consider allowed unseen messages to be pruned from spam quanantine folders and folders which receive mailing list traffic which you don't always read.  You should be especially careful of the date, size, and count spans you specify for folders with this box checked.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_MANUAL_ONLY;
  $description = _("If this item is selected for a given folder, the folder will not be automatically pruned.  It will only be pruned through manual action by you.  Manual action means selecting either 'Prune All Folders' or 'Prune Now' from the pruning options form.  Automatic pruning means sign-on pruning as well as periodic pruning (if that option is selected).");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_SHOW;
  $description = _("This action button simulates pruning of the associated folder.  The number of messages which would have been pruned is displayed.  If there is not at least one span value specified for the folder, an error message is shown.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_PRUNE;
  $description = _("This action button immediately prunes the associated folder.  The number of messages which were pruned is displayed.  If there is not at least one span value specified for the folder, an error message is shown and no messages are pruned.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_SHOW_ALL;
  $description = _("This action button is similar to the 'Show Effect' action button, except that the entire list of folders (and their individual settings) is used.  Folders without at least one span value specified are silently skipped.  The numbers reported for the Trash folder do not take into account any messages that might be sent to the Trash folder as a result of pruning other folders.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_PRUNE_ALL;
  $description = _("In effect, this action is the same as automatic pruning, except that it's triggered manually (and email reports are not made).  This action button is similar to the 'Prune Now' action button, except that the entire list of folders (and their invididual settings) is used.  Folders without at least one span value specified are silently skipped.  If some folders have erroneous values, an error message is shown for them, but other (non-error) folders are still pruned.");
  proon_blat_single_help_item($item, $description, true);

  $item = PROON_UI_SAVE;
  $description = _("This action button saves all user preference values and per-folder settings.  If there are errors detected in the user options or per-folder settings, the save is not done.  As an aid to the user, the button has a different appearance when there are known differences between the values on this page and the values that have already been saved in the past.  That really only applies when the page has been redrawn after one of the action buttons.  The button appearance is not dynamically updated as you edit values on the page.");
  proon_blat_single_help_item($item, $description, true);

  echo '<tr><th colspan=99 align="center"><hr></th></tr>';
  echo '<tr><th colspan=99 align="center">' . PROON_UI_USER_PREFS . '</th></tr>';
  echo '<tr><td colspan=99 align="left">' . 
    _("The following table describes user preferences that can affect how pruning is done or not done for you in particular.  The behavior might be changed or limited by site settings controlled by your local administrator.  Descriptions here are in the same order as the User Preferences form above.") . 
    '<hr></td></tr>';
  echo '<tr>' .
    '<th align="right">' . PROON_UI_ITEM . '</th>' . 
    '<th align="left">' . PROON_UI_DESCRIPTION . '</th>' . 
    '</tr>' . "\n";
  foreach ($help as $item => $description) {
    proon_blat_single_help_item($item, $description, true);
  }
  echo '</table>' . "\n";
  proon_locale_off();
}

/**
 * Common logic for displaying the user preferences table items.
 */
function proon_blat_single_proon_option(&$label, &$explanation, &$input, $clr, $site_pref = '&nbsp;') {
  if ($clr !== null) {
    global $color;
    echo "<tr bgcolor=$color[$clr]>";
  }
  else {
    echo "<tr>";
  }

  echo '<td align="right"><em>' .
    $label .
    '</em>';

  $syt_label = '';
  $syt_value = '';
  if ($site_pref) {
    $syt_label = '<br>' . SYTa . PROON_UI_SITE_PREF . SYTz . '&nbsp;';
    $syt_value = '<br>' . SYTa . $site_pref . SYTz;
  }
  $b64label = base64_encode($label);
  echo '&nbsp;<sup><a name="doit_' . $b64label . '" href="#help_' . $b64label . '">' .
    PROON_UI_GOTO_HELP . '</a></sup>'. $syt_label .'</td>';

  if ($site_pref == '') $site_pref = '&nbsp;';
  echo '<td align="left">' .
    $input . $syt_value .
    '</td>' .
    '</tr>' . "\n";
}

/**
 * Grand round-up of the user preferences table.  All the help strings are
 * inline, but they're actually dumped out later.
 */
function proon_blat_proon_options(&$p, &$help) {
  $proonopts = $p[PROON_P_OPTIONS];
  $proonsite = $p[PROON_P_SITE];
  $no_color = proon_effective_option($p, PROON_O_U_DIS_COLOR);
  $clr = null;
  proon_locale_on();
  echo '<table align=center border=1 cellpadding=2 cellspacing=0>' . "\n";
  echo '<tr><th colspan=99 align=center><font size="+1">' . PROON_UI_USER_PREFS . '</font></th></tr>';
  echo '<tr>' .
    '<th align="right">' . PROON_UI_ITEM . '</th>' . 
    '<th align="left">' . PROON_UI_SETTING . '</th>' . 
    '</tr>' . "\n";



  $label = PROON_UI_LOGIN_N;
  $explanation = _("Ordinarily, there is one pruning attempt at SquirrelMail sign-on time.  If you want the sign-on prunings to be done less often, you can specify a number here.  For example, a value of 3 means 'every 3rd sign-on'.  No value specified or a value of 0 means 'every sign-on'.  The local SquirrelMail site administrator may have specified a maximum value for sign-on pruning frequency, in which case that takes precedence if it is lower.");
  $opt = PROON_O_U_LOGIN_N;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  $input = '<input type="text" size="7" name="' .$opt. '" value="' .$val. '"/>';
  if ($syt) $syt = SYTa . $syt . ' ' . _("maximum") . SYTz;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;


  $label = PROON_UI_INTERVAL;
  $explanation = _("Pruning can be done manually from this options page, or it can be done periodically and automatically.  This item specifies the recurring time period.  The format is the same as for the date span values for individual folders.  If not specified, no automatic/periodic pruning will be done; so, you can think of this field as an on/off switch for automatic pruning.  For safety, a value of 0 is treated the same as no value specified.  The local SquirrelMail site administrator may have specified a minimum pruning interval, in which case that takes precedence if it is lower.  The recurring interval is measured from the SquirrelMail session sign-on, so automatic pruning attempts will be made at the specified intervals thereafter.  The actual pruning happens coincident with some screen update activity, so an idle SquirrelMail session will not be doing any automatic pruning.");
  $opt = PROON_O_U_PRUNE_INTERVAL;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  $input = '<input type="text" size="7" name="' .$opt. '" value="' .$val. '"/>';
  if ($syt) $syt = SYTa . $syt . ' ' . _("minimum") . SYTz;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_VIA_TRASH;
  $explanation = _("If this box is checked, messages pruned from other folders will be sent to the Trash folder.  Messages pruned from the Trash folder will be discarded.  If this box is not checked, messages pruned from all folders will be discarded immediately.  This setting is independent of the overall SquirrelMail setting for using the Trash folder when deleting messages.");
  $ch = '';
  $opt = PROON_O_U_VIA_TRASH;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_ENABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_TRASH_ORDER;
  $explanation = _("If any pruning is requested for the Trash folder along with other folders, this preference controls the ordering.  'First' means that the Trash folder is pruned first, so at the end of a pruning session, it will hold the messages pruned from other folders.  'Last' means that the Trash folder is pruned last, so any messages moved there via pruning will then be subject to a second pruning at the end.  'Natural' means that the Trash folder will be pruned according to its natural order in the list of folders; in other words, it gets no special treatment with respect to ordering.  If no choice is made, the default is 'First'.  This setting makes no practical difference unless 'Prune via Trash' is selected.");
  $fch = ''; $lch = ''; $nch = '';
  $opt = PROON_O_U_TRASH_TIME;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : '';
  if      ((string)$syt == PROON_O_U_T_FIRST) $syt = PROON_UI_FIRST;
  else if ((string)$syt == PROON_O_U_T_LAST) $syt = PROON_UI_LAST;
  else if ((string)$syt == PROON_O_U_T_NATURAL) $syt = PROON_UI_NATURAL;
  else if ($syt != '') {
    proon_log('Bad site preference value for $' . $opt . ".  Value is $proonsite[$opt].");
  }
  if      ((string)$val == PROON_O_U_T_FIRST) $fch = " checked ";
  else if ((string)$val == PROON_O_U_T_LAST) $lch = " checked ";
  else if ((string)$val == PROON_O_U_T_NATURAL) $nch = " checked ";
  $to = PROON_O_U_T_FIRST;
  $input  = '            <input type="radio" name="'.$opt.'" value="'.$to.'"' .$fch .'/>' . PROON_UI_FIRST;
  $to = PROON_O_U_T_NATURAL;
  $input .= '&nbsp;&nbsp;<input type="radio" name="'.$opt.'" value="'.$to.'"' .$nch .'/>' . PROON_UI_NATURAL;
  $to = PROON_O_U_T_LAST;
  $input .= '&nbsp;&nbsp;<input type="radio" name="'.$opt.'" value="'.$to.'"' .$lch .'/>' . PROON_UI_LAST;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_UNSUBSCRIBED;
  $explanation = _("If this box is checked, pruning may also consider unsubscribed folders.  If not checked, only subscribed folders are considered, whether for manual pruning or automatic pruning (you can still use the per-folder 'Show Effect' or 'Prune Now' buttons).  This may be handy if you have unsubscribed folders which receive messages in some way other than by manually refiling things to them.  You can only add settings for a folder by subscribing to it, at least temporarily, but settings for unsubscribed folders are used if this box is checked.");
  $ch = '';
  $opt = PROON_O_U_UNSUBSCRIBED;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_ENABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_DATE_PRUNING;
  $explanation = _("If this disable box is checked, pruning by message date will not be done.  Any per-folder values for the date span column will still be displayed, but they cannot be updated.");
  $ch = '';
  $opt = PROON_O_U_DIS_DATE;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_DISABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_SIZE_PRUNING;
  $explanation = _("If this disable box is checked, pruning by message size will not be done.  Any per-folder values for the size span column will still be displayed, but they cannot be updated.");
  $ch = '';
  $opt = PROON_O_U_DIS_SIZE;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_DISABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_COUNT_PRUNING;
  $explanation = _("If disable this box is checked, pruning by message count will not be done.  Any per-folder values for the count span column will still be displayed, but they cannot be updated.");
  $ch = '';
  $opt = PROON_O_U_DIS_COUNT;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($val == 'true') $ch = " checked ";
  if ($syt) $syt = SYTCH;
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_DISABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_SC_ORDER;
  $explanation = _("When considering which messages to prune by size span and/or by count span, there are two possible orders in which to consider them.  They can be considered by date, in which case messages are pruned from oldest to newest until the size or message count limit for the folder is met.  Or, they can be considered by size, in which case messages are pruned from largest to smallest until the size or message count limit is met.  If neither is selected, the default order is by date.");
  $ach = ''; $sch = '';
  $opt = PROON_O_U_SC_ORDER;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : '';
  if      ((string)$syt == 'date') $syt = PROON_UI_BY_DATE;
  else if ((String)$syt == 'size') $syt = PROON_UI_BY_SIZE;
  else if ($syt != '') {
    proon_log('Bad site preference value for $' . $opt . ".  Value is $proonsite[$opt].");
  }
  if      ((string)$val == 'date') $ach = " checked ";
  else if ((string)$val == 'size') $sch = " checked ";
  $input  =
    '<input type="radio" name="'.$opt.'" value="date"' .$ach .'/>' . PROON_UI_CONSIDER_DATE;
  $input .= '&nbsp;&nbsp;' .
    '<input type="radio" name="'.$opt.'" value="size"' .$sch .'/>' . PROON_UI_CONSIDER_SIZE;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_MESSAGE_AFTER;
  $explanation = _("If this box is checked, a report summarizing automatic pruning will be put into the INBOX as a new message.  An email report is not made if no messages were pruned and no errors occurred.");
  $ch = '';
  $opt = PROON_O_U_MESSAGE_AFTER;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_ENABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_SCREEN_AFTER;
  $explanation = _("If this box is checked, a report summarizing automatic pruning will be made part of the message-list panel.  In contrast to the email notification, a report is made even if no messages were pruned and no errors occurred.  The on-screen notification contains a more verbose version of the same information as the email notification.");
  $ch = '';
  $opt = PROON_O_U_SCREEN_AFTER;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_ENABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_USE_COLORS;
  $explanation = _("This options page is normally constructed using colors from the user-chosen SquirrelMail theme, both to make a pleasing display and to highlight important information.  For some themes, this actually makes things on this page difficult to read.  If this box is checked, this options page will be built without most of the colors.");
  $ch = '';
  $opt = PROON_O_U_DIS_COLOR;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_DISABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  $label = PROON_UI_PRUNE_LINK;
  $explanation = _("A button is normally placed in the SquirrelMail left pane, beneath the list of folders, which enables you to quickly get to this page.  If this box is checked, that button is not drawn in the left pane.  You can still reach this page by selecting 'Options', 'Folder Preferences', and 'Options for Pruning Folders'.");
  $ch = '';
  $opt = PROON_O_U_PRUNE_LINK;
  $val = isset($proonopts[$opt]) ? $proonopts[$opt] : '';
  $syt = isset($proonsite[$opt]) ? $proonsite[$opt] : false;
  if ($syt) $syt = SYTCH;
  if ($val == 'true') $ch = " checked ";
  $input = '<input type="checkbox" name="' .$opt. '" value="true"' .$ch. '/>'.PROON_UI_DISABLED;
  
  $clr = (($clr == 0) ? 9 : 0);
  if ($no_color) $clr = null;
  proon_blat_single_proon_option($label, $explanation, $input, $clr, $syt);
  $help[$label] = $explanation;



  echo '</table>' . "\n";
  proon_locale_off();
}

/**
 * Renders the "Save All" button.
 */
function proon_blat_save_button($dirty, $do_anchor=false) {
  global $color;
  $dirtycolor = ($dirty ? ' bgcolor=' . $color[2] : '');
  $dirtymark = ($dirty ? ' * ' : '');
  $label = PROON_UI_SAVE;
  $anchor = base64_encode($label);
  $h = '';
  if ($do_anchor) $h = '&nbsp;<sup><a href="#help_' . $anchor . '" name="doit_' . $anchor . '">' . PROON_UI_GOTO_HELP . '</a></sup>';
  echo 
    '<a name='.PROON_ACT_SAVE.'/>' .
    '<table ' . $dirtycolor . '><tr>' .
    '<td>' . $dirtymark . '</td>' .
    '<td><input type="submit" name="save" value="' . $label . '"/>' . $h . '</td>';
  echo '<td>' . $dirtymark . '</td>' .
    '</table>' . "<br>\n";
}

/**
 * Renders the "Show All Effects" and "Prune All" buttons.
 */
function proon_blat_doall_buttons($do_anchor=false) {
  echo 
    '<br><a href="../../src/left_main.php" target="left">' .
    PROON_UI_REFRESH_RIGHT . '</a>' . "\n" .
    '<br>' .
    '<a name='.PROON_ACT_SHOW_ALL_EFFECTS.'/>' .
    '<a name='.PROON_ACT_PRUNE_ALL.'/>';

  $labelE = PROON_UI_SHOW_ALL;
  $anchorE = base64_encode($labelE);
  $hE = '';
  $labelP = PROON_UI_PRUNE_ALL;
  $anchorP = base64_encode($labelP);
  $hP = '';
  if ($do_anchor) {
    $hE = '&nbsp;<sup><a href="#help_' . $anchorE . '" name="doit_' . $anchorE . '">' . PROON_UI_GOTO_HELP . '</a></sup>';
    $hP = '&nbsp;<sup><a href="#help_' . $anchorP . '" name="doit_' . $anchorP . '">' . PROON_UI_GOTO_HELP . '</a></sup>';
  }


  echo '<p><input type="submit" name="'.PROON_ACT_SHOW_ALL_EFFECTS.'" value="' . $labelE . '"/>' .
    $hE .
    '&nbsp;&nbsp;&nbsp;' .
    '<input type="submit" name="'.PROON_ACT_PRUNE_ALL.'" value="' . $labelP . '"/>' .
    $hP .
    '<br><p>' . "\n";
}

/**
 * Renders the "Show Effect" button in the leftmost column of the folder table.
 */
function proon_blat_effect_button($fid, $do_anchor, $buttons, $s) {
  $label = PROON_UI_SHOW;
  $anchor = base64_encode($label);
  $h = '';
  if ($do_anchor) $h = '&nbsp;<sup><a href="#help_' . $anchor . '" name="doit_' . $anchor . '">' . PROON_UI_GOTO_HELP . '</a></sup>';
  echo '<td align="left">';
  if ($buttons) {
    echo '<a name='.PROON_PRE_SHOW_EFFECT.$fid.'/>' .
      '<input type="submit" name="' .PROON_PRE_SHOW_EFFECT.$fid. '" value="' . $label . '"/>' .
      $h;
  }
  else {
    echo '&nbsp;';
  }
  if ($s) {
    echo '<br>&nbsp;' . SYTa . $s . SYTz;
  }
  echo "</td>\n";
}

/**
 * Renders the "Prune Now" button in the rightmost column of the folder table.
 */
function proon_blat_now_button($fid, $do_anchor, $buttons, $s) {
  $label = PROON_UI_PRUNE;
  $anchor = base64_encode($label);
  $h = '';
  if ($do_anchor) $h = '<sup><a href="#help_' . $anchor . '" name="doit_' . $anchor . '">' . PROON_UI_GOTO_HELP . '</a></sup>&nbsp;';
  echo '<td align="right" >';
  if ($buttons) {
    echo '<a name='.PROON_PRE_PRUNE_THIS.$fid.'/>' .
      $h .
      '<input type="submit" name="' .PROON_PRE_PRUNE_THIS.$fid. '" value="' . $label . '"/>';
  }
  else {
    echo '&nbsp;';
  }
  if ($s) {
    echo '<br>' . SYTa . $s . SYTz . '&nbsp;';
  }
  echo "</td>\n";
}

/**
 * Controlling function for rendering the 3-part folder table (normal, 
 * unsubscribed, non-existent folders).
 */
function proon_blat_folder_table($action, &$p, &$outcome, &$imapStr) {
  global $color;

  echo '<table border=1 cellpadding=3 cellspacing=0>' . "\n";
  echo '<tr><th colspan=99 align=center><font size="+1">' . PROON_UI_FOLDER_TABLE . '</font></th></tr>';
  echo '<tr><th colspan=99 align=center><font size="+1">' . PROON_UI_SFOLDER_TABLE . '</font></th></tr>';

  proon_folder_table_column_headers(true);

  $clr = '';
  $blatted_boxes = array();
  $boxes = proon_mailbox_list($p, $imapStr);
  $s = $p[PROON_P_SITE];
  for ($boxnum = 0; $boxnum < count($boxes); $boxnum++) {
    $rawline = $boxes[$boxnum]['raw'];
    // This regexp taken from imap_mailbox.php, check_is_noselect()
    // and fixed up to work on either LSUB or LIST lines.  I don't just
    // use check_is_noselect() because it wasn't right until SM 1.4.5.
    $nonselectable = preg_match("/^\* (LSUB|LIST) \([^\)]*\\\\Noselect[^\)]*\)/i", $rawline);
    if (! $nonselectable) {
      $boxname = $boxes[$boxnum]['unformatted-disp'];
      proon_folder_table_row($p, $boxname, $clr, $outcome, ($boxnum==0), true, true);
      $blatted_boxes[] = $boxname;
    }
  }

  // Now, iterate over the pruning settings and do a row for any unsubscribed folders
  $need_unsub_header = true;
  $whatsleft = array();
  $whatsleft = array_merge($whatsleft, $p[PROON_F_DATE_OR_SIZE_OR_COUNT]);
  $whatsleft = array_merge($whatsleft, $p[PROON_P_SITE][PROON_O_F_DATE_SPAN]);
  $whatsleft = array_merge($whatsleft, $p[PROON_P_SITE][PROON_O_F_SIZE_SPAN]);
  $whatsleft = array_merge($whatsleft, $p[PROON_P_SITE][PROON_O_F_COUNT_SPAN]);
  foreach ($whatsleft as $target => $junk) {
    if (! proon_is_subscribed($p, $target, $imapStr)  &&  proon_mailbox_exists($p, $target, $imapStr)) {
      if ($need_unsub_header) {
	echo '<tr><th colspan=99 align=center><font size="+1">' . PROON_UI_UFOLDER_TABLE . '</font></th></tr>';
	proon_folder_table_column_headers(false);
	$need_unsub_header = false;
      }
      $boxname = $target;
      proon_folder_table_row($p, $boxname, $clr, $outcome, false, false, true);
      //unset($whatsleft[$boxname]);
      $blatted_boxes[] = $boxname;
    }
  }
  
  // site pref files which don't exist
  $need_site_header = true;
  foreach ($whatsleft as $boxname => $junk) {
    if (! in_array($boxname, $blatted_boxes)) {
      if ($need_site_header) {
	echo '<tr><th colspan=99 align=center><font size="+1">' . PROON_UI_NFOLDER_TABLE . '</font></th></tr>';
	proon_folder_table_column_headers(false);
	$need_site_header = false;
      }
      proon_folder_table_row($p, $boxname, $clr, $outcome, false, false, false);
      $blatted_boxes[] = $boxname;
    }
  }

  proon_folder_table_column_headers(false);
  echo '</table>' . "\n";
}

/**
 * Factored out logic for rendering folder table section headers
 */
function proon_blat_folder_table_header_item($label, $do_anchor=true) {
  $anchor = base64_encode($label);
  echo '<th align="center">' . $label;
  if ($do_anchor) echo '&nbsp;<sup><a href="#help_' . $anchor . '" name="doit_' . $anchor . '">' . PROON_UI_GOTO_HELP . '</a></sup>';
  echo '</th>' . "\n";
}

/**
 * Factored out logic for rendering folder table column headers
 */
function proon_folder_table_column_headers($anchor) {
  echo '<tr><td>&nbsp;</td>';
  proon_blat_folder_table_header_item(PROON_UI_DATE_SPAN, $anchor);
  proon_blat_folder_table_header_item(PROON_UI_SIZE_SPAN, $anchor);
  proon_blat_folder_table_header_item(PROON_UI_COUNT_SPAN, $anchor);
  proon_blat_folder_table_header_item(PROON_UI_UNSEEN_TOO, $anchor);
  proon_blat_folder_table_header_item(PROON_UI_MANUAL_ONLY, $anchor);
  proon_blat_folder_table_header_item(PROON_UI_FOLDER, false);
  echo '<td>&nbsp;</td></tr>' . "\n";
}

/**
 * Annotations are the messages related to rows in the folder table.
 * They're either indications of what happened or messages about
 * problems (with settings for that folder).
 */
function proon_blat_annotation($a, $clr) {
  $l = isset($a[PROON_OUTCOME_EFFECT]) ? $a[PROON_OUTCOME_EFFECT] : null;
  $c = isset($a[PROON_OUTCOME_ERROR])  ? $a[PROON_OUTCOME_ERROR]  : null;
  $r = isset($a[PROON_OUTCOME_PRUNE])  ? $a[PROON_OUTCOME_PRUNE]  : null;
  $bgclr = " bgcolor=\"$clr\"";
  if ($clr == null) $bgclr = '';
  if ($l) {
    echo "<tr $bgclr>";
    echo '<td colspan=99 align="left"><i><small>' . $l . '</small></i></td>';
    echo '</tr>' . "\n";
  }
  if ($c) {
    echo "<tr $bgclr>";
    echo '<td colspan=99 align="center"><i><small>' . $c . '</small></i></td>';
    echo '</tr>' . "\n";
  }
  if ($r) {
    echo "<tr $bgclr>";
    echo '<td colspan=99 align="right"><i><small>' . $r . '</small></i></td>';
    echo '</tr>' . "\n";
  }
}
/**
 * Controller for the rendering of a single row in the folder table
 */
function proon_folder_table_row(&$p, $boxname, &$clr, &$outcome, $do_anchor, $subscribed, $buttons) {
  $boxtime = isset($p[PROON_O_F_DATE_SPAN][$boxname]) ? $p[PROON_O_F_DATE_SPAN][$boxname] : null;
  $boxsize = isset($p[PROON_O_F_SIZE_SPAN][$boxname]) ? $p[PROON_O_F_SIZE_SPAN][$boxname] : null;
  $boxcount = isset($p[PROON_O_F_COUNT_SPAN][$boxname]) ? $p[PROON_O_F_COUNT_SPAN][$boxname] : null;
  $toss_unseen = isset($p[PROON_O_F_TOSS_UNSEEN][$boxname]);
  $manual_only = isset($p[PROON_O_F_MANUAL_ONLY][$boxname]);
  $clr = (($clr == 0) ? 9 : 0);
  $useclr = $clr;
  $a = isset($outcome[$boxname]) ? $outcome[$boxname] : null;
  if ($a) {
    if      (isset($a[PROON_OUTCOME_ERROR]))  $useclr = 2;
    else if (isset($a[PROON_OUTCOME_PRUNE]))  $useclr = 3;
    else if (isset($a[PROON_OUTCOME_EFFECT])) $useclr = 5;
  }
  global $color;
  $specific_color = $color[$useclr];
  if (proon_effective_option($p, PROON_O_U_DIS_COLOR)) $specific_color = null;
  proon_blat_ftrow($boxname, $boxtime, $boxsize, $boxcount, $toss_unseen, $manual_only, $p[PROON_P_SITE], $specific_color, $p[PROON_P_OPTIONS], $do_anchor, $subscribed, $buttons);
  if ($a) {
    proon_blat_annotation($a, $specific_color);
  }
}

/**
 * Factored out logic answering the question of whether there
 * is any site preference for a given folder.
 */
function proon_has_site_stuff(&$proonsite, $boxname) {
  return 
    isset($proonsite[PROON_O_F_DATE_SPAN][$boxname]) ||
    isset($proonsite[PROON_O_F_SIZE_SPAN][$boxname]) ||
    isset($proonsite[PROON_O_F_COUNT_SPAN][$boxname]) ||
    isset($proonsite[PROON_O_F_TOSS_UNSEEN][$boxname]) ||
    isset($proonsite[PROON_O_F_MANUAL_ONLY][$boxname]);
}

/**
 * Renders and invidual row in the folder table, including any site preferences
 */
function proon_blat_ftrow($folder, $datespan, $sizespan, $countspan, $toss_unseen, $manual_only, &$proonsite, $clr, &$proonopts, $do_anchor, $subscribed, $buttons) {
  $sytish = proon_has_site_stuff($proonsite, $folder);


  $fid = proon_encode_foldername($folder);
  $date_disabled = isset($proonopts[PROON_O_U_DIS_DATE]) ? $proonopts[PROON_O_U_DIS_DATE] : false;
  $ddis = ($date_disabled=='true' ? 'readonly' : '');
  $size_disabled = isset($proonopts[PROON_O_U_DIS_SIZE]) ? $proonopts[PROON_O_U_DIS_SIZE] : false;
  $sdis = ($size_disabled=='true' ? 'readonly' : '');
  $count_disabled = isset($proonopts[PROON_O_U_DIS_COUNT]) ? $proonopts[PROON_O_U_DIS_COUNT] : false;
  $cdis = ($count_disabled=='true' ? 'readonly' : '');
  $bgclr = " bgcolor=\"$clr\"";
  if ($clr == null) $bgclr = '';

  echo "<tr $bgclr>";
  $syt = "<tr $bgclr>";

  $s = $sytish ? PROON_UI_SITE_PREF : null;
  proon_blat_effect_button($fid, $do_anchor, $buttons, $s);

  $s = null;
  if ($sytish) {
    $sytv = isset($proonsite[PROON_O_F_DATE_SPAN][$folder]);
    if ($sytv) $sytv = $proonsite[PROON_O_F_DATE_SPAN][$folder]; else $sytv = '&nbsp;';
    $s = '<br>' . SYTa . $sytv . SYTz;
  }
  echo '<td align="center">' . 
    '<input type="text" align="right" name="' .PROON_PRE_DATE.$fid. "\" $ddis value=\"$datespan\" size=\"7\" />";
  echo $s;
  echo '</td>' . "\n";

  $s = null;
  if ($sytish) {
    $sytv = isset($proonsite[PROON_O_F_SIZE_SPAN][$folder]);
    if ($sytv) $sytv = $proonsite[PROON_O_F_SIZE_SPAN][$folder]; else $sytv = '&nbsp;';
    $s = '<br>' . SYTa . $sytv . SYTz;
  }
  echo '<td align="center">';
  echo 
    '<input type="text" align="right" name="' .PROON_PRE_SIZE.$fid. "\" $sdis value=\"$sizespan\" size=\"7\" />";
  echo $s;
  echo '</td>' . "\n";

  $s = null;
  if ($sytish) {
    $sytv = isset($proonsite[PROON_O_F_COUNT_SPAN][$folder]);
    if ($sytv) $sytv = $proonsite[PROON_O_F_COUNT_SPAN][$folder]; else $sytv = '&nbsp;';
    $s = '<br>' . SYTa . $sytv . SYTz;
  }
  echo '<td align="center">'; 
  echo 
    '<input type="text" align="right" name="' .PROON_PRE_COUNT.$fid. "\" $cdis value=\"$countspan\" size=\"7\" />";
  echo $s;
  echo '</td>' . "\n"; 

  $s = null;
  if ($sytish) {
    $sytv = isset($proonsite[PROON_O_F_TOSS_UNSEEN][$folder]);
    if ($sytv) $sytv = SYTCH; else $sytv = '&nbsp;';
    $s = '<br>' . SYTa . $sytv . SYTz;
  }
  $ch = ($toss_unseen ? ' checked ' : '');
  echo '<td align="center"><input type="checkbox" name="' .PROON_PRE_UNSEEN.$fid. "\" value=\"toss\" $ch/>$s</td>\n";

  $s = null;
  if ($sytish) {
    $sytv = isset($proonsite[PROON_O_F_MANUAL_ONLY][$folder]);
    if ($sytv) $sytv = SYTCH; else $sytv = '&nbsp;';
    $s = '<br>' . SYTa . $sytv . SYTz;
  }
  $ch = ($manual_only ? ' checked ' : '');
  echo '<td align="center"><input type="checkbox" name="' .PROON_PRE_MANUAL.$fid. "\" value=\"click\" $ch/>$s</td>\n";

  $faceword = $subscribed ? 'strong' : 'em';
  $folderI18N = proon_folder_blat_name($folder);
  $s = $sytish ? '<br>&nbsp;' : null;
  echo '<td align="left"><'.$faceword.'>' . htmlspecialchars($folderI18N) . '</'.$faceword.">$s</td>\n";

  $s = $sytish ? PROON_UI_SITE_PREF : null;
  proon_blat_now_button($fid, $do_anchor, $buttons, $s);
  echo '</tr>' . "\n";
}

/**
 * There is a set of navigational links/anchors which repeats in a page.  This
 * function renders them with the "current" location not make a hotlink.
 */
function proon_blat_anchor($except) {
  $alist = array(PROON_ANCHOR_OPTS => PROON_UI_USER_PREFS,
		 PROON_ANCHOR_FOLDERS => PROON_UI_FOLDER_TABLE,
		 PROON_ANCHOR_HELP => PROON_UI_HELP_ALL,
		 PROON_ANCHOR_BOTTOM => PROON_UI_BOP);
  echo "\n".'<a name="'.$except.'"/>'."\n";
  echo "\n".'<center><table cellpadding=6 border=0><tr>';
  foreach ($alist as $anchor => $text) {
    echo '<td>';
    if ($anchor == $except) {
      echo $text;
    }
    else {
      echo '<a href="#'.$anchor.'">'.$text.'</a>';
    }
    echo '</td>';
  }
  echo '</tr></table></center>'."\n";
}

?>
