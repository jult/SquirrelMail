<?php

/**
  * SquirrelMail Censor Plugin
  * Copyright (c) 2007 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage censor
  *
  */


global $censor_backend, $tame_profanities_file, $harsh_profanities_file,
       $censor_replacement, $censor_match_profanitiy_length,
       $censor_advanced, $censor_space_is_punctuation, $censor_all_harsh,
       $censor_stop_outgoing_mail;



// This is the string that will be used 
// to replace any censored words.
//
$censor_replacement = '**';



// When this is enabled, the replacement
// string (see $censor_replacement above)
// will be duplicated for every letter in
// the word that is being censored.
// Otherwise, the replacement string will
// only be inserted once for each word it
// replaces.
//
$censor_match_profanitiy_length = 0;



// When advanced filtering is enabled, words
// that are obscured by the user having added
// punctuation in the middle of them are also
// caught and replaced.
//
$censor_advanced = 0;



// When using advanced filtering (see $censor_advanced
// above), should spaces be considered as 
// punctuation when searching for banned words?
//
$censor_space_is_punctuation = 1;



// When this is enabled, the "tame" word list is 
// treated just the same as the "harsh" list, where
// trigger words embedded in other words are 
// considered to be banned.
//
$censor_all_harsh = 0;



// This plugin can stop outgoing mail and let the user
// evaluate what parts of their message were "inappropriate"
// instead of just sending the censored message.  Enable
// this setting to let the user have a chance at rewriting
// their message.  Note that this might give users more 
// help trying to thwart the filtering herein.
// 
$censor_stop_outgoing_mail = 0;



// The backend from which to retrieve censor word lists
//
// Currently supported backends are:
//
//    file
//
$censor_backend = 'file';



// The file that contains "tame" profanities (words that are
// only checked as stand-alone words when scanning user input)
//
// This may be empty, but not if $harsh_profanities_file is 
// also empty.
//
// This only needs to be configured when using the "file"
// backend for this plugin.
//
$tame_profanities_file = SM_PATH . 'plugins/censor/tame_profanities.txt';



// The file that contains "harsh" profanities (words that are
// checked as stand-alone words as well as embedded in other
// words when scanning user input)
//
// This may be empty, but not if $tame_profanities_file is 
// also empty.
//
// This only needs to be configured when using the "file"
// backend for this plugin.
//
$harsh_profanities_file = SM_PATH . 'plugins/censor/harsh_profanities.txt';



