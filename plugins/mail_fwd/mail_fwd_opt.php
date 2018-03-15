<?php

   /**
    **  mail_fwd_opt.php
    **
    **  Copyright (c) 1999-2002 Pontus Ullgren
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Displays all options relating to mail_fwd plugin
    **
    **/
    
    chdir("..");
    if (!defined('SM_PATH'))
       define('SM_PATH','../');


    include_once(SM_PATH . 'plugins/mail_fwd/functions.php');
    if (file_exists(SM_PATH . 'include/validate.php')) 
       include_once(SM_PATH . 'include/validate.php');
    else if (file_exists(SM_PATH . 'src/validate.php'))
       include_once(SM_PATH . 'src/validate.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/display_messages.php');
    include_once(SM_PATH . 'functions/imap.php');
    if (file_exists(SM_PATH . 'functions/array.php')) 
       include_once(SM_PATH . 'functions/array.php');
    if (file_exists(SM_PATH . 'src/load_prefs.php')) 
       include_once(SM_PATH . 'src/load_prefs.php');
    else if (file_exists(SM_PATH . 'include/load_prefs.php')) 
       include_once(SM_PATH . 'include/load_prefs.php');


    displayPageHeader($color, 'None');

    $mailfwd_user = getPref($data_dir,$username,"mailfwd_user");
    $mailfwd_local = getPref($data_dir,$username,"mailfwd_local");


    // get previous forward entry from MySQL in case
    // it is out of synch with prefs file...
    //
    if ($mysql_forwarding_enabled) {

        $select_result = get_mysql_forward_for_user();
        $row = mysql_fetch_row($select_result);
        $mailfwd_user = $row[0];

    }


    ?>
    <br>
    <form action="../../src/options.php" method="post">
    <input type=hidden name="optmode" value="submit">
    <table align=center width="95%" border="0" cellpadding="2" cellspacing="0"> 
        <tr>
             <td align=center bgcolor="<?php echo $color[0]; ?>" colspan="2"> 
    	         <b><?php echo _("Options") . ' - ' . _("Mail Forwarding"); ?></b>
	     </td>
         </tr>
         <tr><td>&nbsp;</td></tr>
	 <tr>
	     <td align=right><?php echo _("Forward Emails To").':'; ?></td>
	     <td><input type=text name="mfwd_user" value="<?php echo $mailfwd_user ?>" size="30"></td> 
	 </tr>
    <?php
        if($save_local_enabled) {
           echo '<tr>' .
                '<td></td>' .
	        '<td>'.
	        '<input type="checkbox" value=1 name="mfwd_local" ' . ($mailfwd_local?"checked":"") . '>' .
	        _("Save local copy") .
	        '</td></tr>';
        }
        echo 
          '<tr><td></td><td>'.
          '<input type="submit" name="submit_mail_fwd" value="'._("Submit").'">';

        if ($mysql_forwarding_enabled) {
            echo '&nbsp;<input type="button" value="' . _("Clear") 
               . '" onClick="document.forms[0].mfwd_user.value=\'\'">';
        }

        echo '</td></tr></table><br>'.
          '<table align=center width="55%" border="0" cellpadding="2" cellspacing="0">'. 
          '<tr><td>'.
          _("Enter an address to which all incoming mail should be redirected.");

        echo '<br>' . _("More than one address may be entered by separating them with commas.");

        if (!$mysql_forwarding_enabled) {
            echo '<br>' . _("By filling out this form you are able to automatically forward all your new mail to a different email address. Warning: This form changes your .forward file and will destroy any existing custom made .forward file that may exist in you home directory.");
        }
        else {
            echo '<br>' . _("Press clear and submit to disable forwarding and retain all mail in this account.");
        }

        echo '</td></tr>'.
            '</table>';
        echo '</form>';
        echo '</body></html>';
?>
