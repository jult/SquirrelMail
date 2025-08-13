<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

               $message2 = sqimap_get_message($imapConnection, $id, $mailbox);
               $count = count($message2->entities);
               if ($count == 0)
                  $count = 1;
               $seen = $msgs[$k]['FLAG_SEEN'];
               $date_from_msg = decodeHeader($message2->rfc822_header->date);

	       // Wrap time stamp settings
	       bindtextdomain ('archive_mail', SM_PATH . 'locale');
	       textdomain ('archive_mail');
	       /* Translators: this is string used in date strings.
	          See http://www.php.net/functions.date */
	       $date_stamp=_("m/d/Y g:i a");
	       /* $date_stamp has to be sanitized against slashes */
	       $date_stamp=str_replace(array("/","\\"),array("_","_"),$date_stamp);
	       bindtextdomain('squirrelmail', SM_PATH . 'locale');
	       textdomain('squirrelmail');
						     
               $date = date_intl($date_stamp,$date_from_msg);
               $email2 = $msgs[$k]['FROM'];
               if (strpos($email2, '<') !== false)
                  $email2 = substr($email2, strpos($email2, '<')+1, strlen($email2) - strpos($email2, '<')-2);
               $subject2 = $msgs[$k]['SUBJECT'];
               $email = archive_replace_str($email2);
               $timestamp = $msgs[$k]['TIME_STAMP'];
               $subject2 = decodeHeader($subject2);
               $subject2 = str_replace('&nbsp;',' ',$subject2);
               $subject = archive_replace_str($subject2);
               if ($subject == '')
                  $subject = _("No Subject");
               $to = decodeHeader($message2->rfc822_header->getAddr_s('to'));
               for ($b = 1; $b < $count + 1; $b++) {
                  $filename = '';
                  $message = $message2->getEntity($b);
                  $header = $message->rfc822_header;
                  $body = mime_fetch_body ($imapConnection, $id, $b);
                  $body = decodeBody($body, $message->header->encoding);
                  $filename = $message->header->getParameter('filename');
                  if (!$filename)
                     $filename = $message->header->getParameter('name');
                  $type0 = $message->type0;
                  $type1 = $message->type1;
                  if (isset($override_type0))
                     $type0 = $override_type0;
                  if (isset($override_type1))
                     $type1 = $override_type1;
                  if (($filename  || ($type1 == 'rfc822' && $type0 == 'message')) && $archiveattachments == 0) {
                     // We don't want to download attachments
                  }else{
                     $suffix = '';
                     $isattachment = false;
                     $filename = archive_replace_str($filename);
                     if ($filename)
                     $isattachment = true;
                     if (strlen($filename) < 1) {
                        if ($type1 == 'plain' && $type0 == 'text') {
                           $suffix = '.txt';
                        } else if ($type1 == 'richtext' && $type0 == 'text') {
                           $suffix = '.rtf';
                        } else if ($type1 == 'postscript' && $type0 == 'application') {
                           $suffix = '.ps';
                        } else if ($type1 == 'rfc822' && $type0 == 'message') {
                           $suffix = '.eml';
                        } else {
                           if ($type1 != '')
                              $suffix = ".$type1";
                           else
                              $suffix = '';
                        }
                        if ($filename == '') {
                           if ($archivefilenames < 1)
                              $archivefilenames = 0;
                        }
			$name = archive_names ($archivefilenames, $email, $date, $c, $subject);
                        $max = $maxarray[$archivetype];
                        if (strlen($name) > $max && $max > 0) $name=substr($name,0,$max);
                        $increment = checkincrement(strtolower($name . $suffix));
                        $filename = $name . $increment;
                     }
                     $filename = $filename . $suffix;
                  $temp = '';
		  
		  // Set domain
		  bindtextdomain ('archive_mail', SM_PATH . 'locale');
		  textdomain ('archive_mail');
		  
                  if (!$isattachment && ($type0 == 'text' || ($type0 == '' && $type1 == ''))) {
                     if ($type1 == 'plain' || $type1 == '') {
		        // calculate max line size
		        $len=strlen(_("Subject"));
			if ( $len < strlen(_("From")) ) $len=strlen(_("From"));
			if ( $len < strlen(_("To"))   ) $len=strlen(_("To"));
			if ( $len < strlen(_("Date")) ) $len=strlen(_("Date"));
			
                        $temp = str_pad(_("Subject"),$len," ",STR_PAD_LEFT) . ": $subject2\r\n" .
                        str_pad(_("From"),$len," ",STR_PAD_LEFT) . ": $email2\r\n" .
                        str_pad(_("To"),$len," ",STR_PAD_LEFT) . ": $to\r\n" .
                        str_pad(_("Date"),$len," ",STR_PAD_LEFT) . ": $date\r\n\r\n";
                     } elseif ($type1 == 'html') {
                        $temp = "<table><tr><th align=right>" . _("Subject").
                            ":</th><td>$subject2" .
                            "</td></tr>\r\n<tr><th align=right>" . _("From").
                            ":</th><td>$email2" .
                            "</td></tr>\r\n<tr><th align=right>" . _("To").
                            ":</th><td>$to" .
                            "</td></tr>\r\n<tr><th align=right>" . _("Date").
                            ":</th><td>$date" .
                            "</td></tr>\r\n</table>\n<hr>\r\n";
                     }
                  }
		  
		  // Unset domain
		  bindtextdomain('squirrelmail', SM_PATH . 'locale');
                  textdomain('squirrelmail');
		  
                  $body = $temp . $body;
                  $zipfile -> addFile($body, $filename,$timestamp);
               }
               if (!$seen)
                  sqimap_toggle_flag($imapConnection, $id, '\\Seen',false,true);
            }
?>