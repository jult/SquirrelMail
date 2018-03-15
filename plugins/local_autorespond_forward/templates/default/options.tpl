<?php

/**
  * options.tpl
  *
  * Template for the configuration screen for the Local User 
  * Autoresponder and Mail Forwarder plugin.
  *
  * The following variables are available in this template:
  *
FIXME: sync the $color array with 1.5.2 if it is no longer available(?)
  * array   $color        The SquirrelMail colors array
  * string  $vac_action   This value will be either empty or it will
  *                       contain the string "CHANGE_VACATION_SETTINGS",
  *                       in which case, the user should be notified
  *                       that her settings were accepted.
  * boolean $do_autoreply When TRUE/1, the user has enabled the autoreply
  *                       functionality.
  * int     $ident        The number of possible user identities from 
  *                       which the vacation messages should be sent from;
  *                       should always be at least 1.
  * int     $idents       The list of possible user identities from 
  *                       which the vacation messages should be sent from
  * int     $identity     The selected index in $idents that is currently
  *                       being used to send vacation messages
  * string  $vacation_subject  The current subject used for autoreply responses
  * string  $vacation_message  The current message used for autoreply responses
  * boolean $maintain_autoresponder When TRUE/1, we need to offer to 
  *                                 accept vacation message and subject
  *                                 as well as the ability to turn the
  *                                 autoresponder on and off
  * boolean $maintain_forwarding    When TRUE/1, we need to offer to 
  *                                 accept a list of forwarding addresses
  *                                 as well as the ability to turn forwarding
  *                                 of incoming mail on and off as well as
  *                                 local delivery
  * boolean $do_forward        When TRUE/1, forwarding is currently turned on
  * boolean $no_local_delivery When TRUE/1, local delivery is currently off
  * string  $forward_addresses The list of current addresses mail is forwarded to
  * int     $allow_black_hole  When zero, forwarding must be enabled to allow
  *                            local delivery to be suspended; when 1, forwarding
  *                            OR an autoresponse must be active in order to allow
  *                            local delivery to be suspended; when 2, local 
  *                            delivery may be suspended freely
  * array   $vl_errors         A list of error messages to be displayed
  * array   $vl_messages       A list of messages/notices to be displayed
  *                       
  *
  * @copyright &copy; 1999-2009 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


?>

<br />
<table width="95%" align="center" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <td bgcolor="<?php echo $color[0]; ?>">
      <center>
        <b><?php if ($maintain_autoresponder && $maintain_forwarding) echo _("Options - Autoresponder / Mail Forwarding"); else if ($maintain_autoresponder) echo _("Options - Autoresponder"); else echo _("Options - Mail Forwarding"); ?></b>
      </center>
    </td>
  </tr>
</table>

<center>
  <table width="95%" border="0" cellpadding="2" cellspacing="0">
    <tr>
      <td>

<?php if (!empty($vl_errors)) { ?>

        <br />

        <table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[9]; ?>">
          <tr>
            <td>
              <table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[4]; ?>">
                <tr>
                  <td align="center" bgcolor="<?php echo $color[0]; ?>">
                    <font color="<?php echo $color[2]; ?>">
                      <b><?php echo _("ERROR:"); ?></b>
                    </font>
                  </td>
                </tr>
                <tr>
                  <td>
                    <table cellpadding="1" cellspacing="5" align="center" border="0">
                      <tr>
                        <td align="left">
                          <?php foreach ($vl_errors as $vl_error) echo $vl_error . '<br />'; ?>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

<?php } else if ($vac_action == 'CHANGE_VACATION_SETTINGS') { ?>

        <br />

        <table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[9]; ?>">
          <tr>
            <td>
              <table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[4]; ?>">
                <tr>
                  <td align="center" bgcolor="<?php echo $color[0]; ?>">
                    <font color="<?php echo $color[2]; ?>">
                      <b><?php echo _("Settings Saved"); ?></b>
                    </font>
                  </td>
                </tr>

<?php if (!empty($vl_messages)) { ?>

                <tr>
                  <td>
                    <table cellpadding="1" cellspacing="5" align="center" border="0">
                      <tr>
                        <td align="left">
                          <?php foreach ($vl_messages as $vl_message) echo $vl_message . '<br />'; ?>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

<?php } ?>

              </table>
            </td>
          </tr>
        </table>

<?php } ?>

        <br />
        <form method="post" action="options.php">
          <p>

<?php if ($maintain_autoresponder) { ?>

            <input type="checkbox" value="1" id="do_autoreply_id" name="do_autoreply"<?php if ($do_autoreply) echo ' checked="checked"'; ?> />
            <label for="do_autoreply_id"><?php echo _("Enable auto-reply to sender"); ?></label>
            <br />
            <br />

   <?php if ($ident > 1) { 

      echo _("Send from:");

   ?>
            <br />
            <select name="identity">

      <?php foreach ($idents as $i => $formatted_address) { ?>

              <option value="<?php echo $i . '"'; if ($identity == $i) echo ' selected="selected"'; ?>>
      <?php echo $formatted_address; ?>
              </option>

      <?php } ?>

            </select>
            <br />
            <br />

   <?php } else { ?>

            <input type="hidden" name="identity" value="0" />

   <?php } echo _("Subject:"); ?>

            <br />
            <input type="text" size="40" name="vacation_subject" value="<?php echo $vacation_subject; ?>" />
            <br />
            <br />
            <?php echo _("Message:"); ?>
            <br /> 
            <textarea name="vacation_message" rows="8" cols="50"><?php echo $vacation_message; ?></textarea>
            <br />
            <br />

<?php } else { ?>

            <input type="hidden" name="do_autoreply" value="0" />
            <input type="hidden" name="identity" value="0" />
            <input type="hidden" name="vacation_subject" value="" />
            <input type="hidden" name="vacation_message" value="" />

<?php } if ($maintain_forwarding) { ?>

            <input type="checkbox" value="1" id="do_forward" name="do_forward"<?php if ($do_forward) echo ' checked="checked"'; ?> />
            <label for="do_forward"><?php echo _("Forward incoming messages to addresses listed below"); ?></label>
            <br />
            <br />
            <?php echo _("Forwarding email addresses (one address per line):"); ?>
            <br />
            <textarea name="forward_addresses" rows="8" cols="50"><?php echo $forward_addresses; ?></textarea>
            <br />
            <br />

<?php } if ($maintain_forwarding || ($maintain_autoresponder && $allow_black_hole)) { ?>

            <input type="checkbox" value="1" id="no_local_delivery" name="no_local_delivery"<?php if ($no_local_delivery) echo ' checked="checked"'; ?> />
            <label for="no_local_delivery"><?php if ($maintain_autoresponder) /* ----- OLD TEXT: echo _("No local delivery; forward and/or auto-reply only"); ----- */ echo _("Keep copies of incoming messages in this account") . '<br />' . (!$allow_black_hole ? _("(must have at least one forwarding address to disable)") : ($allow_black_hole == 1 ? _("(must have at least one forwarding address or an active auto-reply to disable)") : '')); else /* ----- OLD TEXT: echo _("No local delivery; forward only"); ----- */ echo _("Keep copies of incoming messages in this account") . '<br />' . ($allow_black_hole < 2 ? _("(must have at least one forwarding address to disable)") : ''); ?></label>
            <br />
            <br />

<?php } ?>

            <input type="hidden" name="vac_action" value="CHANGE_VACATION_SETTINGS" />
            <input type="submit" value="<?php echo _("Submit"); ?>" />
          </p>
        </form>
      </td>
    </tr>
  </table>
</center>


