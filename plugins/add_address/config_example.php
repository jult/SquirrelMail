<?php

/**
  * SquirrelMail Add Address Plugin
  *
  * Copyright (c) 2008-2012 Paul Lesniewski <paul@squirrelmail.org>,
  * Copyright (c) 1999-2008 The SquirrelMail Project Team
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage add_address
  *
  */

global $abook_lookup_threshold, $allow_dns_verification,
       $aa_dont_include_identities, $dynamic_address_book_link,
       $no_ldap_for_auto_add, $no_ldap_for_add_link,
       $no_ldap_for_user_input;


// When searching to see if an address is already in a user's
// address book, if the address is one of the user's outgoing
// email addresses (aliases/identites), should it be skipped
// even if it is not in the address book?
//
// 0 = No.  Include identities too for addition to the address
//     book.
//
// 1 = Yes.  Skip identities; do not offer them for inclusion in
//     the address book
//
// This is a default value that users can override on the
// display options page.
//
$aa_dont_include_identities = 0;



// This plugin allows users to pass email addresses through
// DNS-based verification.  If this creates problems, set
// this to 0 (zero) to disallow this feature.
//
$allow_dns_verification = 1;



// When testing if several addresses are in an address book,
// as of SquirrelMail 1.4.16, this plugin asks the address
// book to look up each address one at a time.  If using
// certain address book backends with large numbers of
// address lookups, this may create unnecessary load on the
// backend.  When this threshold is met (number of addresses
// being looked up at a time), the entire address book contents
// are instead pulled from the backend and cached long enough
// to search for each address in memory instead.
//
// Finding the right threshold is likely to be both system-
// specific and dependent upon the address backend type being
// used and overall system load, as well as the average size
// of user address books.  Please send feedback on what numbers
// worked for you along with details about your system.
//
$abook_lookup_threshold = 50;



// The "Add to Address Book" link that is displayed on the
// message view screen can be supressed when the message
// contains no addresses that aren't already in the user's
// address book.  However, this requires extra work when
// displaying each message.
//
// 0 = Do not pre-process messages to determine if the
//     "Add to Address Book" link should be shown
//
// 1 = Show the "Add to Address Book" link only if necessary
//
$dynamic_address_book_link = 1;



// Addresses are normally tested against all address book
// backends.  If it is not desirable to test addresses against
// LDAP backends due to server load or other issues, the
// following settings can be used to fine-tune LDAP lookups.
//
// NOTE: before using these settings, you should consider
//       disabling $dynamic_address_book_link first, which
//       may be sufficient.  Enabling any of the settings
//       below can result in users adding addresses that
//       are already in their global address books to their
//       personal address books (this can in turn cause
//       problems if the global address book is updated and
//       managed by a central authority).
//
// $no_ldap_for_auto_add will ignore LDAP backends when
// scanning outgoing messages for addresses to put into
// the user's address book.
//
// $no_ldap_for_add_link will ignore LDAP backends when
// making dynamic address book checks to determine if the
// "Add to Address Book" link should be shown.  This setting
// will have no effect unless $dynamic_address_book_link is
// turned on.
//
// $no_ldap_for_user_input will ignore LDAP backends when
// on the screen that allows the user to fine-tune and
// confirm address book additions.  Load from this feature/page
// can be exepcted to be lower than the two features above,
// thus this setting can usually be left turned off.
//
// 0 = Disable setting
// 1 = Enable setting
//
$no_ldap_for_auto_add = 0;
$no_ldap_for_add_link = 0;
$no_ldap_for_user_input = 0;



