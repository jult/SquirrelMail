<?php

/**
 * This file contains a whole bunch of internally-used constants and
 * is only of interest to someone working on this plugin's code.
 * Although it is sometimes harmless, the defined values should not
 * be changed without looking at how they're used in the code.
 */

define('PROON_ACT_HELLO', 'hello');
define('PROON_ACT_SAVE', 'save');
define('PROON_ACT_PRUNE_ALL', 'pruneall');
define('PROON_ACT_SHOW_EFFECT', 'effect');
define('PROON_ACT_SHOW_ALL_EFFECTS', 'alleffects');
define('PROON_ACT_PRUNE_THIS', 'now');

define('PROON_OUTCOME_EFFECT', 'left');
define('PROON_OUTCOME_ERROR', 'center');
define('PROON_OUTCOME_PRUNE', 'right');
define('PROON_OUTCOME_TOTAL_BEFORE', 'pretotal');
define('PROON_OUTCOME_TOTAL_UNSEEN', 'totunseen');
define('PROON_OUTCOME_DUE_TO_DATE', 'duetodate');
define('PROON_OUTCOME_DUE_TO_SIZE', 'duetosize');
define('PROON_OUTCOME_DUE_TO_COUNT', 'duetocount');
define('PROON_OUTCOME_TOSSED_UNSEEN', 'tossedunseen');

define('PROON_PRE_DATE', 'date_');
define('PROON_PRE_SIZE', 'size_');
define('PROON_PRE_COUNT', 'count_');
define('PROON_PRE_UNSEEN', 'unseen_');
define('PROON_PRE_MANUAL', 'manual_');
define('PROON_PRE_SHOW_EFFECT', 'effect_');
define('PROON_PRE_PRUNE_THIS', 'now_');
define('PROON_PRE_OPTION', 'proon_');

define('PROON_ANCHOR_OPTS', 'opts');
define('PROON_ANCHOR_FOLDERS', 'fldrs');
define('PROON_ANCHOR_HELP', 'help');
define('PROON_ANCHOR_BOTTOM', 'bot');

define('PROON_F_DATE_OR_SIZE_OR_COUNT', 'dateorsizeorcount');
define('PROON_P_OPTIONS', 'useropts');
define('PROON_P_SITE', 'siteopts');
define('PROON_P_PREFS_SEEN', 'prefs_seen');

// These are preference item names.  If they change,
// values stored in users' preferences will be stranded.
define('PROON_O_F_DATE_SPAN', 'proon_folder_datespans');
define('PROON_O_F_SIZE_SPAN', 'proon_folder_sizespans');
define('PROON_O_F_COUNT_SPAN', 'proon_folder_countspans');
define('PROON_O_F_TOSS_UNSEEN', 'proon_folder_toss_unseen');
define('PROON_O_F_MANUAL_ONLY', 'proon_folder_manual_only');
define('PROON_O_U_PRUNE_INTERVAL', 'proon_user_prune_interval');
define('PROON_O_U_LOGIN_N', 'proon_user_login_prune_frequency');
define('PROON_O_U_LOGIN_COUNT', 'proon_login_count'); // not a user-settable preference
define('PROON_O_U_VIA_TRASH', 'proon_user_via_trash');
define('PROON_O_U_TRASH_TIME', 'proon_user_trash_time');
define('PROON_O_U_UNSUBSCRIBED', 'proon_user_unsubscribed');
define('PROON_O_U_DIS_DATE', 'proon_user_disable_date');
define('PROON_O_U_DIS_SIZE', 'proon_user_disable_size');
define('PROON_O_U_DIS_COUNT', 'proon_user_disable_count');
define('PROON_O_U_SC_ORDER', 'proon_user_sc_order');
define('PROON_O_U_MESSAGE_AFTER', 'proon_user_message_after');
define('PROON_O_U_SCREEN_AFTER', 'proon_user_screen_after');
define('PROON_O_U_DIS_COLOR', 'proon_user_disable_colors');
define('PROON_O_U_PRUNE_LINK', 'proon_user_prune_link');

define('PROON_O_U_T_FIRST', 'first');
define('PROON_O_U_T_NATURAL', 'natural');
define('PROON_O_U_T_LAST', 'last');

define('PROON_P_MAILBOXES', 'boxes');
define('PROON_P_BOXNAMES', 'boxnames');

define('PROON_PREF_BLANK', '!');

define('PROON_LAST_PRUNE', 'proon_bang');

define('PROON_M_DATE', 'date');
define('PROON_M_SIZE', 'size');
define('PROON_M_FLAGS', 'flags');
define('PROON_M_TARGET', 'target');
define('PROON_M_SPANS', 'spans');
define('PROON_M_MBOX', 'mbox');
define('PROON_M_TO_PRUNE', 'toprune');
define('PROON_M_DUE_TO_SIZE', 'duetosize');
define('PROON_M_DUE_TO_DATE', 'duetodate');
define('PROON_M_DUE_TO_COUNT', 'duetocount');
define('PROON_M_UNSEEN_SIZE', 'usize');
define('PROON_M_UNSEEN_COUNT', 'ucount');
define('PROON_M_DELETED_SIZE', 'dsize');

define('PROON_SPAN_DATE', 'date');
define('PROON_SPAN_SIZE', 'size');
define('PROON_SPAN_COUNT', 'count');

define('SYTa', '<small><i>');
define('SYTz', '</i></small>');
define('SYTCH', '<small><input type="checkbox" name="" value="" disabled checked></small>');

?>
