<?php 

/*
###########################################################
#
# Written in PHP by Jure Koren <jure@kiss.uni-lj.si>, based
# on a python module by Michael Wallace.
#
# Latest version should reside on
# http://limonez.net/~jure/php/md5crypt.phps
#
# Usage: string md5crypt(string $password, string $salt)
# returns a FreeBSD compatible MD5 password hash string
#
# Michael's note:
#
###########################################################
#
# 0423.2000 by michal wallace http://www.sabren.com/
# based on perl's Crypt::PasswdMD5 by Luis Munoz (lem@cantv.net)
# based on /usr/src/libcrypt/crypt.c from FreeBSD 2.2.5-RELEASE
#
# MANY THANKS TO
#
#  Carey Evans - http://home.clear.net.nz/pages/c.evans/
#  Dennis Marti - http://users.starpower.net/marti1/
#
#  For the patches that got this thing working!
#
###########################################################
*/

function hx2bin($str) {

  $len = strlen($str);
  $nstr = "";
  for ($i=0;$i<$len;$i+=2) {
    $num = sscanf(substr($str,$i,2), "%x");
    $nstr.=chr($num[0]);
  }
  return $nstr;
}

function to64($v, $n) {
  $ITOA64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

  $ret = "";
  while (($n - 1) >= 0) {
    $n--;
    $ret .= $ITOA64[$v & 0x3f];
    $v = $v >> 6;
  }
  
  return $ret;

}

function md5crypt($pw, $salt, $magic="") {

  $MAGIC = "$1$";

  if ($magic == "") $magic = $MAGIC;
  
  $slist = explode("$", $salt);
  if ($slist[0] == "1") $salt = $slist[1];
  $salt = substr($salt, 0, 8);
  
  $ctx = $pw . $magic . $salt;
  
  $final = hx2bin(md5($pw . $salt . $pw));
  
  for ($i=strlen($pw); $i>0; $i-=16) {
    if ($i > 16)
      $ctx .= substr($final,0,16);
    else
      $ctx .= substr($final,0,$i);
  }
  
  $i = strlen($pw);
  while ($i > 0) {
    if ($i & 1) $ctx .= chr(0);
    else $ctx .= $pw[0];
    $i = $i >> 1;
  }
  
  $final = hx2bin(md5($ctx));

  # this is really stupid and takes too long

  for ($i=0;$i<1000;$i++) {
    $ctx1 = "";
    if ($i & 1) $ctx1 .= $pw;
    else $ctx1 .= substr($final,0,16);
    if ($i % 3) $ctx1 .= $salt;
    if ($i % 7) $ctx1 .= $pw;
    if ($i & 1) $ctx1 .= substr($final,0,16);
    else $ctx1 .= $pw;
    $final = hx2bin(md5($ctx1));
  }
  
  $passwd = "";
  
  $passwd .= to64( ( (ord($final[0]) << 16) | (ord($final[6]) << 8) | (ord($final[12])) ), 4);
  $passwd .= to64( ( (ord($final[1]) << 16) | (ord($final[7]) << 8) | (ord($final[13])) ), 4);
  $passwd .= to64( ( (ord($final[2]) << 16) | (ord($final[8]) << 8) | (ord($final[14])) ), 4);
  $passwd .= to64( ( (ord($final[3]) << 16) | (ord($final[9]) << 8) | (ord($final[15])) ), 4);
  $passwd .= to64( ( (ord($final[4]) << 16) | (ord($final[10]) << 8) | (ord($final[5])) ), 4);
  $passwd .= to64( ord($final[11]), 2);

  return "$magic$salt\$$passwd";

}

?>
