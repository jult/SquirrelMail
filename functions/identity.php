<?php

/**
 * identity.php
 *
 * This contains utility functions for dealing with multiple identities
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: identity.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package squirrelmail
 * @since 1.4.2
 */

/** Used to simplify includes */
if (!defined('SM_PATH')) {
    define('SM_PATH','../');
}

include_once(SM_PATH . 'include/load_prefs.php');

/**
* Returns an array of all the identities.
* Array is keyed: full_name, reply_to, email_address, index, signature
* @return array full_name,reply_to,email_address,index,signature
*/
function get_identities() {

    global $username, $data_dir, $domain;

    $em = getPref($data_dir,$username,'email_address');
    if ( ! $em ) {
        if (strpos($username , '@') == false) {
            $em = $username.'@'.$domain;
        } else {
            $em = $username;
        }
    }
    $identities = array();
    /* We always have this one, even if the user doesn't use multiple identities */
    $identities[] = array('full_name' => getPref($data_dir,$username,'full_name'),
        'email_address' => $em,
        'reply_to' => getPref($data_dir,$username,'reply_to'),
        'signature' => getSig($data_dir,$username,'g'),
        'index' => 0 );

    $num_ids = getPref($data_dir,$username,'identities');
    /* If there are any others, add them to the array */
    if (!empty($num_ids) && $num_ids > 1) {
        for ($i=1;$i<$num_ids;$i++) {
            $identities[] = array('full_name' => getPref($data_dir,$username,'full_name' . $i),
            'email_address' => getPref($data_dir,$username,'email_address' . $i),
            'reply_to' => getPref($data_dir,$username,'reply_to' . $i),
            'signature' => getSig($data_dir,$username,$i),
            'index' => $i );
        }
    }

    return $identities;
}

/**
 * Function to save the identities array
 *
 * @param  array     $identities     Array of identities
 */
function save_identities($identities) {

    global $username, $data_dir;

    if (empty($identities) || !is_array($identities)) {
        return;
    }


    $num_cur = getPref($data_dir, $username, 'identities', 0);
    
    $cnt = count($identities);

    // Remove any additional identities in prefs //
    for($i=$cnt; $i <= $num_cur; $i++) {
        removePref($data_dir, $username, 'full_name' . $i);
        removePref($data_dir, $username, 'email_address' . $i);
        removePref($data_dir, $username, 'reply_to' . $i);
        setSig($data_dir, $username, $i, '');
    }

    foreach($identities as $id=>$ident) {

        $key = ($id?$id:'');

        setPref($data_dir, $username, 'full_name' . $key, $ident['full_name']);
        setPref($data_dir, $username, 'email_address' . $key, $ident['email_address']);
        setPref($data_dir, $username, 'reply_to' . $key, $ident['reply_to']);

        if ($id === 0) {
            setSig($data_dir, $username, 'g', $ident['signature']);
        } else {
            setSig($data_dir, $username, $key, $ident['signature']);
        }

    }

    setPref($data_dir, $username, 'identities', $cnt);

}

/**
 * Returns an array with a fixed set of identities
 *
 * @param   array       $identities      Array of identities
 * @param   int         $id             Identity to modify
 * @param   string      $action         Action to perform
 * @param   boolean     $override_edit_identity  For use by plugins
 *                                               where the incoming
 *                                               identities array is
 *                                               trusted (OPTIONAL;
 *                                               default FALSE)
 * @return  array
 */
function sqfixidentities( $identities, $id, $action, $override_edit_identity=FALSE ) {

    global $edit_identity, $data_dir, $username,
           $edit_name, $edit_reply_to;
    $num_cur = (int)getPref($data_dir, $username, 'identities', 0);
    $fixed = array();
    $tmp_hold = array();
    $i = 0;

    if (empty($identities) || !is_array($identities)) {
        return $fixed;
    }

    if ($override_edit_identity)
        $edit_identity_local = TRUE;
    else
        $edit_identity_local = $edit_identity;

    // NOTE that $identities is untrusted user input at this point

    // make sure no one is being sneaky trying to add identities when they shouldn't
    if (!$edit_identity_local && $num_cur !== count($identities)) {
        exit;
    }
    // only allow growing the identities list if action is "save" and the last ident is populated
    // (the input form always has a blank set of inputs for adding a new identity)
    // (but we assume when $override_edit_identities is used, a plugin is not passing in the
    // identities array with a blank element on the end)
    if (!$override_edit_identity && $edit_identity_local
     && ($action !== 'save' || empty_identity(end($identities)))) {
        array_pop($identities);
    }
    // make sure someone not trying to mess with index numbers
    for ($x = 0; $x < $num_cur ; $x++) { // there could be one more when adding but that's ok
        if (!isset($identities[$x]))
            exit;
    }

    foreach( $identities as $key=>$ident ) {

        if ($edit_identity_local && empty_identity($ident)) {
            continue;
        }

        // when user isn't allowed to edit some fields, make sure they are unchanged
        $pref_index = ($key ? $key : '');
        if (!$edit_identity_local && !$edit_name)
            $ident['full_name'] = getPref($data_dir, $username, 'full_name' . $pref_index);
        if (!$edit_identity_local)
            $ident['email_address'] = getPref($data_dir, $username, 'email_address' . $pref_index);
        if (!$edit_identity_local && !$edit_reply_to)
            $ident['reply_to'] = getPref($data_dir, $username, 'reply_to' . $pref_index);

        switch($action) {

            case 'makedefault':

                // can only get here if someone is trying to be sneaky
                if ($num_cur < 2) exit;

                if ($key == $id) {
                    $fixed[0] = $ident;

                    // inform plugins about renumbering of ids
                    do_hook('options_identities_renumber', $id, 'default');

                    continue 2;
                } else {
                    $fixed[$i+1] = $ident;
                }
                break;

            case 'move':

                // can only get here if someone is trying to be sneaky
                if ($num_cur < 2) exit;

                if ($key == ($id - 1)) {
                    $tmp_hold = $ident;

                    // inform plugins about renumbering of ids
                    do_hook('options_identities_renumber', $id , $id - 1);

                    continue 2;
                } else {
                    $fixed[$i] = $ident;

                    if ($key == $id) {
                        $i++;
                        $fixed[$i] = $tmp_hold;
                    }
                }
                break;

            case 'delete':

                // can only get here if someone is trying to be sneaky
                if (!$edit_identity_local) exit;

                if ($key == $id) {
                    // inform plugins about deleted id
                    do_hook('options_identities_process', $action, $id);

                    continue 2;
                } else {
                    $fixed[$i] = $ident;
                }
                break;

            // Process actions from plugins and save/update action //
            default:
                /**
                 * send action and id information. number of hook arguments 
                 * differs from 1.4.4 or older and 1.5.0. count($args) can 
                 * be used to detect modified hook. Older hook does not 
                 * provide information that can be useful for plugins.
                 */
                do_hook('options_identities_process', $action, $id);

                $fixed[$i] = $ident;

        }

        // Inc array index //
        $i++;
    }

    ksort($fixed);
    return $fixed;

}

/**
 * Function to test if identity is empty
 *
 * @param   array   $identity   Identity Array
 * @return  boolean
 */
function empty_identity($ident) {
    if (empty($ident['full_name']) && empty($ident['email_address']) && empty($ident['signature']) && empty($ident['reply_to'])) {
        return true;
    } else {
        return false;
    }
}

