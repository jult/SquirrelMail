<?php

/**
  * mark_read_options.tpl
  *
  * Template for showing user-configurable options on the folder page
  * for the Mark Read plugin.
  *
  * The following variables are available in this template:
  *
  * boolean $show_read_link_allow_override     Whether or not users are allowed
  *                                            to change the folders that read
  *                                            links are shown next to.
  * boolean $confirm_read_link                 Whether or not read link actions
  *                                            are to be confirmed first.
  * boolean $show_read_button_allow_override   Whether or not users are allowed
  *                                            to change the folders that read
  *                                            buttons are shown in.
  * boolean $confirm_read_button               Whether or not read button actions
  *                                            are to be confirmed first.
  * boolean $show_unread_link_allow_override   Whether or not users are allowed
  *                                            to change the folders that unread
  *                                            links are shown next to.
  * boolean $confirm_unread_link               Whether or not unread link actions
  *                                            are to be confirmed first.
  * boolean $show_unread_button_allow_override Whether or not users are allowed
  *                                            to change the folders that unread
  *                                            buttons are shown in.
  * boolean $confirm_unread_button             Whether or not unread button actions
  *                                            are to be confirmed first.
  * array   $mr_folder_listing   An array where each entry is a seven-element
  *                              array keyed by "displayable", "option_value",
  *                              "show_unread_link", "show_read_link",
  *                              "show_read_button", "show_unread_button"
  *                              and "is_special", where the last five are boolean
  *                              values.
  * boolean $javascript_on       Whether or not JavaScript is supported/enabled.
FIXME: sync the $color array with 1.5.2 if it is no longer available(?)
  * array   $color               The SquirrelMail colors array.
  *
  * Copyright (c) 2004-2005 Dave Kliczbor <maligree@gmx.de>
  * Copyright (c) 2003-2009 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2004 Ferdie Ferdsen <ferdie.ferdsen@despam.de>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage mark_read
  *
  */


// retrieve the template vars
//
extract($t);


if (($show_read_link_allow_override || $show_unread_link_allow_override)
 && ($show_read_button_allow_override || $show_unread_button_allow_override))
   $title = _("Mark-As-Read Links/Buttons");
else if ($show_read_link_allow_override || $show_unread_link_allow_override)
   $title = _("Mark-As-Read Links");
else if ($show_read_button_allow_override || $show_unread_button_allow_override)
   $title = _("Mark-As-Read Buttons");
else
   $title = 'Mark Read CONFIGURATION ERROR';


$form_index = 1;


?>
<form style="display:inline; margin:0" method="post" name="mark_read_links_form">
  <input type="hidden" value="1" name="mark_read_form">
    <table width="70%" cols="1" align="center" cellpadding="4" cellspacing="0" border="0">
      <?php if (!check_sm_version(1, 4, 10)) echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n"; ?>
      <tr>
        <td bgcolor="<?php echo $color[9]; ?>" align="center"><strong><?php echo $title; ?></strong></td>
      </tr>
      <tr>
        <td bgcolor="<?php echo $color[0]; ?>" align="center"><?php



// where to show read links?
//
if ($show_read_link_allow_override) { ?><table width="100%" border="0">
            <tr>
              <td width="60%" align="right">
                <select name="mark_read_show_read_link[]" multiple size="8">
                  <?php foreach ($mr_folder_listing as $folder_info) { ?>
                    <option value="<?php echo $folder_info['option_value']; if ($folder_info['show_read_link']) echo '" selected="selected'; ?>"><?php echo $folder_info['displayable']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td valign="bottom">
                <table cellpadding="0" cellspacing="5" border="0">
                  <tr>
                    <td><?php echo _("Read links next to the folder name mark all messages in the folders as having been read.") . '<br /><br />'; if ($javascript_on) { ?>
                <input type="checkbox" name="confirm_read_link" id="confirm_read_link" value="1"<?php if ($confirm_read_link) echo ' checked="checked"'; ?> /><label for="confirm_read_link">
                <?php echo _("Confirm read"); /* "elements[x] below because javascript can't reference element names with brackets in them */ ?>
                </label>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; ?>]; for (i = 0; i < list.length; i++) if (<?php
                   $first = TRUE;
                   foreach ($mr_folder_listing as $folder_info)
                   {
                      if (!$folder_info['is_special']) continue;
                      if (!$first) echo ' || ';
                      else $first = FALSE;
                      echo 'list.options[i].value==\'' . $folder_info['option_value'] . '\'';
                   }
                ?>) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle Special Folders"); ?></a>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; $form_index += 2; ?>]; for (i = 0; i < list.length; i++) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle All"); ?></a>
              <?php } ?></td></tr></table></td>
            </tr>
          </table>


<?php }


// where to show unread links?
//
if ($show_unread_link_allow_override) {

   if ($show_read_link_allow_override)
      echo '<hr width="75%" />';

        ?><table width="100%" border="0">
            <tr>
              <td width="60%" align="right">
                <select name="mark_read_show_unread_link[]" multiple size="8">
                  <?php foreach ($mr_folder_listing as $folder_info) { ?>
                    <option value="<?php echo $folder_info['option_value']; if ($folder_info['show_unread_link']) echo '" selected="selected'; ?>"><?php echo $folder_info['displayable']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td valign="bottom">
                <table cellpadding="0" cellspacing="5" border="0">
                  <tr>
                    <td><?php echo _("Unread links next to the folder name mark all messages in the folders as having been unread.") . '<br /><br />'; if ($javascript_on) { ?>
                <input type="checkbox" name="confirm_unread_link" id="confirm_unread_link" value="1"<?php if ($confirm_unread_link) echo ' checked="checked"'; ?> /><label for="confirm_unread_link">
                <?php echo _("Confirm unread"); /* "elements[x] below because javascript can't reference element names with brackets in them */ ?>
                </label>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; ?>]; for (i = 0; i < list.length; i++) if (<?php
                   $first = TRUE;
                   foreach ($mr_folder_listing as $folder_info)
                   {
                      if (!$folder_info['is_special']) continue;
                      if (!$first) echo ' || ';
                      else $first = FALSE;
                      echo 'list.options[i].value==\'' . $folder_info['option_value'] . '\'';
                   }
                ?>) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle Special Folders"); ?></a>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; $form_index += 2; ?>]; for (i = 0; i < list.length; i++) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle All"); ?></a>
              <?php } ?></td></tr></table></td>
            </tr>
          </table>


<?php }


// where to show read buttons?
//
if ($show_read_button_allow_override) {

   if ($show_read_link_allow_override || $show_unread_link_allow_override)
      echo '<hr width="75%" />';

        ?><table width="100%" border="0">
            <tr>
              <td width="60%" align="right">
                <select name="mark_read_show_read_button[]" multiple size="8">
                  <?php foreach ($mr_folder_listing as $folder_info) { ?>
                    <option value="<?php echo $folder_info['option_value']; if ($folder_info['show_read_button']) echo '" selected="selected'; ?>"><?php echo $folder_info['displayable']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td valign="bottom">
                <table cellpadding="0" cellspacing="5" border="0">
                  <tr>
                    <td><?php echo _("Read buttons on the message list page mark all messages in the folder as having been read.") . '<br /><br />'; if ($javascript_on) { ?>
                <input type="checkbox" name="confirm_read_button" id="confirm_read_button" value="1"<?php if ($confirm_read_button) echo ' checked="checked"'; ?> /><label for="confirm_read_button">
                <?php echo _("Confirm read"); /* "elements[x] below because javascript can't reference element names with brackets in them */ ?>
                </label>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; ?>]; for (i = 0; i < list.length; i++) if (<?php
                   $first = TRUE;
                   foreach ($mr_folder_listing as $folder_info)
                   {
                      if (!$folder_info['is_special']) continue;
                      if (!$first) echo ' || ';
                      else $first = FALSE;
                      echo 'list.options[i].value==\'' . $folder_info['option_value'] . '\'';
                   }
                ?>) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle Special Folders"); ?></a>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; $form_index += 2; ?>]; for (i = 0; i < list.length; i++) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle All"); ?></a>
              <?php } ?></td></tr></table></td>
            </tr>
          </table>


<?php }


// where to show unread buttons?
//
if ($show_unread_button_allow_override) {

   if ($show_read_link_allow_override || $show_unread_link_allow_override
    || $show_read_button_allow_override)
      echo '<hr width="75%" />';

        ?><table width="100%" border="0">
            <tr>
              <td width="60%" align="right">
                <select name="mark_read_show_unread_button[]" multiple size="8">
                  <?php foreach ($mr_folder_listing as $folder_info) { ?>
                    <option value="<?php echo $folder_info['option_value']; if ($folder_info['show_unread_button']) echo '" selected="selected'; ?>"><?php echo $folder_info['displayable']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td valign="bottom">
                <table cellpadding="0" cellspacing="5" border="0">
                  <tr>
                    <td><?php echo _("Unread buttons on the message list page mark all messages in the folder as having been unread.") . '<br /><br />'; if ($javascript_on) { ?>
                <input type="checkbox" name="confirm_unread_button" id="confirm_unread_button" value="1"<?php if ($confirm_unread_button) echo ' checked="checked"'; ?> /><label for="confirm_unread_button">
                <?php echo _("Confirm unread"); /* "elements[x] below because javascript can't reference element names with brackets in them */ ?>
                </label>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; ?>]; for (i = 0; i < list.length; i++) if (<?php
                   $first = TRUE;
                   foreach ($mr_folder_listing as $folder_info)
                   {
                      if (!$folder_info['is_special']) continue;
                      if (!$first) echo ' || ';
                      else $first = FALSE;
                      echo 'list.options[i].value==\'' . $folder_info['option_value'] . '\'';
                   }
                ?>) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle Special Folders"); ?></a>
                <br />
                <br />
                <a href="#" onClick="list = document.mark_read_links_form.elements[<?php echo $form_index; $form_index += 2; ?>]; for (i = 0; i < list.length; i++) { list.options[i].selected = !list.options[i].selected; } return false;"><?php echo _("Toggle All"); ?></a>
              <?php } ?></td></tr></table></td>
            </tr>
          </table>


<?php } ?>

        </td>
      </tr>
      <tr>
        <td bgcolor="<?php echo $color[0]; ?>" align="right"><input type="submit" value="<?php echo _("Save"); ?>"></td>
      </tr>
      <tr><td bgcolor="<?php echo $color[4]; ?>">&nbsp;</td></tr>
    </table>
  </form>

