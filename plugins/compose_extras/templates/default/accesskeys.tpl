<?php

/**
  * accesskeys.tpl
  *
  * Template for outputting JavaScript that assigns accesskey
  * attributes to certain elements on the compose screen.
  *
  * The following variables are available in this template:
  *
  * string $submit_button_name The name of the send button
  * string $accesskey_compose_identity
  * string $accesskey_compose_priority
  * string $accesskey_compose_on_read
  * string $accesskey_compose_on_delivery
  * string $accesskey_compose_to
  * string $accesskey_compose_cc
  * string $accesskey_compose_bcc
  * string $accesskey_compose_subject
  * string $accesskey_compose_body
  * string $accesskey_compose_signature
  * string $accesskey_compose_addresses
  * string $accesskey_compose_save_draft
  * string $accesskey_compose_send
  * string $accesskey_compose_attach_browse
  * string $accesskey_compose_attach
  * string $accesskey_compose_delete_attach
  *
  * @copyright &copy; 1999-2012 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


?>
<script language="JavaScript" type="text/javascript">
<!--
    <?php if ($accesskey_compose_identity != 'NONE')
             echo "if (typeof(document.compose.identity) != 'undefined') document.compose.identity.accessKey='$accesskey_compose_identity';"; ?>

    <?php if ($accesskey_compose_to != 'NONE')
             echo "document.compose.send_to.accessKey='$accesskey_compose_to';"; ?>

    <?php if ($accesskey_compose_cc != 'NONE')
             echo "document.compose.send_to_cc.accessKey='$accesskey_compose_cc';"; ?>

    <?php if ($accesskey_compose_bcc != 'NONE')
             echo "document.compose.send_to_bcc.accessKey='$accesskey_compose_bcc';"; ?>

    <?php if ($accesskey_compose_subject != 'NONE')
             echo "document.compose.subject.accessKey='$accesskey_compose_subject';"; ?>

    <?php if ($accesskey_compose_body != 'NONE')
             echo "document.compose.body.accessKey='$accesskey_compose_body';"; ?>

    <?php if ($accesskey_compose_priority != 'NONE')
             echo "document.compose.mailprio.accessKey='$accesskey_compose_priority';"; ?>

    <?php if ($accesskey_compose_on_read != 'NONE')
             echo "document.compose.request_mdn.accessKey='$accesskey_compose_on_read';"; ?>

    <?php if ($accesskey_compose_on_delivery != 'NONE')
             echo "document.compose.request_dr.accessKey='$accesskey_compose_on_delivery';"; ?>

    <?php if ($accesskey_compose_addresses != 'NONE')
             echo "if (typeof(document.compose.html_addr_search) != 'undefined') document.compose.html_addr_search.accessKey='$accesskey_compose_addresses';"; ?>

    <?php if ($accesskey_compose_signature != 'NONE')
             echo "document.compose.sigappend.accessKey='$accesskey_compose_signature';"; ?>

    <?php if ($accesskey_compose_save_draft != 'NONE')
             echo "document.compose.draft.accessKey='$accesskey_compose_save_draft';"; ?>

    <?php if ($accesskey_compose_send != 'NONE')
             echo "document.compose.$submit_button_name.accessKey='$accesskey_compose_send';"; ?>

    <?php if ($accesskey_compose_attach_browse != 'NONE')
             echo "document.compose.attachfile.accessKey='$accesskey_compose_attach_browse';"; ?>

    <?php if ($accesskey_compose_attach != 'NONE')
             echo "document.compose.attach.accessKey='$accesskey_compose_attach';"; ?>

    <?php if ($accesskey_compose_delete_attach != 'NONE')
             echo "if (typeof(document.compose.do_delete) != 'undefined') document.compose.do_delete.accessKey='$accesskey_compose_delete_attach';"; ?>

// -->
</script>

