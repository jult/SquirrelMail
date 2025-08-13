<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

               $body_a = sqimap_run_command($imapConnection, "FETCH $id RFC822",true, $response, $readmessage, $uid_support);
               if ($response == 'OK') {
                  $c++;
                  $seen = $msgs[$k]['FLAG_SEEN'];
                  if (!$seen)
                     sqimap_toggle_flag($imapConnection, $id, '\\Seen',false,true);
                  $email = $msgs[$k]['FROM'];
                  if (strpos($email, '<') !== false)
                     $email = substr($email, strpos($email, '<')+1, strpos($email, '>') - strpos($email, '<')-1);
                  $subject = $msgs[$k]['SUBJECT'];
                  $subject = decodeHeader($subject);
                  $subject = str_replace('&nbsp;',' ',$subject);
                  $subject = archive_replace_str($subject,'');
                  $timestamp = $msgs[$k]['TIME_STAMP'];
                  $date = date('m-j-Y', $timestamp);
                  $cb = count($body_a);
                  if ($archiveattachments == 0) {
                     $boundry = array();
                     for ($v = 0; $v < $cb; $v++) {
                        if (count($boundry)) {
                           if (in_array(trim($body_a[$v]), $boundry)) {
                              if (!strpos($body_a[$v+1]," text/") && !strpos($body_a[$v+1]," multipart/alternative")) {
                                 $q = 1;
                                 $body_a[$v] = '';
                                 while ($v < $cb && $q) {
                                    $v++;
                                    if (in_array(trim($body_a[$v]), $boundry)) {
                                       $q = 0;
                                       $v--;
                                    }
                                    if (in_array(trim($body_a[$v]), $eboundry))
                                       $q = 0;
                                    if ($q != 0)
                                       unset($body_a[$v]);
                                 }
                              }
                           }
                        }else{
                           if (strpos($body_a[$v], 'boundary="') !== false) {
                              $x = strpos(strtolower($body_a[$v]),'boundary="')+10;
                              if ($x == 11) $x = 10;
                              $x2 = strpos($body_a[$v],'"',$x+1);
                              $b = "--" . trim(substr(trim($body_a[$v]),$x,-1));
                              $boundry[] = $b;
                              $eboundry[] = $b . "--";
                           }
                        }
                     }
                  }

                  if (trim($body_a[$cb-1]) == ')')
                     unset($body_a[$cb-1]);
                  array_shift($body_a);
                  $body = implode('', $body_a);
                  $body .= "\r\n";
                  $suffix = '.eml';
                  $name = archive_names ($archivefilenames, $email, $date, $c, $subject);
                  $max = $maxarray[$archivetype];
                  if (strlen($name) > $max && $max > 0) $name = substr($name,0,$max);
                  $increment = checkincrement(strtolower($name . $suffix));
                  $zipfile -> addFile($body, $name . $increment .  $suffix, $timestamp);
               }
?>