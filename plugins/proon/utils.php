<?php

include_once(SM_PATH . 'plugins/proon/site-config.php.sample');
if (file_exists(SM_PATH . 'plugins/proon/site-config.php')) {
  include(SM_PATH . 'plugins/proon/site-config.php');
}

/**
 * Utility for switching to the proon text domains.  It 
 * has a limited push/pop-like capability.
 */
function proon_locale_on($s = false) {
  global $proon_log_verbose;
  $s && $proon_log_verbose && proon_log('L ON  ' . $s);
  $old = textdomain(null);
  if ($old == 'proon') {
    return;
  }
  global $proon_old_textdomain;
  $proon_old_textdomain = $old;
  bindtextdomain('proon', SM_PATH . 'locale');
  textdomain('proon');
}

/**
 * Switches out of the proon text domain to whatever it was
 * before that.
 */
function proon_locale_off($s = false) {
  global $proon_old_textdomain, $proon_log_verbose;
  $s && $proon_log_verbose && proon_log('L OFF ' . $s);
  $new = textdomain(null);
  if ($new != 'proon') {
    return;
  }
  bindtextdomain('squirrelmail', SM_PATH . 'locale');
  if (! $proon_old_textdomain) {
    $proon_old_textdomain = 'squirrelmail';
  }
  textdomain($proon_old_textdomain);
}

include_once(SM_PATH . 'plugins/proon/define.php');
include_once(SM_PATH . 'plugins/proon/defineui.php');

/**
 * Look for a suitable delimiter character.  For each
 * candidate delimiter character in $trythese, see if it
 * happens to appear in any of the keys or values in 
 * $some_array.
 */
function proon_pick_delimiter($some_array, $trythese) {
  if ($some_array) {
    $line = '';
    foreach ($some_array as $key => $val) {
      $line .= "$key$val";
    }
    $len = strlen($trythese);
    for ($ii=0; $ii<$len; ++$ii) {
      $maybe = $trythese{$ii};
      if (!strstr($line, $maybe)) {
	return $maybe;
      }
    }
  }
  return null;
}

/**
 * Encode an associative array of key/value pairs into a delimited
 * string of the form "=k1=v1=k2=v2".  The string starts with the
 * delimiter character, which is selected so as to not conflict with
 * any of the keys or values.  There is no delimiter at the end of
 * the returned string.
 */
function proon_to_pref(&$some_array) {
  if (!isset($some_array)  ||  count($some_array) == 0) return null;
  $d1 = '~!@#$%^\\&*()_+=-`\'[]{};:"/?.>,<|';
  $d2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $d3 = 'abcdefghijklmnopqrstuvwxyz';
  $d4 = '0123456789';
  $delim = proon_pick_delimiter($some_array, $d1 . $d2 . $d3 . $d4);
  if ($delim == null) return false;  // this is very bad :-(
  $pref = '';
  if ($some_array) foreach($some_array as $key=>$value) {
    $pref .= "$delim$key$delim$value";
  }
  return $pref;
}

/**
 * The logical reverse of the proon_to_pref function.  It unpacks a
 * delimited string and returns an associative array of the key/value
 * pairs found.  It's very tolerant, so you're likely to get some
 * kind of result even if the input is grossly malformed.
 */
function proon_from_pref($pref) {
  $delim = $pref{0};
  $pref  = substr($pref, 1);
  $pref  = explode($delim, $pref);
  $len = count($pref);
  for ($ii=0; $ii<$len; ) {
    $k = $pref[$ii];
    ++$ii;
    $v = $pref[$ii];
    ++$ii;
    $kv[$k] = $v;
  }
  return $kv;
}

/**
 * Factor out login logic to avoid multiple connects when just one
 * will do.  In fact, if the SM connection is already hanging around
 * we'll use that one.
 */
function proon_login(& $imapStr) {
  if ($imapStr == null) {
    global $imapConnection;
    if (isset($imapConnection)  &&  $imapConnection !== null) {
      global $proon_log_verbose;
      $proon_log_verbose && proon_log("logon  imapConnection $imapConnection");
      $imapStr = $imapConnection;
    }
    else {
      include_once(SM_PATH . 'functions/imap.php');
      global $imapServerAddress, $imapPort;
      $imapStr = sqimap_login($_SESSION['username'], $_COOKIE['key'], $imapServerAddress, $imapPort, 0);
    }
  }
}

/**
 * Only actually do an IMAP logout if we did the login.  Otherwise,
 * it's somebody else's job.
 */
function proon_logout(& $imapStr) {
  if (!isset($imapStr)  ||  $imapStr == null) return;

  global $imapConnection;
  if (isset($imapConnection)  &&  $imapConnection == $imapStr) {
    global $proon_log_verbose;
    $proon_log_verbose && proon_log("logoff imapConnection $imapConnection");
  }
  else {
    sqimap_logout($imapStr);
  }
  $imapStr = null;
}

/**
 * Factor out some tedious logic for gathering the various per-folder user
 * and site values for pruning spans.
 */
function proon_get_spans(&$p, &$target) {
  $rawdate = (isset($p[PROON_O_F_DATE_SPAN][$target]) ? $p[PROON_O_F_DATE_SPAN][$target] : '');
  $rawsize = (isset($p[PROON_O_F_SIZE_SPAN][$target]) ? $p[PROON_O_F_SIZE_SPAN][$target] : '');
  $rawcount = (isset($p[PROON_O_F_COUNT_SPAN][$target]) ? $p[PROON_O_F_COUNT_SPAN][$target] : '');
  $m = proon_check_spans($rawdate, $rawsize, $rawcount);
  // We used to complain about lack of any user-defined spans, but site preferences
  // can override anyhow.
  //if ($m !== true) return $m;
  $spans[PROON_SPAN_DATE] = proon_minimum_folder_option($p, PROON_O_F_DATE_SPAN, $target, 'd');
  $spans[PROON_SPAN_SIZE] = proon_minimum_folder_option($p, PROON_O_F_SIZE_SPAN, $target, 's');
  $spans[PROON_SPAN_COUNT] = proon_minimum_folder_option($p, PROON_O_F_COUNT_SPAN, $target, 'c');;
  return $spans;
}

/**
 * Checks the span values for syntactic correctness.
 */
function proon_check_spans($rawdate, $rawsize, $rawcount) {
  if (!$rawdate  &&  !$rawsize  &&  !$rawcount) {
    proon_locale_on();
    $m = _("None of the span values has been set for this folder.");
    proon_locale_off();
    return $m;
  }
  $datespan = proon_date_in_seconds($rawdate);
  $sizespan = proon_size_in_bytes($rawsize);
  $countspan = proon_count_in_each($rawcount);

  if ($rawdate != ''  &&  $datespan === false) {
    proon_locale_on();
    $m = _("The date span is malformed.");
    proon_locale_off();
    return $m;
  }
  if ($rawsize != ''  &&  $sizespan === false) {
    proon_locale_on();
    $m = _("The size span is malformed.");
    proon_locale_off();
    return $m;
  }
  if ($rawcount != ''  &&  $countspan === false) {
    proon_locale_on();
    $m = _("The count span is malformed.");
    proon_locale_off();
    return $m;
  }
  return true;
}
/**
 * Converts a string date span to a number of seconds.
 * Returns false if it doesn't like something.
 */
function proon_date_in_seconds($rawspan) {
  $rawspan = trim($rawspan);
  if (!isset($rawspan)  ||  $rawspan == ''  ||  $rawspan == '/') return false;
  $first_slash = strpos($rawspan, '/');
  $last_slash = strrpos($rawspan, '/');
  if ($first_slash != $last_slash) return false;

  if (false !== $first_slash) {
    list($days, $hours) = explode('/', $rawspan);
    $days = trim($days);
    $hours = trim($hours);
    if (! $days) $days = 0;
    if (! $hours) $hours = 0;
  }
  else {
    $days = trim($rawspan);
    $hours = 0;
  }
  if (!is_numeric($days)  ||  !is_numeric($hours)) return false;

  $spansecs = 3600 * ((24 * $days) + $hours);
  // not so reliable around or before ye olde epoch date
  if ((time() - $spansecs) < 100000) return false;
  if ($spansecs < 0) return false;
  return $spansecs;
}

/**
 * Converts a string size span to a number of bytes.
 * Returns false if it doesn't like something.
 */
function proon_size_in_bytes($rawsize) {
  return proon_scaled_singles($rawsize, 'm');
}

/**
 * Converts a string count span to a number of messages.
 * Returns false if it doesn't like something.
 */
function proon_count_in_each($rawsize) {
  return proon_scaled_singles($rawsize, 'b');
}

/**
 * Factored out common logic for conversion of count
 * and size spans.  The scale suffixes are the same, but
 * the default suffix is different.
 * Returns false if it doesn't like something.
 */
function proon_scaled_singles($rawvalue, $default_suffix) {
  if (!isset($rawvalue)) return false;
  $rawvalue = trim($rawvalue);
  if ($rawvalue == '') return false;
  $suffix = substr($rawvalue, strlen($rawvalue) - 1);
  if (is_numeric($suffix)) {
    $number = $rawvalue;
    $suffix = $default_suffix;
  }
  else {
    $number = substr($rawvalue, 0, strlen($rawvalue) - 1);
  }
  if (!is_numeric($number)) return false;
  switch ($suffix) {
  case 'm': $multiplier = 1000 * 1000; break;
  case 'M': $multiplier = 1024 * 1024; break;
  case 'k': $multiplier = 1000; break;
  case 'K': $multiplier = 1024; break;
  case 'b': $multiplier = 1; break;
  case 'B': $multiplier = 1; break;
  default:
    return false;
  }
  $tally = ($number * $multiplier);
  if ($tally <= 0) return false;
  return $tally;
}

/**
 * Makes a foldername safe for use inside HTML
 */
function proon_encode_foldername($folder) {
  return base64_encode($folder);
}

/**
 * Reverse of proon_encode_foldername
 */
function proon_pull_foldername($key) {
  list($word, $foldername) = explode('_', $key);
  return base64_decode($foldername);
}

/**
 * A utility for comparing two arrays to see if they differ
 * in a way that proon cares about.
 */
function proon_arrays_differ(&$a, &$b) {
  if ($a === $b) return false;
  if (is_array($a)  &&  !is_array($b)) return true;
  if (is_array($b)  &&  !is_array($a)) return true;
  // we'd use array_diff_assoc here, but it only comes in at PHP 4.3
  // array_diff isn't good enough because we care about the keys
  foreach ($a as $k => $v) {
    if (!isset($b[$k])  ||  $b[$k] !== $v) return true;
  }
  foreach ($b as $k => $v) {
    if (!isset($a[$k])  ||  $a[$k] !== $v) return true;
  }
  return false;
}

/**
 * Write all proon-related preference items to the user's store.
 */
function proon_set_prefs(&$p) {
  global $data_dir, $username;

  $pref = proon_to_pref($p[PROON_O_F_DATE_SPAN]);
  if ($pref == '') $pref = PROON_PREF_BLANK;
  setPref($data_dir, $username, PROON_O_F_DATE_SPAN, $pref);

  $pref = proon_to_pref($p[PROON_O_F_SIZE_SPAN]);
  if ($pref == '') $pref = PROON_PREF_BLANK;
  setPref($data_dir, $username, PROON_O_F_SIZE_SPAN, $pref);

  $pref = proon_to_pref($p[PROON_O_F_COUNT_SPAN]);
  if ($pref == '') $pref = PROON_PREF_BLANK;
  setPref($data_dir, $username, PROON_O_F_COUNT_SPAN, $pref);

  $pref = proon_to_pref($p[PROON_O_F_TOSS_UNSEEN]);
  if ($pref == '') $pref = PROON_PREF_BLANK;
  setPref($data_dir, $username, PROON_O_F_TOSS_UNSEEN, $pref);

  $pref = proon_to_pref($p[PROON_O_F_MANUAL_ONLY]);
  if ($pref == '') $pref = PROON_PREF_BLANK;
  setPref($data_dir, $username, PROON_O_F_MANUAL_ONLY, $pref);

  $proonopts = $p[PROON_P_OPTIONS];

  $opt = PROON_O_U_LOGIN_N;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_PRUNE_INTERVAL;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_VIA_TRASH;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_TRASH_TIME;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_UNSUBSCRIBED;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_DIS_DATE;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_DIS_SIZE;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_DIS_COUNT;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_SC_ORDER;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_MESSAGE_AFTER;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_SCREEN_AFTER;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_DIS_COLOR;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

  $opt = PROON_O_U_PRUNE_LINK;
  $pref = (isset($proonopts[$opt])) ? $proonopts[$opt] : PROON_PREF_BLANK;
  setPref($data_dir, $username, $opt, $pref);

}

/**
 * Factored out common logic for fetching a single user preference item.
 * Includes getting the equivalent (same name) site preference item.
 */
function proon_get_single_pref($opt, &$saw_pref_count, &$proonopts, &$proonsite) {
  global $data_dir, $username;
  $val = getPref($data_dir, $username, $opt);
  if ($val) ++$saw_pref_count;
  if ($val  &&  (string)$val != PROON_PREF_BLANK) $proonopts[$opt] = $val;
  global $$opt;
  $val = $$opt;
  if ($val  &&  (string)$val != PROON_PREF_BLANK) $proonsite[$opt] = $val;
}

/**
 * Factored out common logic for fetching a per-folder user preference item.
 * Includes getting the equivalent (same name) site preference item.
 */
function proon_get_list_pref($opt, &$saw_pref_count, &$p, &$proonsite) {
  global $data_dir, $username;
  $blob = getPref($data_dir, $username, $opt);
  if ($blob) ++$saw_pref_count;
  $p[$opt] = ($blob  &&  (string)$blob != PROON_PREF_BLANK) ? proon_from_pref($blob) : array();
  global $$opt;
  $blob = $$opt;
  $proonsite[$opt] = ($blob  &&  (String)$blob != PROON_PREF_BLANK) ? proon_from_pref($blob) : array();
}

/**
 * Fetch all proon-related items from the user's preferences store.
 */
function proon_get_prefs() {
  global $data_dir, $username;
  $saw_pref_count = 0; // tells if at least one pref was read
  $proonopts = array();
  $proonsite = array();

  proon_get_list_pref(PROON_O_F_DATE_SPAN, $saw_pref_count, $p, $proonsite);
  proon_get_list_pref(PROON_O_F_SIZE_SPAN, $saw_pref_count, $p, $proonsite);
  proon_get_list_pref(PROON_O_F_COUNT_SPAN, $saw_pref_count, $p, $proonsite);
  proon_get_list_pref(PROON_O_F_TOSS_UNSEEN, $saw_pref_count, $p, $proonsite);
  proon_get_list_pref(PROON_O_F_MANUAL_ONLY, $saw_pref_count, $p, $proonsite);

  $dateorsizeorcountlist = array();
  foreach ($p[PROON_O_F_DATE_SPAN] as $folder => $value) {
    $dateorsizeorcountlist[$folder] = $value;
  }
  foreach ($p[PROON_O_F_SIZE_SPAN] as $folder => $value) {
    $dateorsizeorcountlist[$folder] = $value;
  }
  foreach ($p[PROON_O_F_COUNT_SPAN] as $folder => $value) {
    $dateorsizeorcountlist[$folder] = $value;
  }
  ksort($dateorsizeorcountlist);  // cosmetic
  $p[PROON_F_DATE_OR_SIZE_OR_COUNT] = $dateorsizeorcountlist;
  
  proon_get_single_pref(PROON_O_U_LOGIN_N, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_PRUNE_INTERVAL, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_VIA_TRASH, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_TRASH_TIME, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_UNSUBSCRIBED, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_DIS_DATE, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_DIS_SIZE, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_DIS_COUNT, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_SC_ORDER, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_MESSAGE_AFTER, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_SCREEN_AFTER, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_DIS_COLOR, $saw_pref_count, $proonopts, $proonsite);
  proon_get_single_pref(PROON_O_U_PRUNE_LINK, $saw_pref_count, $proonopts, $proonsite);

  $p[PROON_P_OPTIONS]  = $proonopts;
  $p[PROON_P_SITE]  = $proonsite;
  $p[PROON_P_PREFS_SEEN] = $saw_pref_count;
  
  return $p;
}


/**
 * Return the effective value of some named option.  If
 * there is a site pref, it rulez.  Else, return the user
 * pref value.
 */
function proon_effective_option(&$p, $opt) {
  $site_pref = (isset($p[PROON_P_SITE][$opt])  &&  $p[PROON_P_SITE][$opt]) ? ($p[PROON_P_SITE][$opt]) : null;
  if ($site_pref) return $site_pref;
  $user_pref = (isset($p[PROON_P_OPTIONS][$opt])  &&  $p[PROON_P_OPTIONS][$opt]) ? ($p[PROON_P_OPTIONS][$opt]) : null;
  return $user_pref;
}

/**
 * Return the effective value of some named per-folder option.  If
 * there is a site pref, it rulez.  Else, return the user
 * pref value.
 */
function proon_effective_folder_option(&$p, $opt, $target) {
  $site_pref = (isset($p[PROON_P_SITE][$opt][$target])  &&  $p[PROON_P_SITE][$opt][$target]) ? ($p[PROON_P_SITE][$opt][$target]) : null;
  if ($site_pref) return $site_pref;
  $user_pref = (isset($p[$opt][$target])  &&  $p[$opt][$target]) ? ($p[$opt][$target]) : null;
  return $user_pref;
}

/**
 * Return the effective value of some named per-folder option.  It's the
 * minimum of the site value and the user value (though null/zero/false
 * for either of those is ignored).
 */
function proon_minimum_folder_option(&$p, $opt, $target, $type) {
  $site_pref = (isset($p[PROON_P_SITE][$opt][$target])  &&  $p[PROON_P_SITE][$opt][$target]) ? ($p[PROON_P_SITE][$opt][$target]) : null;
  $user_pref = (isset($p[$opt][$target])  &&  $p[$opt][$target]) ? ($p[$opt][$target]) : null;
  if ($type == 'd') {
    $site_pref = proon_date_in_seconds($site_pref);
    $user_pref = proon_date_in_seconds($user_pref);
  }
  else if ($type == 's') {
    $site_pref = proon_size_in_bytes($site_pref);
    $user_pref = proon_size_in_bytes($user_pref);
  }
  else if ($type == 'c') {
    $site_pref = proon_count_in_each($site_pref);
    $user_pref = proon_count_in_each($user_pref);
  }
  if      (! $site_pref) return $user_pref;
  else if (! $user_pref) return $site_pref;
  if ($site_pref < $user_pref) return $site_pref;
  return $user_pref;
}

/**
 * Read preference values from storage and compare to copies
 * received from form submission.  If any don't exactly match,
 * even if logically equivalent, the memory copy is considered
 * dirty.
 *
 * @return boolean dirty flag
 */
function proon_is_dirty(&$p) {
  $pref_p = proon_get_prefs();
  if (proon_arrays_differ($p[PROON_O_F_DATE_SPAN], $pref_p[PROON_O_F_DATE_SPAN])) return true;
  if (proon_arrays_differ($p[PROON_O_F_SIZE_SPAN], $pref_p[PROON_O_F_SIZE_SPAN])) return true;
  if (proon_arrays_differ($p[PROON_O_F_COUNT_SPAN], $pref_p[PROON_O_F_COUNT_SPAN])) return true;
  if (proon_arrays_differ($p[PROON_O_F_TOSS_UNSEEN], $pref_p[PROON_O_F_TOSS_UNSEEN])) return true;
  if (proon_arrays_differ($p[PROON_O_F_MANUAL_ONLY], $pref_p[PROON_O_F_MANUAL_ONLY])) return true;
  return proon_arrays_differ($p[PROON_P_OPTIONS], $pref_p[PROON_P_OPTIONS]);
}

/**
 * A little utility for calculating some widths to make reports
 * (after pruning) pretty instead of ragged.
 */
function proon_calc_report_widths(&$p, &$outcome, $verbose) {
  $w = array();
  $w['duelabel'] = max(strlen(PROON_UI_DATE_SPAN), 
		       strlen(PROON_UI_SIZE_SPAN), 
		       strlen(PROON_UI_COUNT_SPAN),
		       strlen(PROON_UI_UNSEEN_TOO));
  $w['fname'] = strlen(PROON_UI_FOLDER);
  $w['spanval'] = 0;
  $w['total'] = 0;
  proon_locale_on();
  $yess = _("yes");
  proon_locale_off();
  foreach ($outcome as $f => $an) {
    $w['fname'] = max($w['fname'], strlen(proon_folder_blat_name($f)));
    $d = isset($p[PROON_O_F_DATE_SPAN][$f]) ? $p[PROON_O_F_DATE_SPAN][$f] : '';
    $s = isset($p[PROON_O_F_SIZE_SPAN][$f]) ? $p[PROON_O_F_SIZE_SPAN][$f] : '';
    $c = isset($p[PROON_O_F_COUNT_SPAN][$f]) ? $p[PROON_O_F_COUNT_SPAN][$f] : '';
    $u = isset($p[PROON_O_F_TOSS_UNSEEN][$f]) ? $yess : '';
    $w['spanval'] = max($w['spanval'], strlen($d), strlen($s), strlen($c), strlen($u));
    $dp = $an[PROON_OUTCOME_DUE_TO_DATE];
    $sp = $an[PROON_OUTCOME_DUE_TO_SIZE];
    $cp = $an[PROON_OUTCOME_DUE_TO_COUNT];
    $tot_pruned = $dp + $sp + $cp;
    $w['total'] += $tot_pruned;
  }  
  return $w;
}

/**
 * Create a stylish and handsome post-pruning report.  The on-screen
 * report is verbose, but the email report isn't.
 */
function proon_format_outcome(&$p, &$outcome, $verbose) {
  $w = proon_calc_report_widths($p, $outcome, $verbose);
  if (! $verbose  &&  $w['total'] < 1) return null;
  $format_f = '%-'.$w['fname'].'s   %s = %s - %s   %s'."\n\n";
  $format_p = '    %'.$w['duelabel'].'s [%'.$w['spanval'].'s]: %3s'."\n";
  $report = null;
  $did_legend = false;
  proon_locale_on();
  $yess = _("yes");
  proon_locale_off();
  foreach ($outcome as $f => $f_out) {
    $d = isset($p[PROON_O_F_DATE_SPAN][$f]) ? $p[PROON_O_F_DATE_SPAN][$f] : '';
    $s = isset($p[PROON_O_F_SIZE_SPAN][$f]) ? $p[PROON_O_F_SIZE_SPAN][$f] : '';
    $c = isset($p[PROON_O_F_COUNT_SPAN][$f]) ? $p[PROON_O_F_COUNT_SPAN][$f] : '';
    $u = isset($p[PROON_O_F_TOSS_UNSEEN][$f]) ? $p[PROON_O_F_TOSS_UNSEEN][$f] : '';
    $dp = $f_out[PROON_OUTCOME_DUE_TO_DATE];
    $sp = $f_out[PROON_OUTCOME_DUE_TO_SIZE];
    $cp = $f_out[PROON_OUTCOME_DUE_TO_COUNT];
    $tot_pruned = $dp + $sp + $cp;
    $err = (isset($f_out[PROON_OUTCOME_ERROR]) ? $f_out[PROON_OUTCOME_ERROR] : '');
    if (! $verbose  &&  ! $err  &&  ($tot_pruned < 1)) continue;
    $span_value_width = max(strlen($d), strlen($s), strlen($c));
    $tot_before = $f_out[PROON_OUTCOME_TOTAL_BEFORE];
    if (! $did_legend) {
      proon_locale_on();
      $report .= sprintf($format_f, PROON_UI_FOLDER.':', _("Remainder"), _("Before"), _("Pruned"), '');
      proon_locale_off();
      $did_legend = true;
    }
    $fI18N = proon_folder_blat_name($f);
    $report .= sprintf($format_f, $fI18N.':', ($tot_before - $tot_pruned), $tot_before, $tot_pruned, $err);
    if ($d !== ''  &&  $d !== null) {
      $report .= sprintf($format_p, PROON_UI_DATE_SPAN, $d, $dp);
    }
    if ($s !== ''  &&  $s !== null) {
      $report .= sprintf($format_p, PROON_UI_SIZE_SPAN, $s, $sp);
    }
    if ($c !== ''  &&  $c !== null) {
      $report .= sprintf($format_p, PROON_UI_COUNT_SPAN, $c, $cp);
    }
    if ($u !== ''  &&  $u !== null) {
      $report .= sprintf($format_p, PROON_UI_UNSEEN_TOO, $yess, '');
    }
    $report .= "\n\n";
  }
  return $report;
}

/**
 * Make a folder name suitable for display.  This includes localizing
 * INBOX or decoding other mailbox names.
 */
function proon_folder_blat_name($folder) {
  if (strcasecmp('INBOX', $folder) == 0) {
    $folderI18N = _("INBOX");
  }
  else {
    $folderI18N = imap_utf7_decode_local($folder);
  }
  return $folderI18N;
}

/**
 * Creates the email headers for the email post-pruning report.
 */
function proon_format_headers(&$p, &$outcome, $verbose) {
  $w = proon_calc_report_widths($p, $outcome, $verbose);
  include_once(SM_PATH . 'class/mime/Rfc822Header.class.php');
  $hdr = new Rfc822Header();
  // I'd prefer to have a '<>' from address, but SM (as of 1.4.4) doesn't
  // fare well with those when they are displayed.
  global $proon_site_from_address;
  $f = 'From: '.$proon_site_from_address."\r\n";
  proon_locale_on();
  $s = 'Subject: '._("Messages automatically pruned:").' '.$w['total']."\r\n";
  proon_locale_off();
  $hdr->parseHeader($f . $s);
  return $hdr;
}

/**
 * This just abstracts getting the list of mailboxes since we need it
 * in a couple of unrelated places and don't want to bother to get it
 * more than once.
 */
function proon_mailbox_list(&$p, &$imapStr) {
  proon_login($imapStr);
  if (! isset($p[PROON_P_OPTIONS][PROON_P_MAILBOXES])) {
    $p[PROON_P_MAILBOXES] = sqimap_mailbox_list($imapStr);
    foreach($p[PROON_P_MAILBOXES] as $mbox) {
      $name = $mbox['unformatted-disp'];
      $p[PROON_P_BOXNAMES][$name] = true;
    }
  }
  return $p[PROON_P_MAILBOXES];
}

/**
 * Utility for keeping track of subscribed folders.
 */
function proon_is_subscribed(&$p, &$target, $imapStr) {
  proon_mailbox_list($p, $imapStr);
  return (isset($p[PROON_P_BOXNAMES][$target]));
}

/**
 * Utility for keeping track of folders which actually exist
 */
function proon_mailbox_exists(&$p, &$tgt, $imapStr) {
  global $folder_prefix;
  $target = $folder_prefix . $tgt;
  if (proon_is_subscribed($p, $target, $imapStr)) return true;
  $exists = sqimap_mailbox_exists($imapStr, $target);
  return $exists;
}

/**
 * Utility for logging time delta for some event.
 */
function proon_log_delta($tag, $t1, $t2 = null, $folder = null) {
  if ($t2 == null) $t2 = microtime();
  list($u1, $s1) = explode(' ', $t1);
  list($u2, $s2) = explode(' ', $t2);
  $delta = (($s2-$s1) + ($u2-$u1));
  $delta = substr($delta, 0, 8); // ought to be enough precision for any geek
  proon_log($delta.' '.$tag, $folder);
}

/**
 * Does arbitrary logging to a file for the squirrel_logger plugin.
 */
function proon_log($s, $folder = null) {
  $f = ($folder ? "[$folder] " : '');
  global $proon_log_file;
  if ($proon_log_file == 'squirrel_logger') {
    include_once(SM_PATH . 'plugins/squirrel_logger/functions.php');
    sl_logit('PROON', $f.$s);
  }
  else {
    if ($proon_log_file != null  &&  $proon_log_file !== false) {
      $l = fopen($proon_log_file, 'a+');
      fputs($l, date('Y-m-d H:i:s O').' ['.$_SESSION['username'].'] '.$f); fputs($l, $s); fputs($l, "\n");
      fflush($l);
    }
  }
}

?>
