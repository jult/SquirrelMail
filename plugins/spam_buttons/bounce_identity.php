<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  * This file is originally based on the Bounce plugin by Seth E.
  * Randall, updated by Paul Lesniewski for the Spam Buttons plugin.
  *
  */


// identity.php was added in SquirrelMail 1.4.2
//
if (!file_exists(SM_PATH . 'functions/identity.php')) 
{
    include_once(SM_PATH . 'include/load_prefs.php');

    // ripped from functions/identity.php from 1.4.11SVN on 2007/09/11
    //
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
} 
else 
{
    include_once(SM_PATH . 'functions/identity.php');
}


