<?php

/********************************************************************************
 * function generate_seed
 * Description:  Generates a new seed for MD5 hashes
 *******************************************************************************/

function generate_seed() {

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$pwdlen = 8;

	mt_srand((double)microtime()*1000000*getmypid()); 

	$password='';
	while(strlen($password)<$pwdlen)
		$password.=substr($chars,(mt_rand()%strlen($chars)),1);
	return $password;
}

?>
