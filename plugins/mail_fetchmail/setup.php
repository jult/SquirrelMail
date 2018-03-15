<?php
/***
Email Fetchmail plugin for SquirrelMail
----------------------------------------
Ver 0.1 Jul 2001 by Jonathan Bayer (jbayer@spamcop.net>
Ver 0.2 Aug 2001 by Jonathan Bayer (jbayer@spamcop.net>
Copied from the mail_fwd plugin written by Ritchie Low

***/
 
/// Location of the wfetch binary
/// Don't forget to change in fetch/config.mk aswell
global $mail_fetchmail_wfetch_binary;
$mail_fetchmail_wfetch_binary = "/usr/local/sbin/wfetch";

/// Location of the buildfetchmailrc binary
/// Don't forget to change in buildfetchmailrc/config.mk aswell
global $mail_fetchmail_buildfetchmailrc_binary;
$mail_fetchmail_buildfetchmailrc_binary = "/usr/local/sbin/buildfetchmailrc";

function squirrelmail_plugin_init_mail_fetchmail() {
  global $squirrelmail_plugin_hooks;
  global $mailbox, $imap_stream, $imapConnection;

  $squirrelmail_plugin_hooks["options_personal_save"]["mail_fetch"] = "mail_fetchmail_save_pref";
  $squirrelmail_plugin_hooks["loading_prefs"]["mail_fetch"] = "mail_fetchmail_load_pref";
  $squirrelmail_plugin_hooks["options_personal_inside"]["mail_fetch"] = "mail_fetchmail_inside";

}

function mail_fetchmail_inside() {
  global $username,$data_dir;
  global $mailfetch_user, $mailfetch_pswd, $mailfetch_srvr, $mailfetch_local;
  global $color;

  ?>
	<tr><td><hr></td><td><hr></td></tr>
      <tr>
        <td align=right>Fetch Emails:   From userid:</td>
        <td><input type=text name=mfetch_user value="<?php echo "$mailfetch_user" ?>" size=30></td>
</tr><tr>
        <td align=right>Password:</td>
        <td><input type=password name=mfetch_pswd value="<?php echo "$mailfetch_pswd" ?>" size=30></td>
</tr><tr>
        <td align=right>Mail Server:</td>
        <td><input type=text name=mfetch_srvr value="<?php echo "$mailfetch_srvr" ?>" size=30></td>
      </tr>
	<tr><td><hr></td><td><hr></td></tr>
  <?php
}

function mail_fetchmail_load_pref() {
  global $username,$data_dir;
  global $mailfetch_user, $mailfetch_pswd, $mailfetch_srvr, $mailfetch_local;

  $mailfetch_user = getPref($data_dir,$username,"mailfetch_user");
  $mailfetch_pswd = getPref($data_dir,$username,"mailfetch_pswd");
  $mailfetch_srvr = getPref($data_dir,$username,"mailfetch_srvr");
  $mailfetch_local = getPref($data_dir,$username,"mailfetch_local");
}

function mail_fetchmail_save_pref() {
  global $username,$data_dir;
  global $mfetch_user, $mfetch_pswd, $mfetch_srvr;
  global $mail_fetchmail_wfetch_binary;
  global $mail_fetchmail_buildfetchmailrc_binary;

  if (isset($mfetch_user)) {
    setPref($data_dir,$username,"mailfetch_user",$mfetch_user);
    setPref($data_dir,$username,"mailfetch_srvr",$mfetch_srvr);
    setPref($data_dir,$username,"mailfetch_pswd",$mfetch_pswd);
  } else {
    setPref($data_dir,$username,"mailfetch_user","");
    setPref($data_dir,$username,"mailfetch_srvr","");
    setPref($data_dir,$username,"mailfetch_pswd","");
  }


  // Escape all evil the user might have inserted 
  $email = EscapeShellCmd($mfetch_user);
  $pswd  = EscapeShellCmd($mfetch_pswd);
  $srvr = EscapeShellCmd($mfetch_srvr);
	if ($email == "") { $email = "-"; }
	if ($pswd  == "") { $pswd  = "-"; }
	if ($srvr  == "") { $srvr  = "-"; }
  echo "<b>";

  passthru($mail_fetchmail_wfetch_binary." ".$username." ".$email." ".$pswd." ".$srvr." ");
  passthru($mail_fetchmail_buildfetchmailrc_binary);
  echo "</b>\n"; 
}

?>
