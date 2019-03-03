<?php

// As you might guess, things in this file are 
// hook functions and related things.  We try to be
// sleek and fast where appropriate.

include_once(SM_PATH . 'plugins/proon/utils.php');

/**
 * This function considers both a sitewide minimum interval
 * between checks and a personal interval.  Both tests
 * must pass or the hook is not active.  Some of the coding
 * in here is a bit, uh, squirrelly because this function
 * is meant to run as fast as possible.
 */
function proon_is_hook_active($what) {
  global $data_dir, $username;
  $last_prune = null;
  if (sqsession_is_registered(PROON_LAST_PRUNE)) {
    sqGetGlobalVar(PROON_LAST_PRUNE, $last_prune, SQ_SESSION);
  }
  // If $last_prune is null, it's just after logon, so the
  // hook is only active if it's a multiple of the user-selected
  // "every N logins"
  if ($last_prune == null) {
    $lc = getPref($data_dir, $username, PROON_O_U_LOGIN_COUNT);
    setPref($data_dir, $username, PROON_O_U_LOGIN_COUNT, $lc + 1);
    $site_freq = false;
    global $proon_user_login_prune_frequency;
    if (isset($proon_user_login_prune_frequency)) {
      $site_freq = proon_count_in_each($proon_user_login_prune_frequency);
      if ($site_freq <= 0) $site_freq = false;
    }
    $user_freq = getPref($data_dir, $username, PROON_O_U_LOGIN_N);
    if (!$user_freq  ||  $user_freq == PROON_PREF_BLANK) {
      $user_freq = 1;
    }
    else {
      $user_freq = proon_count_in_each($user_freq);
      if ($user_freq <= 0) $user_freq = false;
    }
    // 4 possibilities....
    if      ($site_freq  &&  ! $user_freq) $freq = $site_freq;
    else if ($user_freq  &&  ! $site_freq) $freq = $user_freq;
    else if ($user_freq  &&    $site_freq) $freq = min($site_freq, $user_freq);
    else                                   $freq = 1;
    if (($lc % $freq) == 0) return true;
    // If we skipped pruning for the "every N" thing, then
    // set this variable just as if we did a pruning.
    sqsession_register(time(), PROON_LAST_PRUNE);
    return false;
  }

  $time_diff = time() - $last_prune;

  // We skip the proon-ish general mechanism for getting preferences
  // for the sake of speed.
  global $proon_user_prune_interval;
  if (isset($proon_user_prune_interval)) {
    $site_interval = proon_date_in_seconds($proon_user_prune_interval);
  }
  if ($site_interval > $time_diff) return false;

  // We skip the proon-ish general mechanism for getting preferences
  // for the sake of speed.
  $interval = getPref($data_dir, $username, PROON_O_U_PRUNE_INTERVAL);
  if ($interval == PROON_PREF_BLANK) {
    // no interval, so not active
    return false;
  }

  $interval = proon_date_in_seconds($interval);
  if ($interval > $time_diff) return false;

  return true;
}

/**
 * This function is called both at webmail_top (to catch the login
 * case) and at right_main_bottom to catch recurring prune intervals.
 * Both result, when active, in a "prune all" activity.
 */
function proon_prune_hook_function($h) {
  if (is_array($h)) $h = $h[0];
  if (!proon_is_hook_active($h)) {
    return;
  }

  include_once(SM_PATH . 'plugins/proon/utils.php');

  $p = proon_get_prefs();
  $outcome = array();
  $notice = null;
  $imapStr = null;
  include_once(SM_PATH . 'plugins/proon/action.php');
  proon_action_pruneall($p, $outcome, $notice, $imapStr, false);
  sqsession_register(time(), PROON_LAST_PRUNE);

  if (proon_effective_option($p, PROON_O_U_MESSAGE_AFTER)) {
    $report_slim = proon_format_outcome($p, $outcome, false);
    if ($report_slim !== null) {
      include_once(SM_PATH . 'class/mime/Message.class.php');
      $msg = new Message();
      $msg->rfc822_header = proon_format_headers($p, $outcome, false);
      $msg->setBody($report_slim);
      include_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
      $deliver = new Deliver_IMAP();
      $length = $deliver->mail($msg);
      proon_login($imapStr);
      sqimap_append($imapStr, 'INBOX', $length);
      $deliver->mail($msg, $imapStr);
      sqimap_append_done($imapStr, 'INBOX');
    }
  }
  if (proon_effective_option($p, PROON_O_U_SCREEN_AFTER)) {
    $report_verbose = proon_format_outcome($p, $outcome, true);
    
    echo '<pre>';
    proon_locale_on();
    echo _("proon autopruning report") . "\n\n";
    proon_locale_off();
    echo $report_verbose;
    echo '</pre>';
  }
  proon_logout($imapStr);

}

/*
 * The stand-alone proon options page setup.
 */
function proon_optpage($h) {
  global $optpage_blocks;
  proon_locale_on();
  $optpage_blocks[] = array(
			    'name' => PROON_PRUNING_DOTDOTDOT,
			    'url' => SM_PATH . 'plugins/proon/form.php',
			    'desc' => _("Options for Pruning Folders"),
			    'js' => FALSE
			    );
  proon_locale_off();
}

/*
 * The stand-alone proon options page redirect.
 */
function proon_optpage_loadinfo($h) {
  global $optpage_name, $optpage_file;
  $optpage_name = PROON_PRUNING_DOTDOTDOT;
  $optpage_file = SM_PATH . 'plugins/proon/form.php';
}

/**
 * This is called for both options_folder_inside and left_main_after
 * to put in links to the proon form page.
 */
function proon_link($h) {
  if (is_array($h)) $h = $h[0];
  
  $options_page = true;
  if ($h == 'left_main_after') {
    $options_page = false;
    global $proon_user_prune_link;
    $disable_left_link = $proon_user_prune_link;
    if ($disable_left_link  &&  (string)$disable_left_link != PROON_PREF_BLANK) {
      // site doesn't want it on the left pane
      return false;
    }
    global $data_dir, $username;
    $disable_left_link = getPref($data_dir, $username, PROON_O_U_PRUNE_LINK);
    if ($disable_left_link  &&  $disable_left_link != PROON_PREF_BLANK) {
      // user doesn't want it on the left pane
      return false;
    }
  }

  include_once(SM_PATH . 'plugins/proon/utils.php');
  proon_locale_on();

  if ($options_page) {
    echo '<tr><th colspan=99 align=center>' . _("Options for Pruning Folders") . '</th>';
    echo '<tr><td colspan=99 align=center>';
    echo '<a href="../plugins/proon/form.php">' . PROON_PRUNING_DOTDOTDOT . '</a></th>';
  }
  else {
    echo '<p><center>';
    echo '<form target="right" action="../plugins/proon/form.php" method="get">';
    echo '<input class="small" type="submit" value="' . PROON_PRUNING_DOTDOTDOT . '"></form>';
    echo '</center>';
  }
    
  proon_locale_off();
}

/**
 * Handles 'one time' stuff, which includes sending new users to the
 * proon form and optionally converting auto_prune_sent settings to 
 * equivalent proon settings.  These are controlled by site configs.
 */
function proon_onetime($h) {
  if (is_array($h)) $h = $h[0];
  global $data_dir, $username;
  global $proon_log_verbose;
  $proon_log_verbose && proon_log("othk u=$username d=$data_dir s=" . SQ_SESSION . " sp=" . SM_PATH);
  if (! $data_dir  ||  ! $username) return;

  global $right_frame;

  // caution: this hook can be called twice if a site uses a
  // redirecting login plugin.
  // We only want to do stuff the first time through.
  $proon_onetime = false;
  if (sqsession_is_registered('proon_onetime')) {
    sqGetGlobalVar('proon_onetime', $proon_onetime, SQ_SESSION);
  }
  if ($proon_onetime) {
    if ($proon_onetime !== true) {
      $proon_log_verbose && proon_log("proon_onetime=" . $proon_onetime);
      $right_frame = $proon_onetime;
    }
    return; // already been here
  }
  
  sqsession_register(true, 'proon_onetime');

  $p = proon_get_prefs();
  if ($p[PROON_P_PREFS_SEEN] > 0) return;  // not our first time
  
  global $proon_onetime_everybody;
  global $proon_convert_apst;
  if ($proon_convert_apst) {
    global $data_dir, $username;
    $apst = getPref($data_dir, $username, 'auto_prune_sent_threshold');
    if (is_numeric($apst)  &&  $apst > 0) {
      global $proon_redirect_apst;
      if ($proon_redirect_apst  ||  $proon_onetime_everybody) {
	//$right_frame = '../plugins/proon/form.php';
	$right_frame = 'options.php';
	sqsession_register('proon', 'optpage');
	sqsession_register($right_frame, 'proon_onetime');
      }
      else {
	proon_actually_convert_apst($apst);
      }
    }
  }
  if ($proon_onetime_everybody) {
    //$right_frame = '../plugins/proon/form.php';
    $right_frame = 'options.php';
    sqsession_register('proon', 'optpage');
    sqsession_register($right_frame, 'proon_onetime');
  }
}

/**
 * The guts of auto_prune_sent -> proon preference
 * conversion.
 */
function proon_actually_convert_apst($apst) {
  global $sent_folder, $folder_prefix;
  global $username, $data_dir;
  $sf = $sent_folder;
  if ($sf != ''  &&  isset($folder_prefix)  &&  strlen($folder_prefix) > 0) {
    $sf = substr($sf, strlen($folder_prefix));
  }
  $s[$sf] = $apst;
  setPref($data_dir, $username, PROON_O_F_DATE_SPAN, proon_to_pref($s));
  $s[$sf] = 'toss';
  setPref($data_dir, $username, PROON_O_F_TOSS_UNSEEN, proon_to_pref($s));
  proon_log('converted auto_prune_sent_threshold=' . $apst . " for folder $sf");

  global $proon_delete_apst;
  if ($proon_delete_apst) {
    setPref($data_dir, $username, 'auto_prune_sent_threshold', null);
  }
}

?>
