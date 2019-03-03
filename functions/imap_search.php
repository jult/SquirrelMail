<?php

/**
 * imap_search.php
 *
 * IMAP search routines
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: imap_search.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package squirrelmail
 * @subpackage imap
 * @deprecated This search interface has been largely replaced by asearch
 */

/**
 * Load up a bunch of SM functions */
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/date.php');
require_once(SM_PATH . 'functions/mailbox_display.php');
require_once(SM_PATH . 'functions/mime.php');

/**
  * @param string $search_where The location to search (see RFC3501 section 6.4.4)
  *                             If this string contains underscores, they are
  *                             interpreted as an OR condition, thus "TO_CC" will
  *                             result in a search of the TO *or* CC headers
  */
function sqimap_search($imapConnection, $search_where, $search_what, $mailbox,
                       $color, $search_position = '', $search_all, $count_all) {

    global $message_highlight_list, $squirrelmail_language, $languages,
           $index_order, $pos, $allow_charset_search, $uid_support,
	   $imap_server_type;

    $pos = $search_position;

    $urlMailbox = urlencode($mailbox);

    /* construct the search query, taking multiple search terms into account */
    $multi_search = array();
    $search_what  = trim($search_what);
    $search_what  = preg_replace('/[ ]{2,}/', ' ', $search_what);
    $multi_search = explode(' ', $search_what);
    $search_string = '';

    if (strtoupper($languages[$squirrelmail_language]['CHARSET']) == 'ISO-2022-JP') {
        foreach($multi_search as $idx=>$search_part) {
            $multi_search[$idx] = mb_convert_encoding($search_part, 'JIS', 'auto');
        }
    }

    $and_search = TRUE;
    while ($pos = strpos($search_where, '_')) {
        $and_search = FALSE;
        $search_where = 'OR ' . substr($search_where, 0, $pos) . ' %s ' . substr($search_where, $pos + 1);
    }
    $search_where .= ' %s ';
    $search_parts = array_filter(explode(' %s ', $search_where));

    $search_literal = array('commands'=>array(), 'literal_args'=>array());
    $use_search_literal = FALSE;
    foreach ($multi_search as $string) {
        //FIXME: why JIS?  shouldn't input be in EUC-JP?  this is copied from DEVEL
        if (isset($languages[$squirrelmail_language]['CHARSET']) &&
            strtoupper($languages[$squirrelmail_language]['CHARSET']) == 'ISO-2022-JP')
            $string = mb_convert_encoding($string, 'JIS', 'auto');
        if (preg_match('/["\\\\\r\n\x80-\xff]/', $string))
            $use_search_literal = TRUE;
        foreach ($search_parts as $chunk) {
            $search_literal['commands'][] = $chunk;
            $search_literal['literal_args'][] = $string;
        }
        $search_string .= str_replace('%s', 
                         '"'
                       . str_replace(array('\\', '"'), array('\\\\', '\\"'), $string)
                       . '"', $search_where);
    }

    $search_string = trim($search_string);
    $original_search_literal = $search_literal;

    /* now use $search_string in the imap search */
    if ($allow_charset_search && isset($languages[$squirrelmail_language]['CHARSET']) &&
        $languages[$squirrelmail_language]['CHARSET']) {
        if ($use_search_literal) {
            $search_literal['commands'][0] = 'SEARCH CHARSET '
                . strtoupper($languages[$squirrelmail_language]['CHARSET'])
                . ' ALL ' . $search_literal['commands'][0];
        } else {
            $ss = "SEARCH CHARSET "
                . strtoupper($languages[$squirrelmail_language]['CHARSET'])
                . ($and_search ? ' ALL' : '') . " $search_string";
        }
    } else {
        if ($use_search_literal) {
            $search_literal['commands'][0] = 'SEARCH ALL ' . $search_literal['commands'][0];
        } else {
            $ss = "SEARCH ALL $search_string";
        }
    }

    /* read data back from IMAP */
    if ($use_search_literal) {
        $readin = sqimap_run_literal_command($imapConnection, $search_literal, false, $result, $message, $uid_support);
    } else {
        $readin = sqimap_run_command($imapConnection, $ss, false, $result, $message, $uid_support);
    }

    /* try US-ASCII charset if search fails */
    if (isset($languages[$squirrelmail_language]['CHARSET'])
        && strtolower($result) == 'no') {
        if ($use_search_literal) {
            $original_search_literal['commands'][0] = 'SEARCH CHARSET "US-ASCII" ALL '
                                                    . $original_search_literal['commands'][0];
        } else {
            $ss = "SEARCH CHARSET \"US-ASCII\" ALL $search_string";
        }
        if ($use_search_literal) {
            $readin = sqimap_run_literal_command($imapConnection, $search_literal, false, $result, $message, $uid_support);
        } else {
            $readin = sqimap_run_command($imapConnection, $ss, false, $result, $message, $uid_support);
        }
    }

    unset($messagelist);

    /* Keep going till we find the SEARCH response */
    foreach ($readin as $readin_part) {
        /* Check to see if a SEARCH response was received */
        if (substr($readin_part, 0, 9) == '* SEARCH ') {
            $messagelist = preg_split("/ /", substr($readin_part, 9));
        } else if (isset($errors)) {
            $errors = $errors.$readin_part;
        } else {
            $errors = $readin_part;
        }
    }

    /* If nothing is found * SEARCH should be the first error else echo errors */
    if (isset($errors)) {
        if (strstr($errors,'* SEARCH')) {
            return array();
        }
        echo '<!-- '.sm_encode_html_special_chars($errors) .' -->';
    }


    global $sent_folder;

    $cnt = count($messagelist);
    for ($q = 0; $q < $cnt; $q++) {
        $id[$q] = trim($messagelist[$q]);
    }
    $issent = ($mailbox == $sent_folder);

    $msgs = fillMessageArray($imapConnection,$id,$cnt);

    return $msgs;
}



