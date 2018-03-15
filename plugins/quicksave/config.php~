<?php


global $quicksave_cookie_days, $quicksave_cookie_hours, 
       $quicksave_cookie_minutes, $maxCookieLength, $maxCookies, 
       $useMultipleCookies, $maxSingleCookieLength, 
       $default_save_frequency, $default_cookie_encryption,
       $default_save_frequency_units,
       $user_can_override_save_frequency,
       $user_can_override_save_frequency_units,
       $user_can_override_encryption, 
       $show_message_body_on_recover_notice,
       $show_message_recovery_alert_in_motd,
       $show_message_body_on_recover_motd_notice,
       $show_message_details_in_motd;



// The default message save frequency and units.
// Units can be either "seconds" or "miliseconds".
//
$default_save_frequency = 5;
$default_save_frequency_units = 'seconds';



// Can users change the save frequency or units?
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$user_can_override_save_frequency = 1;
$user_can_override_save_frequency_units = 1;



// The default cookie encryption level: 
// "none", "low", "medium" or "moderate".
//
$default_cookie_encryption = 'none';



// Can users change the cookie encryption level?
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$user_can_override_encryption = 1;



// Show the first part of the message body in the recovery alert?
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$show_message_body_on_recover_notice = 0;



// Show a message recovery alert in the MOTD (Message
// Of The Day) section above the message list when first
// logging in?
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$show_message_recovery_alert_in_motd = 1;



// Show message details (To:, Subject:, etc) in the recovery
// alert in the MOTD (Message Of The Day) area?  If not, a
// more brief non-detailed alert will appear.
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$show_message_details_in_motd = 0;



// Show the first part of the message body in the MOTD recovery alert?
// This has no effect if $show_message_details_in_motd is off.
// Set to 1 or 0 (zero), meaning yes or no respectively.
//
$show_message_body_on_recover_motd_notice = 0;



// These values can be changed to determine how long QuickSave
// cookies should be kept on users' computers
//
$quicksave_cookie_days = 0;
$quicksave_cookie_hours = 1;
$quicksave_cookie_minutes = 0;



// These can be very temperamental settings.  These values are about maximum
// for IE6, default security settings.  If you get "must be logged in"
// messages or blank restores, try lowering these settings.  You can use
// multiple cookie functionality to break long bodies into multiple cookies
// and potentially save longer messages, but tests with IE6 proved that it 
// chokes on anything larger than the settings below, which, when all's said
// and done, is less than the single cookie method!  Therefore, it is 
// recommended that you leave $useMultipleCookies at zero (which is also
// liable to have better performance, anyway).
//
$maxSingleCookieLength = 3320; // this works for "medium" encryption
// $maxSingleCookieLength = 3610; // this works for "moderate" encryption
// $maxSingleCookieLength = 3630; // this works for "low" encryption
// $maxSingleCookieLength = 3730; // this works for "no" encryption

$useMultipleCookies = 0;

$maxCookieLength = 700; // only applicable when $useMultipleCookies = 1

$maxCookies = 5; // only applicable when $useMultipleCookies = 1



