<?php
include_once(SM_PATH . 'plugins/proon/utils.php');

// This file is mostly for action which take place as a
// result of clicks on the proon form.  They're separated
// out here mainly to declutter form.php.

/**
 * This function queries an already-selected message folder
 * for information for all messages in it and pops the results
 * into this globby-everything-but-the-kitchen-sink bucket
 * called $m.
 */
function proon_get_messages_to_consider(&$m, &$imapStr) {
  proon_login($imapStr);

  $fetch = 'FETCH 1:* (UID FLAGS RFC822.SIZE INTERNALDATE)';
  $t1 = microtime();
  $result = sqimap_run_command_list($imapStr, $fetch, true/*false*/, $response, $message, true);
  global $proon_log_verbose;
  $proon_log_verbose && proon_log_delta($fetch, $t1, null, $m[PROON_M_TARGET]);
  // I'm not entirely clear on why the results are nested in exactly this way,
  // so I hope it's good for all environments.
  foreach ($result as $subresult) {
    foreach ($subresult as $subsub) {
      // * 3 FETCH (UID 18 FLAGS (\Seen) RFC822.SIZE 12241 INTERNALDATE " 3-Apr-2004 18:56:51 -0700")
      if (preg_match('/\* [0-9]+ FETCH /i', $subsub)) {
	$uid  = (preg_match('/UID ([0-9]+)/i', $subsub, $matches)) ? $matches[1] : null;
	$size = (preg_match('/RFC822.SIZE ([0-9]+)/i', $subsub, $matches)) ? $matches[1] : null;
	$date = (preg_match('/INTERNALDATE "([^"]+)/i', $subsub, $matches)) ? trim($matches[1]) : null;
	$flags = (preg_match('/FLAGS \\(([^\\)]+)/i', $subsub, $matches)) ? trim($matches[1]) : null;
	// I'd like to use strptime, but I'm not sure all IMAP servers use the same
	// datetime format for this, and the locale-specific month abbrevs are a bother.
	$etad = strtotime($date);
	$m[PROON_M_DATE][$uid] = $etad;
	$m[PROON_M_SIZE][$uid] = $size;
	$m[PROON_M_FLAGS][$uid] = $flags;
      }
    }
  }
}

/**
 * This is a little utility method for sorting $m's array of message
 * sizes.  The trick is to sort them in reverse by message date.  So, we turn
 * it into a non-numeric value by prepending an exclamation point,
 * then we normalize the lengths of the date stamps by left padding
 * with zeroes.  After the sort, all that prefixing gets stripped off.
 * This isn't the most efficient way to do things, but it does have the
 * virtue of simplicity.
 */
function proon_arsort_size_by_date(&$m) {
  foreach ($m[PROON_M_SIZE] as $uid => $size) {
    $etad = $m[PROON_M_DATE][$uid];
    $len = strlen($etad);
    $etad = substr('!0000000000000', 0, 14-$len) . $etad . '_';
    $m[PROON_M_SIZE][$uid] = $etad . $size;
  }
  arsort($m[PROON_M_SIZE]);
  foreach ($m[PROON_M_SIZE] as $uid => $size) {
    list(, $size) = explode('_', $size);
    $m[PROON_M_SIZE][$uid] = $size;
  }
}

/**
 * Consider messages by date for pruning.  Simply iterate over
 * all messages and select for pruning any message older than
 * the cutoff date.
 */
function proon_consider_date(&$p, &$m, $datespan) {
  if ($datespan != null  &&  $datespan != '') {
    $cutofftime = (time() - $datespan);

    // not so reliable around or before ye olde epoch date
    // this should have been caught in proon_date_in_seconds, but this is
    // a safety net
    if ($cutofftime < 100000) $cutofftime = 100000;
    foreach ($m[PROON_M_DATE] as $uid => $mdate) {
      // Want to move UIDs from the date list to the "to prune" list.
      // Also remove them from the size list since they are already marked
      // for death.
      if ($mdate < $cutofftime) {
	$m[PROON_M_TO_PRUNE][] = $uid;
	unset($m[PROON_M_SIZE][$uid]);
      }
      // We don't unset this value because it may be needed later if there
      // is a date sort for the message sizes.
      // unset($m[PROON_M_DATE][$uid]);
    }
  }
  $m[PROON_M_DUE_TO_DATE] = count($m[PROON_M_TO_PRUNE]);
}

function proon_sort_conveniently(&$p, &$m, $sizespan, $countspan) {
  if ($sizespan != null   &&  $sizespan != ''
  ||  $countspan != null  &&  $countspan != '') {
    // default is by date
    $sizebydate = true;
    if (proon_effective_option($p, PROON_O_U_SC_ORDER)  == 'size') {
      $sizebydate = false;
    }
    if ($sizebydate) {
      // sort in reverse date order, which means newer first
      proon_arsort_size_by_date($m);
    }
    else {
      // sort in order by size, which means smallest first
      asort($m[PROON_M_SIZE]);
    }
  }
}

/**
 * Look at the remaining messages in a folder and get the
 * total message size down below the threshold.  After sorting
 * the list into the order we want, add up the sizes of messages
 * to keep, and add everything after that to the prune list.
 */
function proon_consider_size(&$p, &$m, $sizespan) {
  $oldcount = count($m[PROON_M_TO_PRUNE]);
  if ($sizespan != null  &&  $sizespan != '') {
    // Don't have to worry about \Deleted messages since they've
    // already been taken out of the message list earlier.  But
    // unseen messages, though maybe removed from the message list, are
    // optionally going to stay in the mailbox and so their sizes
    // must be accounted for.
    $sofar = $m[PROON_M_UNSEEN_SIZE];
    foreach ($m[PROON_M_SIZE] as $uid => $size) {
      $sofar += $size;
      if ($sofar > $sizespan) {
	// We've preserved all we need to; start chopping.
	$m[PROON_M_TO_PRUNE][] = $uid;
	unset($m[PROON_M_SIZE][$uid]);
      }
    }
  }
  $m[PROON_M_DUE_TO_SIZE] = count($m[PROON_M_TO_PRUNE]) - $oldcount;
}

function proon_consider_count(&$p, &$m, $countspan) {
  $oldcount = count($m[PROON_M_TO_PRUNE]);
  if ($countspan != null  &&  $countspan != '') {
    // Don't have to worry about \Deleted messages since they've
    // already been taken out of the message list earlier.  But
    // unseen messages, though maybe removed from the message list, are
    // optionally going to stay in the mailbox and so their sizes
    // must be accounted for.
    $sofar = $m[PROON_M_UNSEEN_COUNT];
    foreach ($m[PROON_M_SIZE] as $uid => $size) {
      ++$sofar;
      if ($sofar > $countspan) {
	// We've preserved all we need to; start chopping.
	$m[PROON_M_TO_PRUNE][] = $uid;
	unset($m[PROON_M_SIZE][$uid]);
      }
    }
  }
  $m[PROON_M_DUE_TO_COUNT] = count($m[PROON_M_TO_PRUNE]) - $oldcount;
}

/**
 * Takes an SM mailbox 'RIGHTS' attribute and interprets it to figure
 * out if the folder is READ-WRITE.  This seems strightforward since
 * the IMAP spec says it should be the literal string 'READ-WRITE'.
 * We relax that to be case-insensitive, and we'll accept it if it
 * contains that string instead of being exactly equal.  Also, at least
 * one IMAP server answer 'READ_WRITE' with an underscore, so check for
 * that, too.  If the proon config variable proon_readwrite_check is
 * false, skip all that and just answer yes.  If proon_readwrite_check
 * is a string, look for it.
 */
function proon_is_readwrite(&$rights) {
  global $proon_readwrite_check;
  if (! $proon_readwrite_check) return true;
  if (stristr($rights, 'READ-WRITE')) return true;
  if (stristr($rights, 'READ_WRITE')) return true;
  if (is_string($proon_readwrite_check)  &&  stristr($rights, $proon_readwrite_check)) return true;
  return false;
}

/**
 * Given a target folder, get the list of messages, shuffle through
 * them according to pruning criteria, and return the list of UIDs
 * to prune from that folder.  The folder is still selected when
 * this method returns.  (We actually return this giant bucket called
 * $m which has all kinds of stuff in it besides the list of things
 * to prune.)
 */
function proon_get_uids_one_folder(&$p, $target, $select_target, $spans, &$imapStr) {

  $m[PROON_M_SIZE] = array();
  $m[PROON_M_DATE] = array();
  $m[PROON_M_TO_PRUNE] = array();
  $m[PROON_M_DUE_TO_DATE] = 0;
  $m[PROON_M_DUE_TO_SIZE] = 0;
  $m[PROON_M_DUE_TO_COUNT] = 0;
  $m[PROON_M_UNSEEN_SIZE] = 0;
  $m[PROON_M_UNSEEN_COUNT] = 0;
  $m[PROON_M_DELETED_SIZE] = 0;

  $m[PROON_M_TARGET] = $target;
  $m[PROON_M_SPANS] = $spans;

  proon_login($imapStr);
  $mbx = sqimap_mailbox_select($imapStr, $select_target);
  $m[PROON_M_MBOX] = $mbx;
  if ($mbx['EXISTS'] == 0) return $m;

  global $proon_readwrite_check;

  if (!proon_is_readwrite($mbx['RIGHTS'])) return $m;

  $toss_unseen = proon_effective_folder_option($p, PROON_O_F_TOSS_UNSEEN, $target);

  proon_get_messages_to_consider($m, $imapStr);
  # Warning: Invalid argument supplied for foreach() in /var/www/squirrelmail-1.4.10a/plugins/proon/action.php on line 217
  foreach ($m[PROON_M_FLAGS] as $uid => $flags) {
    // Completely ignore any deleted messages.  They'll get
    // squeezed during the expunge.  Unfortunately, their sizes
    // goof up the size calculations, so we have to adjust.
    $deleted = (stristr($m[PROON_M_FLAGS][$uid], '\\Deleted') !== false);
    if ($deleted) {
      $m[PROON_M_DELETED_SIZE] += $m[PROON_M_SIZE][$uid];
      unset($m[PROON_M_SIZE][$uid]);
      unset($m[PROON_M_DATE][$uid]);
      unset($m[PROON_M_FLAGS][$uid]);
    }
    if (! $toss_unseen) {
      // Remove unseen messages from the list of prune candidates
      // to protect them from being pruned.  Keep track of their
      // total size, though, for size consideration.
      $seen = (stristr($m[PROON_M_FLAGS][$uid], '\\Seen') !== false);
      if (! $seen) {
	$m[PROON_M_UNSEEN_SIZE] += $m[PROON_M_SIZE][$uid];
	++$m[PROON_M_UNSEEN_COUNT];
	unset($m[PROON_M_SIZE][$uid]);
	unset($m[PROON_M_DATE][$uid]);
	unset($m[PROON_M_FLAGS][$uid]);
      }
    }
  }

  if (!proon_effective_option($p, PROON_O_U_DIS_DATE)) {
    proon_consider_date($p, $m, $spans[PROON_SPAN_DATE]);
  }
  proon_sort_conveniently($p, $m, $spans[PROON_SPAN_DATE], $spans[PROON_SPAN_COUNT]);
  if (!proon_effective_option($p, PROON_O_U_DIS_SIZE)) {
    proon_consider_size($p, $m, $spans[PROON_SPAN_SIZE]);
  }
  if (!proon_effective_option($p, PROON_O_U_DIS_COUNT)) {
    proon_consider_count($p, $m, $spans[PROON_SPAN_COUNT]);
  }

  return $m;
}

/**
 * Form processing when the user has clicked on the "save all"
 * button.  First, validate all the preference values.  Only save
 * them if everything is ok.  If options aren't saved, set the
 * dirty flag.  If they are saved, they're no longer dirty.  (We
 * don't bother to check whether they've changed before we save
 * because the check would be about as expensive as the save itself.)
 *
 * @return boolean indicator of dirtiness of in-memory preferences
 */
function proon_action_save(&$p, &$outcome, &$notice) {
  $ok_to_save = true;

  if (isset($p[PROON_P_OPTIONS][PROON_O_U_PRUNE_INTERVAL])) {
    $raw = $p[PROON_P_OPTIONS][PROON_O_U_PRUNE_INTERVAL];
    $cooked = proon_date_in_seconds($raw);
    if ($cooked === false) {
      proon_locale_on();
      $notice .= _("Problem with '").PROON_UI_INTERVAL.'\'.  ';
      proon_locale_off();
      $ok_to_save = false;
    }
  }

  foreach ($p[PROON_F_DATE_OR_SIZE_OR_COUNT] as $target => $junk) {
    $spans = proon_get_spans($p, $target);
    if (! is_array($spans)) {
      $outcome[$target][PROON_OUTCOME_ERROR] = $spans;
      $ok_to_save = false;
    }
  }
  if ($ok_to_save) {
    proon_set_prefs($p);
    $dirty = false;
  }
  else {
    proon_locale_on();
    $notice .= _("Values were NOT saved due to a problem in one or more fields.");
    proon_locale_off();
    $dirty = true;
  }
  return $dirty;
}

/**
 * A little utility for formatting the numeric part of the message count summary.
 */
function proon_number_tail(&$a) {
  $duedate = $a[PROON_OUTCOME_DUE_TO_DATE];
  if (!$duedate) $duedate = 0;
  $duesize = $a[PROON_OUTCOME_DUE_TO_SIZE];
  if (!$duesize) $duesize = 0;
  $duecount = $a[PROON_OUTCOME_DUE_TO_COUNT];
  if (!$duecount) $duecount = 0;
  // someone reported seeing blank instead of 0, so the above "if" is to 
  // try to counteract that

  $tot = $duedate + $duesize + $duecount;
  $original = $a[PROON_OUTCOME_TOTAL_BEFORE];
  if (!$original) $original = 0;
  $leave = $original - $tot;
  $numbers = "$original - ($duedate + $duesize + $duecount) = $leave";
  return $numbers;
}

/**
 * Transfer some outcome facts from the per-mailbox data to
 * the outcome data.  The need for this is really an artifact
 * of the post-prune reporting (just makes it more convenient).
 */
function proon_transfer_outcome(&$m, &$a) {
  $a[PROON_OUTCOME_TOTAL_BEFORE] = $m[PROON_M_MBOX]['EXISTS'];
  $a[PROON_OUTCOME_TOTAL_UNSEEN] = -1;
  $a[PROON_OUTCOME_DUE_TO_DATE] = $m[PROON_M_DUE_TO_DATE];
  $a[PROON_OUTCOME_DUE_TO_SIZE] = $m[PROON_M_DUE_TO_SIZE];
  $a[PROON_OUTCOME_DUE_TO_COUNT] = $m[PROON_M_DUE_TO_COUNT];
  $a[PROON_OUTCOME_TOSSED_UNSEEN] = -1;
}

/**
 * Form processing when the user has selected "show effect" for a single
 * folder.  Pruning parameters are validated first.
 */
function proon_action_effect(&$p, &$target, &$outcome, &$imapStr) {
  return proon_action_now_or_effect($p, $target, $outcome, $imapStr, false);
}

/**
 * Form processing when the user has selected "prune now" for a single
 * folder.  Pruning parameters are validated first.
 */
function proon_action_now(&$p, &$target, &$outcome, &$imapStr) {
  proon_action_now_or_effect($p, $target, $outcome, $imapStr, true);
}

/**
 * Common logic for "show effect" or "proon now" for a single folder.
 */
function proon_action_now_or_effect(&$p, &$target, &$outcome, &$imapStr, $doit) {
  if (strtoupper($target) == 'INBOX') {
    $select_target = 'INBOX';
  }
  else {
    global $folder_prefix;
    $select_target = $folder_prefix . $target;
  }
  proon_login($imapStr);
  if (! sqimap_mailbox_exists($imapStr, $select_target)) {
    proon_locale_on();
    $outcome[$target][PROON_OUTCOME_ERROR] = _("Folder doesn't exist.");
    proon_locale_off();
    return false;
  }

  $spans = proon_get_spans($p, $target);
  if (! is_array($spans)) {
    $outcome[$target][PROON_OUTCOME_ERROR] = $spans;
    return false;
  }

  $m = proon_get_uids_one_folder($p, $target, $select_target, $spans, $imapStr);
  proon_transfer_outcome($m, $outcome[$target]);

  $numbers = proon_number_tail($outcome[$target]);
  if ($doit) {
    proon_locale_on();
    $outcome[$target][PROON_OUTCOME_PRUNE] = _("Messages (pruned):").'  '. $numbers;
    proon_locale_off();
    if (count($m[PROON_M_TO_PRUNE]) > 0) {
      global $move_to_trash, $trash_folder, $folder_prefix;
      $old_move_to_trash = $move_to_trash;
      $move_to_trash = proon_effective_option($p, PROON_O_U_VIA_TRASH);
      $fptarget = $folder_prefix . $target;
      // This delete method includes an optional copy to trash first.
      proon_login($imapStr);
      sqimap_msgs_list_delete($imapStr, $fptarget, $m[PROON_M_TO_PRUNE]);
      $move_to_trash = $old_move_to_trash;
      // The expunge method takes an optional list of messages, but there's no such
      // things in the IMAP4rev1 spec, and it blows up on my server.  So, expunge the whole folder.
      proon_login($imapStr);
      sqimap_mailbox_expunge($imapStr, $fptarget, true, null/*$m[PROON_M_TO_PRUNE]*/);
    }
  }
  else {
    proon_locale_on();
    $outcome[$target][PROON_OUTCOME_EFFECT] = _("Messages (to prune):").'  '. $numbers;
    proon_locale_off();
  }
  return true;
}

/**
 * Common logic for "show all effects" or "prune all".
 */
function proon_action_effect_or_prune_all(&$p, &$outcome, &$notice, &$imapStr, $doit, $manual) {
  $t1 = microtime();
  $pruneall_notice = false;
  $prune_via_trash = proon_effective_option($p, PROON_O_U_VIA_TRASH);
  $trash_order = PROON_O_U_T_NATURAL;
  if ($prune_via_trash) {
    $trash_order = proon_effective_option($p, PROON_O_U_TRASH_TIME);
    if (!$trash_order) $trash_order = PROON_O_U_T_FIRST;
  }
  global $trash_folder, $folder_prefix;
  $tbox = isset($trash_folder) ? $trash_folder : '';
  if ($tbox != ''  &&  isset($folder_prefix)  &&  strlen($folder_prefix) > 0) {
    $tbox = substr($tbox, strlen($folder_prefix));
  }
  $tbox_has_spans = proon_minimum_folder_option($p, PROON_O_F_DATE_SPAN, $tbox, 'd')
                 || proon_minimum_folder_option($p, PROON_O_F_SIZE_SPAN, $tbox, 's')
                 || proon_minimum_folder_option($p, PROON_O_F_COUNT_SPAN, $tbox, 'c');

  $tbox_manual_only = proon_effective_folder_option($p, PROON_O_F_MANUAL_ONLY, $tbox);

  if ($trash_order == PROON_O_U_T_FIRST  
  &&  $tbox != ''  
  &&  $tbox_has_spans  
  &&  ($manual  ||  !$tbox_manual_only)) {
    if (false === proon_action_now_or_effect($p, $tbox, $outcome, $imapStr, $doit)) {
      $pruneall_notice = true;
    }
  }
  $consider_unsubs = proon_effective_option($p, PROON_O_U_UNSUBSCRIBED);
  // want to look at any folder with a user-specified span or any of
  // the site preferences; we don't care about the values
  $those_with_site_spans = array_merge($p[PROON_P_SITE][PROON_O_F_DATE_SPAN],
				       $p[PROON_P_SITE][PROON_O_F_SIZE_SPAN],
				       $p[PROON_P_SITE][PROON_O_F_COUNT_SPAN]);

  $all_your_bases = array_merge($p[PROON_F_DATE_OR_SIZE_OR_COUNT], $those_with_site_spans);

  foreach ($all_your_bases as $target => $junk) {
    if ($target == $tbox  &&  $trash_order != PROON_O_U_T_NATURAL) continue;
    // we're always going to look at folders mentioned in site preference spans;
    // otherwise, only look at them conditionally if not subscribed
    if (! array_key_exists($target, $those_with_site_spans)) {
      $issubbed = proon_is_subscribed($p, $target, $imapStr);
      if (! $issubbed  &&  ! $consider_unsubs) continue;
      $target_manual_only = proon_effective_folder_option($p, PROON_O_F_MANUAL_ONLY, $target);
      if (! $manual  &&  $target_manual_only) continue;
    }
    if (!proon_mailbox_exists($p, $target, $imapStr)) continue;
    if (false === proon_action_now_or_effect($p, $target, $outcome, $imapStr, $doit)) {
      $pruneall_notice = true;
    }
  }

  if ($trash_order == PROON_O_U_T_LAST
  &&  $tbox != ''  
  &&  $tbox_has_spans  
  &&  ($manual  ||  !$tbox_manual_only)) {
    if (false === proon_action_now_or_effect($p, $tbox, $outcome, $imapStr, $doit)) {
      $pruneall_notice = true;
    }
  }
  $notice = false;
  if ($doit  &&  $pruneall_notice) {
    proon_locale_on();
    $notice = _("Some folders WERE NOT pruned due to improper date, size, or count spans, or possibly other problems.  See the folder list below for details.  Those without problems WERE pruned.");
    proon_locale_off();
  }

  $pairs = array();
  foreach($outcome as $folder => $f_out) {
    $dp = $f_out[PROON_OUTCOME_DUE_TO_DATE];
    $sp = $f_out[PROON_OUTCOME_DUE_TO_SIZE];
    $cp = $f_out[PROON_OUTCOME_DUE_TO_COUNT];
    $tot_pruned = $dp + $sp + $cp;
    if ($tot_pruned > 0) {
      $pairs[$folder] = $tot_pruned;
    }
  }
  $ep = ($doit ? 'PRUNE' : 'EFFECT');
  $ma = ($manual ? 'MANUAL' : 'AUTO');
  $pd = proon_to_pref($pairs);
  if (! $pd) {
    $pd = 'NONE';
  }
  proon_log_delta("PRUNE ALL, doit=$ep, manual=$ma, results=$pd", $t1, null, null);
  return;
}

/**
 * Form processing when the user has selected "show effects" for all
 * folders.  Individual folder pruning parameters are validated first.
 */
function proon_action_alleffects(&$p, &$outcome, &$notice, &$imapStr) {
  $ret = proon_action_effect_or_prune_all($p, $outcome, $notice, $imapStr, false, true);
  return $ret;
}

/**
 * Form processing when the user has selected "prune now" for all
 * folders.  Individual folder pruning parameters are validated first.
 * Folders with good parameters get pruned and others get flagged as
 * not pruned.
 */
function proon_action_pruneall(&$p, &$outcome, &$notice, &$imapStr, $manual) {
  $ret = proon_action_effect_or_prune_all($p, $outcome, $notice, $imapStr, true, $manual);
  return $ret;
}

?>
