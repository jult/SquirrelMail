<?php
/**
** Email Forwarding plugin for SquirrelMail
**  $File$
**
**  Copyright (c) 1999-2002 Pontus Ullgren
**  Licensed under the GNU GPL. For full terms see the file COPYING.
**
**  Contains common functions used in the mail_fwd plugin.
**
**/


if (defined('SM_PATH'))
   include_once(SM_PATH . 'plugins/mail_fwd/config.php');
else
   include_once('../plugins/mail_fwd/config.php');


function mail_fwd_optpage_register_block_do () {
    // Gets added to the user's OPTIONS page.
    global $optpage_blocks;

    if ( !soupNazi() ) {
        /* Register Squirrelspell with the $optionpages array. */
        $optpage_blocks[] = array(
            'name' => _("Mail Forwarding Options"),
            'url'  => '../plugins/mail_fwd/mail_fwd_opt.php',
            'desc' => _("You may forward your incoming mail to other email addresses by using this option."),
            'js'   => FALSE
        );													           }														   
}

function mail_fwd_save_pref_do() {
    if ( (float)substr(PHP_VERSION,0,3) < 4.1 ) {
        global $_SESSION, $_GET, $_POST;
    }
    global $optmode;
    global $username, $data_dir;
    global $mail_fwd_wfwd_binary, $save_local_enabled;
    global $mysql_forwarding_enabled, $mysql_server, $mysql_manager_id,
           $mysql_manager_pwd, $mysql_database, $mysql_table, 
           $mysql_userid_field, $mysql_forward_field;

    if ( isset($optmode) && $optmode == 'submit') {

        if (isset($_POST['mfwd_user'])) {
            setPref($data_dir,$username,"mailfwd_user",$_POST['mfwd_user']);
        } else {
            setPref($data_dir,$username,"mailfwd_user","");
        }

        if ( isset($_POST['mfwd_local']) ) {
            setPref($data_dir,$username,"mailfwd_local", $_POST['mfwd_local']);
        } else {
            setPref($data_dir,$username,"mailfwd_local", "");
        }

        // Escape all evil the user might have inserted 
        $email = escapeshellcmd($_POST['mfwd_user']);

        if( $save_local_enabled && isset($_POST['mfwd_local']) ) {
            if ( ctype_print($email) ) {
                $email = "\\".$username." ".$email;
            }
        }

        echo "<b>";
        echo "<b>";
        
        if (! $mysql_forwarding_enabled ) {
            passthru($mail_fwd_wfwd_binary." ".$username." ".$email);
        }
        else {
        	
 
            //
            // forward addresses stored in mysql table...
            //


            $optmode = 'display';

    
            // check for previous forward entry...
            //
            $select_result = get_mysql_forward_for_user();

            
            // build the update query
            //
            $update_string =  'UPDATE '. $mysql_table . ' SET ' . $mysql_forward_field
                . ' = "' . $email . '" WHERE ' . $mysql_userid_field . ' = "' . $username . '"';


            // if there were no rows for this user, we need
            // to insert instead of update...
            //
            if (mysql_num_rows($select_result) == 0) {
                $update_string = 'INSERT INTO ' . $mysql_table . ' (' 
                               . $mysql_userid_field . ', ' 
                               . $mysql_forward_field . ') VALUES ("' 
                               . $username . '", "' . $email  . '")';
            }


            // if there is no forwarding address, delete the row from the table
            //
            if ($email == '') {
                $update_string = 'DELETE FROM '. $mysql_table   
                    . ' WHERE ' . $mysql_userid_field . ' = "' . $username . '"';
            }


            // get mysql connection
            //
            $databaseConnection = mysql_connect($mysql_server, $mysql_manager_id, $mysql_manager_pwd);


            // make sure connection is OK
            //
            if ( ! $databaseConnection ) {
                echo '<p align=center><b>Error - Database connection failed.'
                    . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
                return;
            }

            
            // connect to desired database
            //
            if ( !mysql_select_db($mysql_database,$databaseConnection) ) {
                echo '<p align=center><b>Error - Database not found.'
                    . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
                return;
            }


            // execute the update (or insert)
            //
            if (mysql_query($update_string, $databaseConnection)) {

                echo '<p align=center><b>Forwarding settings saved.</b></p>';
                return;
            	
            }

            else {

                echo '<p align=center><b>Error - Could not save forwarding settings.'
                    . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
                return;

            }

        
        }
        
        echo "</b><br>\n"; 
    }

}


function get_mysql_forward_for_user() {

    global $username;
    global $mysql_forwarding_enabled, $mysql_server, $mysql_manager_id,
           $mysql_manager_pwd, $mysql_database, $mysql_table, 
           $mysql_userid_field, $mysql_forward_field;


    // get mysql connection
    //
    $databaseConnection = mysql_connect($mysql_server, $mysql_manager_id, $mysql_manager_pwd);


    // make sure connection is OK
    //
    if ( ! $databaseConnection ) {
        echo '<p align=center><b>Error - Database connection failed.'
            . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
        return;
    }

            
    // connect to desired database
    //
    if ( !mysql_select_db($mysql_database,$databaseConnection) ) {
        echo '<p align=center><b>Error - Database not found.'
            . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
        return;
    }


    // build query statement to see if user already
    // has an entry or not
    //
    $query_string = 'SELECT ' . $mysql_forward_field
        . ' FROM '  . $mysql_table
        . ' WHERE ' . $mysql_userid_field . '="' . $username . '"';


    // execute query
    //
    $select_result = mysql_query($query_string, $databaseConnection);


    // make sure the query was OK
    //
    if (!$select_result) {
        echo '<p align=center><b>Error - Could not retrieve forwarding settings.'
            . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
        return;
    }


    // if there was more than one row returned, there
    // are issues best resolved by sysadmin...
    //
    if (mysql_num_rows($select_result) > 1) {
        echo '<p align=center><b>Error - More than one forwarding entry found for ' . $username . '.'
            . '&nbsp;&nbsp;Please <a href="mailto:webmaster">contact</a> the system administrator.</b></p>';
        return;
    }


    return $select_result;


}

?>
