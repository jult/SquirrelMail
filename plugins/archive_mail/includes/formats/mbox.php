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
                  $email = trim(str_replace(array(' ',chr(9)), '-', $email));
                  $timestamp = $msgs[$k]['TIME_STAMP'];
                  if (date('d',$timestamp) < 10)
                     $mdate = date('D M  j H:i:s Y', $timestamp);
                  else
                     $mdate = date('D M d H:i:s Y', $timestamp);
                  if ($archiveattachments == 0) {
                     $boundry = array();
                     $cb = count($body_a);
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
                  array_shift($body_a);
                  $body_a[0] = "From $email $mdate\r\n" . $body_a[0];
                  if ($c > 1)
                     $body_a[0] = "\r\n" . $body_a[0];
                  $cb = count($body_a);
                  for ($v = 1; $v < $cb; $v++) {
                     if (substr(str_replace('>', '', $body_a[$v]),0,5) == "From ")
                        $body_a[$v] = ">" . $body_a[$v];
                  }
                  $mbox .= implode('', $body_a);
               }
?>