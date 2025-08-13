<?php

/**
  * SquirrelMail Autocomplete Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2005 Graham <gsm-smpi@soundclashchampions.com>
  * Copyright (c) 2001 Tyler Akins
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage autocomplete
  *
  */

global $autocomplete_enable, $autocomplete_enable_allow_override,
       $enable_remote_address_book_preload, $ac_debug, $autocomplete_only_personal,
       $autocomplete_only_personal_allow_override,
       $autocomplete_minimum_number_characters, $max_list_height,
       $autocomplete_minimum_number_characters_allow_override,
       $autocomplete_preload, $autocomplete_preload_allow_override,
       $autocomplete_restrict_matching, $autocomplete_restrict_matching_allow_override,
       $autocomplete_match_nicknames, $autocomplete_match_nicknames_allow_override,
       $autocomplete_match_fullnames, $autocomplete_match_fullnames_allow_override,
       $autocomplete_match_emails, $autocomplete_match_emails_allow_override,
       $autocomplete_by_tab, $autocomplete_by_tab_allow_override,
       $autocomplete_match_case, $autocomplete_match_case_allow_override;



// Should autocompletion functionality be enabled by default?
//
//    0 = no
//    1 = yes
//
$autocomplete_enable = 1;



// Should users be able to turn autocomplete on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_enable_allow_override = 1;



// Should only personal contacts be searched?
//
//    0 = no (search global address books as well)
//    1 = yes (only search personal address book)
//
$autocomplete_only_personal = 0;



// Should users be able to turn personal/global searches on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_only_personal_allow_override = 1;



// Should users' entire address books be pre-fetched and loaded into
// the compose screen?
//
// This allows fast lookups, but for users with large address books,
// it may cause the compose screen to load slowly and/or consume more
// bandwidth than necessary.  If disabled, all address lookups are
// done dynamically, which may result in more server requests.
//
// If any LDAP-backed address books are in use, please refer to the
// $enable_remote_address_book_preload setting for more information.
//
//    0 = no
//    1 = yes
//
$autocomplete_preload = 1;



// Should users be able to turn preloading on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_preload_allow_override = 1;



// If you have a LDAP-backed address book, you may not wish for the
// entire directory to be loaded when pre-fetching all contacts
// (when $autocomplete_preload is enabled and $autocomplete_only_personal
// is disabled).  You can choose to either allow it to be loaded with
// all other contacts (set this to 1) or exclude it from pre-fetch
// mode (set this to 0 (zero)).  Note that when $autocomplete_preload
// is disabled, LDAP lookups will always be enabled unless
// $autocomplete_only_personal is enabled.
//
// Note that when this setting is enabled, the number of contacts
// loaded may be subject to the "maxrows" you may have configured for
// the LDAP server.
//
// Also, if you want to enable this setting, you must also enable
// listing of your LDAP address book backend.  In SquirrelMail
// version 1.5.1 and above, the "listing" configuration setting has
// to be enabled for the LDAP server; in SquirrelMail version 1.4.23
// and up, you should add "$ldap_abook_allow_listing = TRUE;"
// (without the quotes) to config/config_local.php; in versions prior
// to 1.4.23, you need to make a small change to the file
// functions/abook_ldap_server.php - at the bottom of the file, in
// function list_addr(), you need to comment out the first "return"
// statement and un-comment the second one.  Further instructions
// are found in the comments for that function.
//
$enable_remote_address_book_preload = 0;



// Should text matches in contact data only be done starting at
// the beginning (of a name, email address, etc.)?  If disabled,
// matches will be found anywhere in the contact data.
//
//    0 = no
//    1 = yes
//
$autocomplete_restrict_matching = 0;



// Should users be able to turn restrictive matching on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_restrict_matching_allow_override = 1;



// How many characters need to be typed in order to start autocompletion?
//
$autocomplete_minimum_number_characters = 1;



// Should users be able to set the number of characters before autocomplete themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_minimum_number_characters_allow_override = 1;



// Should matching be done against contact nicknames?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_nicknames = 1;



// Should users be able to turn nickname matching on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_nicknames_allow_override = 1;



// Should matching be done against contact full names?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_fullnames = 1;



// Should users be able to turn full name matching on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_fullnames_allow_override = 1;



// Should matching be done against contact email addresses?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_emails = 1;



// Should users be able to turn email matching on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_emails_allow_override = 1;



// Should the plugin match letter case?
//
//    0 = no (case insensitive matching)
//    1 = yes (only match if case matches)
//
$autocomplete_match_case = 0;



// Should users be able to turn case insensitive matching on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_match_case_allow_override = 1;



// Should autocomplete be triggered by the Tab key?
//
//    0 = no, don't autocomplete when Tab is pressed
//    1 = yes, autocomplete when Tab is pressed
//
$autocomplete_by_tab = 1;



// Should users be able to turn autocomplete-by-tab on and off themselves?
//
//    0 = no
//    1 = yes
//
$autocomplete_by_tab_allow_override = 1;



// If vertical scrolling causes problems, set this to zero.  Otherwise,
// this is the maximum height (in pixels) of the dropdown list before
// it starts to scroll
//
// Note - due to issues IE has with setting maximum height, if you
// want to disable this, it may be better to set it to some very large
// number INSTEAD of zero.
//
$max_list_height = 300;



// Debug mode
// 
$ac_debug = 0;



