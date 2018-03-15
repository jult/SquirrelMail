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
                  $body = implode('', $body_a);
                  srand((double) microtime() * 1000000);
                  $filename = $timestamp . '.' . rand(1,100000) . '.' . 'localhost';
                  $dir = 'cur';
                  if (!$seen)
                     $dir = 'new';
                  else
                     $filename .= ':2,S';
                  $zipfile -> addFile($body, "$dir/$filename", $timestamp);
               }
?>