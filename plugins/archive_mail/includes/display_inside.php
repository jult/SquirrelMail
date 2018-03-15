<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

      define('SM_PATH','../../../');

      /* SquirrelMail required files. */

      require_once(SM_PATH . 'include/validate.php');
      require_once(SM_PATH . 'functions/page_header.php');
      require_once(SM_PATH . 'functions/imap.php');
      require_once(SM_PATH . 'include/load_prefs.php');

      include_once(SM_PATH . 'plugins/archive_mail/includes/display_save.php');

      displayPageHeader($color, 'None');

      global $username, $data_dir;

      // Set domain
      bindtextdomain ('archive_mail', SM_PATH . 'locale');
      textdomain ('archive_mail');

      $filename = getHashedFile($username, $data_dir, '');
      $archivefilenames = getPref($filename, $username, 'archivefilenames');
      $archiveattachments = getPref($filename, $username, 'archiveattachments');
      $archivetype = getPref($filename, $username, 'archivetype');
      $archiveent = getPref($filename, $username, 'archiveent');
      echo "<br><br><center><table><form method=post action=\"" . SM_PATH . "plugins/archive_mail/includes/display_inside.php\">\n";
      echo "<tr><td align=right valign=top>\n";
      echo _("Format Emails as") . ":</td>\n";
      echo "<td><select name=archiveent>\n";

      echo "<option value=1";
      if ($archiveent == 1)
         echo " SELECTED";
      echo ">" . _("EML Messages") . "</option>\n";

      echo "<option value=2";
      if ($archiveent == 2)
         echo " SELECTED";
      echo ">" . _("Mbox") . "</option>\n";

      echo "<option value=3";
      if ($archiveent == 3)
         echo " SELECTED";
      echo ">" . _("Maildir") . "</option>\n";
/*
      echo "<option value=4";
      if ($archiveent == 4)
         echo " SELECTED";
      echo ">" . _("PST") . "</option>\n";
*/
      echo "<option value=0";
      if ($archiveent == 0)
         echo " SELECTED";
      echo ">" . _("Separate Entities") . "</option>\n";

      echo "</select><br>\n";
      echo "</td></tr>\n";
      echo "<tr><td align=right valign=top>\n";
      echo _("Save Attachments") . ":</td>\n";
      echo "<td><input type=radio name=\"archiveattachments\" value=1";
      if ($archiveattachments == 1)
         echo " checked";
      echo ">&nbsp;" . _("Yes") . "&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=\"archiveattachments\" value=0";
      if ($archiveattachments == 0)
         echo " checked";
      echo ">&nbsp;" . _("No") . "</td>\n";
      echo "</tr>\n";

      echo "<tr><td colspan=2><center><input type=submit name=submit value='" . _("Save") . "'></form><center></td></tr>\n";
      echo "<tr><td colspan=2><hr width='100%'></td></tr>\n";

      echo "<tr><td colspan=2><center><b>";

      switch ($archiveent) {
         case 0:
            print _("Separate Entities Options");
            break;
         case 1:
            print _("EML Messages Options");
            break;
         case 2:
            print _("Mbox Options");
            break;
         case 3:
            print _("Maildir Options");
            break;
      }
      echo "</b></center><br></td></tr>\n";

      echo "<tr><td align=right valign=top><form method=post action=\"" . SM_PATH . "plugins/archive_mail/includes/display_inside.php\">\n";
      echo _("Compress Type") . ":</td>\n";
      echo "<td><select name=archivetype>\n";

      echo "<option value=3";
      if ($archivetype == 3)
         echo " SELECTED";
      echo ">" . _("Tar") . "</option>\n";

      echo "<option value=1";
      if ($archivetype == 1)
         echo " SELECTED";
      echo ">" . _("Tar.GZ") . "</option>\n";

      if ($archiveent == 2) {
         echo "<option value=2";
         if ($archivetype == 2)
            echo " SELECTED";
         echo ">" . _("Text") . "</option>\n";
      }

      echo "<option value=0";
      if ($archivetype == 0)
         echo " SELECTED";
      echo ">" . _("Zip") . "</option>\n";

      echo "</select><br>\n";
      echo "</td></tr>\n";
      if ($archiveent < 2) {
         echo "<tr><td align=right valign=top>\n";
         echo _("Email Filename") . ":</td>\n";
         echo "<td><select name=archivefilenames>\n";

         echo "<option value=0";
         if ($archivefilenames == 0)
            echo " SELECTED";
         echo ">" . _("Numbered") . "</option>\n";

         echo "<option value=1";
         if ($archivefilenames == 1)
            echo " SELECTED";
         echo ">" . _("Date") . "</option>\n";

         echo "<option value=2";
         if ($archivefilenames == 2)
            echo " SELECTED";
         echo ">" . _("Date") . " - " . _("Email") . "</option>\n";

         echo "<option value=7";
         if ($archivefilenames == 7)
            echo " SELECTED";
         echo ">" . _("Date") . " - " . _("Subject") . "</option>\n";

         echo "<option value=3";
         if ($archivefilenames == 3)
            echo " SELECTED";
         echo ">" . _("Email") . "</option>\n";

         echo "<option value=4";
         if ($archivefilenames == 4)
            echo " SELECTED";
         echo ">" . _("Email") . " - " . _("Date") . "</option>\n";

         echo "<option value=6";
         if ($archivefilenames == 6)
            echo " SELECTED";
         echo ">" . _("Email") . " - " . _("Subject") . "</option>\n";

         echo "<option value=5";
         if ($archivefilenames == 5)
            echo " SELECTED";
         echo ">" . _("Subject") . "</option>\n";

         echo "<option value=8";
         if ($archivefilenames == 8)
            echo " SELECTED";
         echo ">" . _("Subject") . " - " . _("Email") . "</option>\n";

         echo "<option value=9";
         if ($archivefilenames == 9)
            echo " SELECTED";
         echo ">" . _("Subject") . " - " . _("Date") . "</option>\n";

         echo "</select><br>\n";
         echo "</td></tr>\n";
      }

      echo "<tr><td colspan=2><center><input type=submit name=submit value='" . _("Save") . "'></form></center></td></tr>\n";
      echo '</table></center>';

      // Unset domain
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');

      function getByteSize($ini_size) {
         if(!$ini_size) return FALSE;
         $ini_size = trim($ini_size);
         switch(strtoupper(substr($ini_size, -1))) {
            case 'G':
               $bytesize = 1073741824;
               break;
            case 'M':
               $bytesize = 1048576;
               break;
            case 'K':
               $bytesize = 1024;
               break;
            default:
               $bytesize = 1;
         }
         $bytesize *= (int)substr($ini_size, 0, -1);
         return $bytesize;
      }


?>