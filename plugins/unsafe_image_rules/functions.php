<?php

/**
 * Unsafe Image Rules plugin - Functions
 *
 * The functions and inclusions needed for this plugin to work.
 *
 * @copyright © 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: functions.php,v 1.12 2006/01/26 22:27:29 jervfors Exp $
 * @package plugins
 * @subpackage unsafe_image_rules
 */

/* Path for SquirrelMail required files. */
if (!defined('SM_PATH')) define('SM_PATH','../../');

/* SquirrelMail required files. */
include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/addressbook.php');

/**
 * This is the legacy for defining sqm_baseuri() and is needed to be compatible
 * with SquirrelMail versions before v1.4.6-rc1 and v1.5.1
 */
if (!function_exists('sqm_baseuri')) {
    include_once(SM_PATH . 'functions/display_messages.php');
}

/**
 * Initialise the default configuration and, if a configuration file exists, let
 * the configuration file overrule them.
 *
 * DON'T EDIT THIS FILE TO CONFIGURE THIS PLUGIN! READ "INSTALL" FOR
 * INSTRUCTIONS!
 */
global $unsafe_image_rules_disable_all;
$unsafe_image_rules_disable_all = 0;
if (file_exists(SM_PATH . 'plugins/unsafe_image_rules/config.php')) {
    include_once(SM_PATH . 'plugins/unsafe_image_rules/config.php');
}

/**
 * TODO: Undocumented function.
 *
 * For some reason, probably backwards compability, "view_unsafe_images" and
 * "view_unsafe_images_match" are passed to other functions using $_GET instead
 * of being defined as global variables.
 *
 * If "view_unsafe_images" is TRUE, the unsafe images will be displayed. If
 * "view_unsafe_images_match" is TRUE, no link will be displayed in
 * unsafe_image_rules_link_do().
 */
function unsafe_image_rules_main_do() {
    global $has_unsafe_images, $data_dir, $username, $message;
    global $unsafe_image_rules_disable_all;

    /**
     * Don't bother if no unsafe images in this body or view unsafe is alread
     * on. With the 1.4 changes we can't check the status of $has_unsafe_images
     * (which is set in the magicHTML function). Unfortunatly then this plugin
     * will process every message. It's not such a big overhead, but would be
     * nice to re-introduce the check in the future.
     */

    if (sqgetGlobalVar('view_unsafe_images', $view_unsafe_images, SQ_GET) &&
            $view_unsafe_images) return;

    /**
     * Handle the case where we always show unsafe images.
     * Only if the adminstrator option is set.
     */

    if (!(isset($unsafe_image_rules_disable_all) && $unsafe_image_rules_disable_all)) {
        $unsafe_image_rules_all = getPref($data_dir, $username, 'unsafe_image_rules_all');
        if (isset($unsafe_image_rules_all) && $unsafe_image_rules_all == 1) {
            $_GET['view_unsafe_images'] = TRUE;
            $_GET['view_unsafe_images_match'] = TRUE;
            return;
        }
    }

    /**
     * Look through rules for a match with this message if required.
     */

    $unsafe_image_rules_trusted = getPref($data_dir, $username,
            'unsafe_image_rules_trusted');
    if (isset($unsafe_image_rules_trusted) && $unsafe_image_rules_trusted &&
            is_in_unsafe_image_rules_list($message)) {
        $_GET['view_unsafe_images'] = TRUE;
        $_GET['view_unsafe_images_match'] = TRUE;
        return;
    }

    /**
     * Look for a match with someone in address book if that option is given.
     */

    $unsafe_image_rules_addr = getPref($data_dir, $username,
            'unsafe_image_rules_addr');
    if (isset($unsafe_image_rules_addr) && $unsafe_image_rules_addr &&
            is_unsafe_image_from_sender_in_address_book($message)) {
        $_GET['view_unsafe_images'] = TRUE;
        $_GET['view_unsafe_images_match'] = TRUE;
        return;
    }

    /**
     * Look for a match with one of our identities if that option is given.
     */

    $unsafe_image_rules_ids = getPref($data_dir, $username,
            'unsafe_image_rules_ids');
    if (isset($unsafe_image_rules_ids) && $unsafe_image_rules_ids &&
            is_unsafe_image_from_our_id($message)) {
        $_GET['view_unsafe_images'] = TRUE;
        $_GET['view_unsafe_images_match'] = TRUE;
        return;
    }
}

/**
 * Figure out if the message we're looking matches any details in our list.
 */
function is_in_unsafe_image_rules_list($message) {
    $trusted = load_unsafe_image_rules();

    /**
     * Loop through options seeing if this messages details match one.
     */

    for ($lp = 0; $lp < count($trusted); $lp++) {
        if ($trusted[$lp]['where'] == 'To or Cc') {
            if (unsafe_image_rules_match($message, 'To', $trusted[$lp]['how'], $trusted[$lp]['what'])) return TRUE;
            if (unsafe_image_rules_match($message, 'Cc', $trusted[$lp]['how'], $trusted[$lp]['what'])) return TRUE;
        } else {
            if (unsafe_image_rules_match($message, $trusted[$lp]['where'],
                        $trusted[$lp]['how'], $trusted[$lp]['what'])) return TRUE;
        }
    }
    return FALSE;
}

/**
 * TODO: Undocumented function.
 */
function unsafe_image_rules_match($message, $where, $how, $what) {
    $header = $message->rfc822_header;

    switch ($where) {
        /**
         * To and Cc fields are arrays, so we have to join then all together
         */
        case 'To' : $search = $header->getAddr_s('to'); break;
        case 'Cc' : $search = $header->getAddr_s('cc'); break;
        /**
         * From and Subject are standard text fields
         */
        case 'From' : $search = $header->getAddr_s('from'); break;
        case 'Subject' : $search = $header->subject; break;
        /**
         * If we don't recognise the $where then how can it match?
         */
        default: return FALSE;
    }

    if ($how == 'regexp') {
        if (!is_good_unsafe_image_regexp($what)) return FALSE;
        return preg_match($what, $search);
    } else {
        return ((stristr($search, $what) == FALSE) ? FALSE : TRUE);
    }
}

/**
 * Function to figure out if sender is in address book.
 */
function is_unsafe_image_from_sender_in_address_book($message) {
    /**
     *Initialize and addressbook
     */

    $abook = addressbook_init();
    $backend = $abook->localbackend;
    $res = $abook->list_addr($backend);

    /**
     * Add addresses to list and see if any match using separate function.
     */

    $address_list = array();
    while (list($undef, $row) = each($res)) {
        $address_list[] = $row['email'];
    }
    return is_unsafe_image_from_address_in_list($message, $address_list);
}

/**
 * Function to figure out if sender is one of our IDs.
 */
function is_unsafe_image_from_our_id($message) {
    global $data_dir, $username;
    $idents = getPref($data_dir, $username, 'identities', 0);
    if ($idents == 0) {
        if (getPref($data_dir, $username, 'email_address')) { $idents = 1; }
    }

    $address_list = array();
    for ($lp = 0; $lp < $idents; $lp++) {
        $address_list[] = getPref($data_dir, $username, 'email_address' . ($lp > 0 ? $lp : ''));
    }
    return is_unsafe_image_from_address_in_list($message, $address_list);
}

/**
 * Given the message and a list of addresses, see if it's from
 * one of those addresses.
 */
function is_unsafe_image_from_address_in_list($message, $address_list) {
    /**
     * Don't bother if null list is given.
     */

    if (array_count_values($address_list) < 1) return FALSE;

    /**
     * Parse important parts out of from and replyto
     * We do this to remove the name (stuff outside <>) and anything but
     * the actual domain name from the address.
     */

    $regex = '/[<|\s]*([\w|\d|\-|\.]+@)([a-z]+[\w|0-9|\-]*\.)*([a-z]{2,7})[>|\s]*$/';

    $header = $message->rfc822_header;

    $num_matches = preg_match_all($regex, $header->getAddr_s('from'), $matches);
    if ($num_matches == 1) $from = $matches[1][0] . $matches[2][0] . $matches[3][0];
    $num_matches = preg_match_all($regex, $header->getAddr_s('reply_to'), $matches);
    if ($num_matches == 1) $replyto = $matches[1][0] . $matches[2][0] . $matches[3][0];

    /**
     * Check all addresses - parsing them too on the way.
     */

    reset($address_list);
    $address = current($address_list);
    do {
        $num_matches = preg_match_all($regex, $address, $matches);
        if ($num_matches == 1) $address = $matches[1][0] . $matches[2][0] . $matches[3][0];
        if (isset($from) && $address == $from) return TRUE;
        if (isset($replyto) && $address == $replyto) return TRUE;
    } while ($address = next($address_list));
    return FALSE;
}

/**
 * Register our options section.
 */
function unsafe_image_rules_optpage_register_block_do() {
    global $optpage_blocks;

    bindtextdomain('unsafe_image_rules', SM_PATH . 'locale');
    textdomain('unsafe_image_rules');

    $optpage_blocks[] = array(
            'name' => _("Unsafe Image Rules"),
            'url'  => sqm_baseuri() . 'plugins/unsafe_image_rules/options.php',
            'desc' => _("Set up rules about how unsafe images in HTML messages are handled."),
            'js'   => false
            );

    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');

}

/**
 * Function to load trusted message details
 */
function load_unsafe_image_rules() {
    global $data_dir, $username;

    $out = array();
    for ($lp=0; $opt = getPref($data_dir, $username, 'unsafe_image_rules' . $lp); $lp++) {
        $ary = explode(',', $opt);
        $out[$lp]['where'] = array_shift($ary);
        /**
         * If $where begins with R means this is a regexp
         * Done this way for backward compatibility
         */

        if (substr($out[$lp]['where'], 0, 1) == 'R') {
            $out[$lp]['how'] = 'regexp';
            $out[$lp]['where'] = substr($out[$lp]['where'], 1);
        } else {
            $out[$lp]['how'] = 'contains';
        }
        /**
         * Whatever is left is the string. If it contained commas then it
         * get's split, so put it back together.
         */
        $out[$lp]['what'] = join(',', $ary);
    }
    return $out;
}

/**
 * Function to figure out if the regular expression passed is good or not.
 * This is a bit simple - I'm open to suggestions as to how to better it.
 */
function set_bad_unsafe_image_regexp($errno, $errstr, $errfile, $errline) {
    global $bad_unsafe_image_regexp;
    $bad_unsafe_image_regexp = 1;
}

/**
 * TODO: Undocumented function.
 */
function is_good_unsafe_image_regexp($regexp) {
    global $bad_unsafe_image_regexp;
    $bad_unsafe_image_regexp = 0;

    set_error_handler('set_bad_unsafe_image_regexp');
    preg_match($regexp, '');
    restore_error_handler();

    return ($bad_unsafe_image_regexp == 1) ? FALSE : TRUE;
}

// Code borrowed/munged from abook_take
function unsafe_image_rules_link_string($str) {
    global $unsafe_image_found_email, $Email_RegExp_Match;

    while (preg_match('(' . $Email_RegExp_Match . ')', $str, $hits)) {
        $str = substr(strstr($str, $hits[0]), strlen($hits[0]));
        if (! isset($unsafe_image_found_email[$hits[0]])) {
            echo '?address=' .
                htmlspecialchars($hits[0]);
            $unsafe_image_found_email[$hits[0]] = 1;
        }
    }

    return;
}

// Code borrowed/munged from abook_take
function unsafe_image_rules_link_array($array) {
    foreach ($array as $item)
        unsafe_image_rules_link_string($item->getAddress());
}

// Code munged/munged from abook_take
function unsafe_image_rules_link_do() {
    global $message;
    sqgetGlobalVar('mailbox', $mailbox, SQ_FORM);
    sqgetGlobalVar('passed_id', $passed_id, SQ_FORM);
    sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
    sqgetGlobalVar('startMessage', $startMessage, SQ_FORM);
    sqgetGlobalVar('sort', $sort, SQ_FORM);

    // Only show the link if we've chosen to view a message that is unsafe.
    // This can (should) be replaced with $has_unsafe_images if that comes
    // back. It would be nicer from a user perspective if they didn't have to
    // click the 'view unsafe images' link first. I don't want to
    // show my link unless I know the message is unsafe but I can't determine
    // that this early in the game without processing the body and I don't
    // want to do that (double processing and duplicates a lot of code)
    if (sqgetGlobalVar('view_unsafe_images', $view_unsafe_images, SQ_GET) &&
            $view_unsafe_images) {
        // And we didn't match an existing rule
        if (sqgetGlobalVar('view_unsafe_images_match', $view_unsafe_images_match, SQ_GET) &&
                $view_unsafe_images_match) return;

        // Would there ever be a case where there isn't a from?
        if (isset($message->rfc822_header->from)) {
            echo ' | <a href="' . sqm_baseuri() . 'plugins/unsafe_image_rules/add.php';
            unsafe_image_rules_link_array($message->rfc822_header->from);

            // Build and print the link
            bindtextdomain('unsafe_image_rules', SM_PATH . 'locale');
            textdomain('unsafe_image_rules');

            echo '&amp;mailbox=' . $mailbox .
                '&amp;passed_id=' . $passed_id .
                '&amp;passed_ent_id=' . $passed_ent_id .
                '&amp;startMessage=' . $startMessage .
                '&amp;sort=' . $sort .
                '">' . _("Always trust images from this sender") . '</a>';

            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }
    }
}

/**
 * TODO: Undocumented function.
 */
function remove_trusted_unsafe_image($id) {
    global $data_dir, $username;

    while ($trusted = getPref($data_dir, $username, 'unsafe_image_rules' . ($id + 1))) {
        setPref($data_dir, $username, 'unsafe_image_rules' . $id, $trusted);
        $id++;
    }

    removePref($data_dir, $username, 'unsafe_image_rules' . $id);
}

?>