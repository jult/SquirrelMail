<?php

/**
 * imap_general.php
 *
 * This implements all functions that do general IMAP functions.
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: imap_general.php 14813 2019-04-06 18:32:29Z pdontthink $
 * @package squirrelmail
 * @subpackage imap
 */

/** Includes.. */
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'functions/auth.php');


/**
 * Generates a new session ID by incrementing the last one used;
 * this ensures that each command has a unique ID.
 * @param bool unique_id
 * @return string IMAP session id of the form 'A000'.
 */
function sqimap_session_id($unique_id = FALSE) {
    static $sqimap_session_id = 1;

    if (!$unique_id) {
        return( sprintf("A%03d", $sqimap_session_id++) );
    } else {
        return( sprintf("A%03d", $sqimap_session_id++) . ' UID' );
    }
}

/**
 * Both send a command and accept the result from the command.
 * This is to allow proper session number handling.
 */
function sqimap_run_command_list ($imap_stream, $query, $handle_errors, &$response, &$message, $unique_id = false) {
    if ($imap_stream) {
        $sid = sqimap_session_id($unique_id);
        fputs ($imap_stream, $sid . ' ' . $query . "\r\n");
        $read = sqimap_read_data_list ($imap_stream, $sid, $handle_errors, $response, $message, $query );
        return $read;
    } else {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
                _("ERROR: No available IMAP stream.") .
                "</b></font>\n";
        error_box($string,$color);
        return false;
    }
}

function sqimap_run_command ($imap_stream, $query, $handle_errors, &$response,
                            &$message, $unique_id = false,$filter=false,
                             $outputstream=false,$no_return=false) {
    if ($imap_stream) {
        $sid = sqimap_session_id($unique_id);
        fputs ($imap_stream, $sid . ' ' . $query . "\r\n");
        $read = sqimap_read_data ($imap_stream, $sid, $handle_errors, $response,
                                  $message, $query,$filter,$outputstream,$no_return);
        return $read;
    } else {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
                _("ERROR: No available IMAP stream.") .
                "</b></font>\n";
        error_box($string,$color);
        return false;
    }
}

function sqimap_run_literal_command($imap_stream, $query, $handle_errors, &$response, &$message, $unique_id = false) {
    if ($imap_stream) {
        $sid = sqimap_session_id($unique_id);
        $command = sprintf("%s {%d}\r\n", $query['commands'][0], strlen($query['literal_args'][0]));
        fputs($imap_stream, $sid . ' ' . $command);

        // TODO: Put in error handling here //
        $read = sqimap_read_data($imap_stream, $sid, $handle_errors, $response, $message, $query['commands'][0]);
        
        $i = 0;
        $cnt = count($query['literal_args']);
        while( $i < $cnt ) {
        	if (($cnt > 1) && ($i < ($cnt - 1))) {
				$command = sprintf("%s%s {%d}\r\n", $query['literal_args'][$i], (!empty($query['commands'][$i+1]) ? ' ' . $query['commands'][$i+1] : ''), strlen($query['literal_args'][$i+1]));
			} else {
				$command = sprintf("%s\r\n", $query['literal_args'][$i]);
			}
        	
			fputs($imap_stream, $command);
	        $read = sqimap_read_data($imap_stream, $sid, $handle_errors, $response, $message, $query['commands'][0]);

	        $i++;
	        
        }
        return $read;
    } else {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
                _("ERROR: No available IMAP stream.") .
                "</b></font>\n";
        error_box($string,$color);
        return false;
    }
}


/**
 * Custom fgets function: gets a line from the IMAP server,
 * no matter how big it may be.
 * @param stream imap_stream the stream to read from
 * @return string a line
 */
function sqimap_fgets($imap_stream) {
    $read = '';
    $buffer = 4096;
    $results = '';
    $offset = 0;
    while (strpos($results, "\r\n", $offset) === false) {
        if (!($read = fgets($imap_stream, $buffer))) {
        /* this happens in case of an error */
        /* reset $results because it's useless */
        $results = false;
            break;
        }
        if ( $results != '' ) {
            $offset = strlen($results) - 1;
        }
        $results .= $read;
    }
    return $results;
}

function sqimap_fread($imap_stream,$iSize,$filter=false,
                      $outputstream=false, $no_return=false) {
    if (!$filter || !$outputstream) {
        $iBufferSize = $iSize;
    } else {
        // see php bug 24033. They changed fread behaviour %$^&$%
        $iBufferSize = 7800; // multiple of 78 in case of base64 decoding.
    }
    if ($iSize < $iBufferSize) {
        $iBufferSize = $iSize;
    }

    $iRetrieved = 0;
    $results = '';
    $sRead = $sReadRem = '';
    // NB: fread can also stop at end of a packet on sockets.
    while ($iRetrieved < $iSize) {
        $sRead = fread($imap_stream,$iBufferSize);
        $iLength = strlen($sRead);
        $iRetrieved += $iLength ;
        $iRemaining = $iSize - $iRetrieved;
        if ($iRemaining < $iBufferSize) {
            $iBufferSize = $iRemaining;
        }
        if ($sRead == '') {
            $results = false;
            break;
        }
        if ($sReadRem != '') {
            $sRead = $sReadRem . $sRead;
            $sReadRem = '';
        }

        if ($filter && $sRead != '') {
           // in case the filter is base64 decoding we return a remainder
           $sReadRem = $filter($sRead);
        }
        if ($outputstream && $sRead != '') {
           if (is_resource($outputstream)) {
               fwrite($outputstream,$sRead);
           } else if ($outputstream == 'php://stdout') {
               echo $sRead;
           }
        }
        if ($no_return) {
            $sRead = '';
        } else {
            $results .= $sRead;
        }
    }
    return $results;
}

/**
 * Reads the output from the IMAP stream.  If handle_errors is set to true,
 * this will also handle all errors that are received.  If it is not set,
 * the errors will be sent back through $response and $message.
 */
function sqimap_read_data_list ($imap_stream, $tag_uid, $handle_errors,
          &$response, &$message, $query = '',
           $filter = false, $outputstream = false, $no_return = false) {
    global $color, $squirrelmail_language;
    $read = '';
    $tag_uid_a = explode(' ',trim($tag_uid));
    $tag = $tag_uid_a[0];
    $resultlist = array();
    $data = array();
    $read = sqimap_fgets($imap_stream);
    
    $i = 0;
    while ($read) {
        $char = $read{0};
        switch ($char)
        {
          case '+':
          	{
	          	$response = 'OK';
	          	break 2;
          	}
          default:
            $read = sqimap_fgets($imap_stream);
            break;

          case $tag{0}:
          {
            /* get the command */
            $arg = '';
            $i = strlen($tag)+1;
            $s = substr($read,$i);
            if (($j = strpos($s,' ')) || ($j = strpos($s,"\n"))) {
                $arg = substr($s,0,$j);
            }
            $found_tag = substr($read,0,$i-1);
            if ($arg && $found_tag==$tag) {
                switch ($arg)
                {
                  case 'OK':
                  case 'BAD':
                  case 'NO':
                  case 'BYE':
                  case 'PREAUTH':
                    $response = $arg;
                    $message = trim(substr($read,$i+strlen($arg)));
                    break 3; /* switch switch while */
                  default:
                    /* this shouldn't happen */
                    $response = $arg;
                    $message = trim(substr($read,$i+strlen($arg)));
                    break 3; /* switch switch while */
                }
            } elseif($found_tag !== $tag) {
                /* reset data array because we do not need this reponse */
                $data = array();
                $read = sqimap_fgets($imap_stream);
                break;
            }
          } // end case $tag{0}

          case '*':
          {
            if (preg_match('/^\*\s\d+\sFETCH/',$read)) {
                /* check for literal */
                $s = substr($read,-3);
                $fetch_data = array();
                do { /* outer loop, continue until next untagged fetch
                        or tagged reponse */
                    do { /* innerloop for fetching literals. with this loop
                            we prohibid that literal responses appear in the
                            outer loop so we can trust the untagged and
                            tagged info provided by $read */
                        $read_literal = false;
                        if ($s === "}\r\n") {
                            $j = strrpos($read,'{');
                            $iLit = substr($read,$j+1,-3);
                            $fetch_data[] = $read;
                            $sLiteral = sqimap_fread($imap_stream,$iLit,$filter,$outputstream,$no_return);
                            if ($sLiteral === false) { /* error */
                                break 4; /* while while switch while */
                            }
                            /* backwards compattibility */
                            $aLiteral = explode("\n", $sLiteral);
                            /* release not neaded data */
                            unset($sLiteral);
                            foreach ($aLiteral as $line) {
                                $fetch_data[] = $line ."\n";
                            }
                            /* release not neaded data */
                            unset($aLiteral);
                            /* next fgets belongs to this fetch because
                               we just got the exact literalsize and there
                               must follow data to complete the response */
                            $read = sqimap_fgets($imap_stream);
                            if ($read === false) { /* error */
                                break 4; /* while while switch while */
                            }
                            $s = substr($read,-3);
                            $read_literal = true;
                            continue;
                        } else {
                            $fetch_data[] = $read;
                        }
                        /* retrieve next line and check in the while
                           statements if it belongs to this fetch response */
                        $read = sqimap_fgets($imap_stream);
                        if ($read === false) { /* error */
                            break 4; /* while while switch while */
                        }
                        /* check for next untagged reponse and break */
                        if ($read{0} == '*') break 2;
                        $s = substr($read,-3);
                    } while ($s === "}\r\n" || $read_literal);
                    $s = substr($read,-3);
                } while ($read{0} !== '*' &&
                         substr($read,0,strlen($tag)) !== $tag);
                $resultlist[] = $fetch_data;
                /* release not neaded data */
                unset ($fetch_data);
            } else {
                $s = substr($read,-3);
                do {
                    if ($s === "}\r\n") {
                        $j = strrpos($read,'{');
                        $iLit = substr($read,$j+1,-3);
                        // check for numeric value to avoid that untagged responses like:
                        // * OK [PARSE] Unexpected characters at end of address: {SET:debug=51}
                        // will trigger literal fetching  ({SET:debug=51} !== int )
                        if (is_numeric($iLit)) {
                            $data[] = $read;
                            $sLiteral = fread($imap_stream,$iLit);
                            if ($sLiteral === false) { /* error */
                                $read = false;
                                break 3; /* while switch while */
                            }
                            $data[] = $sLiteral;
                            $data[] = sqimap_fgets($imap_stream);
                        } else {
                            $data[] = $read;
                        }
                    } else {
                         $data[] = $read;
                    }
                    $read = sqimap_fgets($imap_stream);
                    if ($read === false) {
                        break 3; /* while switch while */
                    } else if ($read{0} == '*') {
                        break;
                    }
                    $s = substr($read,-3);
                } while ($s === "}\r\n");
                break 1;
            }
            break;
          } // end case '*'
        }   // end switch
    } // end while

    /* error processing in case $read is false */
    if ($read === false) {
        unset($data);
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
                  _("ERROR: Connection dropped by IMAP server.") .
                  "</b><br />\n";
        $cmd = explode(' ',$query);
        $cmd = strtolower($cmd[0]);
        if ($query != '' &&  $cmd != 'login') {
            $string .= ("Query:") . ' '. sm_encode_html_special_chars($query)
            . '<br />' . "</font><br />\n";
        }
        error_box($string,$color);
        exit;
    }

    /* Set $resultlist array */
    if (!empty($data)) {
        $resultlist[] = $data;
    }
    elseif (empty($resultlist)) {
        $resultlist[] = array();
    }

    /* Return result or handle errors */
    if ($handle_errors == false) {
        return( $resultlist );
    }
    switch ($response) {
    case 'OK':
        return $resultlist;
        break;
    case 'NO':
        /* ignore this error from M$ exchange, it is not fatal (aka bug) */
        if (strstr($message, 'command resulted in') === false) {
            sqsession_register('NO', 'IMAP_FATAL_ERROR_TYPE');
            sqsession_register($query, 'IMAP_FATAL_ERROR_QUERY');
            sqsession_register($message, 'IMAP_FATAL_ERROR_MESSAGE');
            set_up_language($squirrelmail_language);
            require_once(SM_PATH . 'functions/display_messages.php');
            $string = "<b><font color=\"$color[2]\">\n" .
                _("ERROR: Could not complete request.") .
                "</b><br />\n" .
                _("Query:") . ' ' .
                sm_encode_html_special_chars($query) . '<br />' .
                _("Reason Given:") . ' ' .
                sm_encode_html_special_chars($message) . "</font><br />\n";
            error_box($string,$color);
            echo '</body></html>';
            exit;
        }
        break;
    case 'BAD':
        sqsession_register('BAD', 'IMAP_FATAL_ERROR_TYPE');
        sqsession_register($query, 'IMAP_FATAL_ERROR_QUERY');
        sqsession_register($message, 'IMAP_FATAL_ERROR_MESSAGE');
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
            _("ERROR: Bad or malformed request.") .
            "</b><br />\n" .
            _("Query:") . ' '.
            sm_encode_html_special_chars($query) . '<br />' .
            _("Server responded:") . ' ' .
            sm_encode_html_special_chars($message) . "</font><br />\n";
        error_box($string,$color);
        echo '</body></html>';
        exit;
    case 'BYE':
        sqsession_register('BYE', 'IMAP_FATAL_ERROR_TYPE');
        sqsession_register($query, 'IMAP_FATAL_ERROR_QUERY');
        sqsession_register($message, 'IMAP_FATAL_ERROR_MESSAGE');
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
            _("ERROR: IMAP server closed the connection.") .
            "</b><br />\n" .
            _("Query:") . ' '.
            sm_encode_html_special_chars($query) . '<br />' .
            _("Server responded:") . ' ' .
            sm_encode_html_special_chars($message) . "</font><br />\n";
        error_box($string,$color);
        echo '</body></html>';
        exit;
    default:
        sqsession_register('UNKNOWN', 'IMAP_FATAL_ERROR_TYPE');
        sqsession_register($query, 'IMAP_FATAL_ERROR_QUERY');
        sqsession_register($message, 'IMAP_FATAL_ERROR_MESSAGE');
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=\"$color[2]\">\n" .
            _("ERROR: Unknown IMAP response.") .
            "</b><br />\n" .
            _("Query:") . ' '.
            sm_encode_html_special_chars($query) . '<br />' .
            _("Server responded:") . ' ' .
            sm_encode_html_special_chars($message) . "</font><br />\n";
        error_box($string,$color);
       /* the error is displayed but because we don't know the reponse we
          return the result anyway */
       return $resultlist;
       break;
    }
}

function sqimap_read_data ($imap_stream, $tag_uid, $handle_errors,
                           &$response, &$message, $query = '',
                           $filter=false,$outputstream=false,$no_return=false) {

    $res = sqimap_read_data_list($imap_stream, $tag_uid, $handle_errors,
              $response, $message, $query,$filter,$outputstream,$no_return);
    /* sqimap_read_data should be called for one response
       but since it just calls sqimap_read_data_list which
       handles multiple responses we need to check for that
       and merge the $res array IF they are seperated and
       IF it was a FETCH response. */

    if (isset($res[1]) && is_array($res[1]) && isset($res[1][0])
        && preg_match('/^\* \d+ FETCH/', $res[1][0])) {
        $result = array();
        foreach($res as $index=>$value) {
            $result = array_merge($result, $res["$index"]);
        }
        return $result;
    }

    return $res[0];
}

/**
 * Logs the user into the IMAP server.  If $hide is set, no error messages
 * will be displayed.  This function returns the IMAP connection handle.
 */
function sqimap_login ($username, $password, $imap_server_address, $imap_port, $hide, $stream_options=array()) {
    global $color, $squirrelmail_language, $onetimepad, $use_imap_tls, $imap_auth_mech,
           $sqimap_capabilities, $display_imap_login_error;

    // Note/TODO: This hack grabs the $authz argument from the session
    $authz = '';
    global $authz;
    sqgetglobalvar('authz' , $authz , SQ_SESSION);

    if(!empty($authz)) {
        /* authz plugin - specific:
         * Get proxy login parameters from authz plugin configuration. If they
         * exist, they will override the current ones.
         * This is useful if we want to use different SASL authentication mechanism
         * and/or different TLS settings for proxy logins. */
        global $authz_imap_auth_mech, $authz_use_imap_tls, $authz_imapPort_tls;
        $imap_auth_mech = !empty($authz_imap_auth_mech) ? strtolower($authz_imap_auth_mech) : $imap_auth_mech;
        $use_imap_tls = !empty($authz_use_imap_tls)? $authz_use_imap_tls : $use_imap_tls;
        $imap_port = !empty($authz_use_imap_tls)? $authz_imapPort_tls : $imap_port;

        if($imap_auth_mech == 'login' || $imap_auth_mech == 'cram-md5') {
            logout_error("Misconfigured Plugin (authz or equivalent):<br/>".
            "The LOGIN and CRAM-MD5 authentication mechanisms cannot be used when attempting proxy login.");
            exit;
        }
    }

    if (!isset($onetimepad) || empty($onetimepad)) {
        sqgetglobalvar('onetimepad' , $onetimepad , SQ_SESSION );
    }
    $imap_server_address = sqimap_get_user_server($imap_server_address, $username);
        $host=$imap_server_address;

    // for backward compatibility: boolean $use_imap_tls set
    // to TRUE means to use plain TLS (as opposed to STARTTLS)
    //
    if ($use_imap_tls === TRUE)
        $use_imap_tls = 1;

    if (!isset($sqimap_capabilities)) {
        sqgetglobalvar('sqimap_capabilities' , $sqimap_capabilities , SQ_SESSION );
    }

    list($imap_stream, $server_info) = sqimap_create_stream($imap_server_address,$imap_port,$use_imap_tls,$stream_options,$hide);

    /* Decrypt the password */
    $password = OneTimePadDecrypt($password, $onetimepad);

    if (($imap_auth_mech == 'cram-md5') OR ($imap_auth_mech == 'digest-md5')) {
        // We're using some sort of authentication OTHER than plain or login
        $tag=sqimap_session_id(false);
        if ($imap_auth_mech == 'digest-md5') {
            $query = $tag . " AUTHENTICATE DIGEST-MD5\r\n";
        } elseif ($imap_auth_mech == 'cram-md5') {
            $query = $tag . " AUTHENTICATE CRAM-MD5\r\n";
        }
        fputs($imap_stream,$query);
        $answer=sqimap_fgets($imap_stream);
        // Trim the "+ " off the front
        $response=explode(" ",$answer,3);
        if ($response[0] == '+') {
            // Got a challenge back
            $challenge=$response[1];
            if ($imap_auth_mech == 'digest-md5') {
                $reply = digest_md5_response($username,$password,$challenge,'imap',$host,$authz);
            } elseif ($imap_auth_mech == 'cram-md5') {
                $reply = cram_md5_response($username,$password,$challenge);
            }
            fputs($imap_stream,$reply);
            $read=sqimap_fgets($imap_stream);
            if ($imap_auth_mech == 'digest-md5') {
                // DIGEST-MD5 has an extra step..
                if (substr($read,0,1) == '+') { // OK so far..
                    fputs($imap_stream,"\r\n");
                    $read=sqimap_fgets($imap_stream);
                }
            }
            $results=explode(" ",$read,3);
            $response=$results[1];
            $message=$results[2];
        } else {
            // Fake the response, so the error trap at the bottom will work
            $response="BAD";
            $message='IMAP server does not appear to support the authentication method selected.';
            $message .= '  Please contact your system administrator.';
        }
    } elseif ($imap_auth_mech == 'login') {
        // this is a workaround to alert users of LOGINDISABLED, which is done "right" in
        // devel but requires functions not available in stable. RFC requires us to
        // not send LOGIN when LOGINDISABLED is advertised.
        if(stristr($server_info, 'LOGINDISABLED')) {
            $response = 'BAD';
            $message = _("The IMAP server is reporting that plain text logins are disabled.").' '.
                _("Using CRAM-MD5 or DIGEST-MD5 authentication instead may work.").' ';
            if (!$use_imap_tls) {
                $message .= _("Also, the use of TLS may allow SquirrelMail to login.").' ';
            }
            $message .= _("Please contact your system administrator and report this error.");
        } else {
            // Original IMAP login code
            if(sq_is8bit($username) || sq_is8bit($password)) {
                $query['commands'][0] = 'LOGIN';
                $query['literal_args'][0] = $username;
                $query['commands'][1] = '';
                $query['literal_args'][1] = $password;
                $read = sqimap_run_literal_command($imap_stream, $query, false, $response, $message);
            } else {
                $query = 'LOGIN "' . quoteimap($username) . '"'
                       . ' "' . quoteimap($password) . '"';
                $read = sqimap_run_command ($imap_stream, $query, false, $response, $message);
            }
        }
    } elseif ($imap_auth_mech == 'plain') {
        /***
         * SASL PLAIN, RFC 4616 (updates 2595)
         *
         * The mechanism consists of a single message, a string of [UTF-8]
         * encoded [Unicode] characters, from the client to the server.  The
         * client presents the authorization identity (identity to act as),
         * followed by a NUL (U+0000) character, followed by the authentication
         * identity (identity whose password will be used), followed by a NUL
         * (U+0000) character, followed by the clear-text password.  As with
         * other SASL mechanisms, the client does not provide an authorization
         * identity when it wishes the server to derive an identity from the
         * credentials and use that as the authorization identity.
         */
        $tag=sqimap_session_id(false);
        $sasl = (isset($sqimap_capabilities['SASL-IR']) && $sqimap_capabilities['SASL-IR']) ? true : false;
        if(!empty($authz)) {
            $auth = base64_encode("$username\0$authz\0$password");
        } else {
            $auth = base64_encode("$username\0$username\0$password");
        }
        if ($sasl) {
            // IMAP Extension for SASL Initial Client Response
            // <draft-siemborski-imap-sasl-initial-response-01b.txt>
            $query = $tag . " AUTHENTICATE PLAIN $auth\r\n";
            fputs($imap_stream, $query);
            $read = sqimap_fgets($imap_stream);
        } else {
            $query = $tag . " AUTHENTICATE PLAIN\r\n";
            fputs($imap_stream, $query);
            $read=sqimap_fgets($imap_stream);
            if (substr($read,0,1) == '+') { // OK so far..
                fputs($imap_stream, "$auth\r\n");
                $read = sqimap_fgets($imap_stream);
            }
        }
        $results=explode(" ",$read,3);
        $response=$results[1];
        $message=$results[2];

    } else {
        $response="BAD";
        $message="Internal SquirrelMail error - unknown IMAP authentication method chosen.  Please contact the developers.";
    }

    /* If the connection was not successful, lets see why */
    if ($response != 'OK') {
        if (!$hide) {
            if ($response != 'NO') {
                /* "BAD" and anything else gets reported here. */
                $message = sm_encode_html_special_chars($message);
                set_up_language($squirrelmail_language, true);
                require_once(SM_PATH . 'functions/display_messages.php');
                if ($response == 'BAD') {
                    $string = sprintf (_("Bad request: %s")."<br />\r\n", $message);
                } else {
                    $string = sprintf (_("Unknown error: %s") . "<br />\n", $message);
                }
                if (isset($read) && is_array($read)) {
                    $string .= '<br />' . _("Read data:") . "<br />\n";
                    foreach ($read as $line) {
                        $string .= sm_encode_html_special_chars($line) . "<br />\n";
                    }
                }
                error_box($string,$color);
                exit;
            } else {
                /*
                 * If the user does not log in with the correct
                 * username and password it is not possible to get the
                 * correct locale from the user's preferences.
                 * Therefore, apply the same hack as on the login
                 * screen.
                 *
                 * $squirrelmail_language is set by a cookie when
                 * the user selects language and logs out
                 */

                set_up_language($squirrelmail_language, true);
                include_once(SM_PATH . 'functions/display_messages.php' );
                sqsession_destroy();
                /* terminate the session nicely */
                sqimap_logout($imap_stream);

                // determine what error message to show to the user
                //
                $fail_msg = _("Unknown user or password incorrect.");
                if ($display_imap_login_error) {
                    // See if there is an error message from the server
                    // Skip any rfc5530 response code: '[something]' at the
                    // start of the message
                    if (!empty($message)
                     && $message{0} == '['
                     && ($end = strstr($message, ']'))
                     && $end != ']') {
                        $message = substr($end, 1);
                    }
                    // Remove surrounding spaces and if there
                    // is anything left, display that as the
                    // error message:
                    $message = trim($message);
                    if (strlen($message))
                        $fail_msg = _($message);
                }

                logout_error($fail_msg);
                exit;
            }
        } else {
            exit;
        }
    }

    // Run ID command if configured - RFC 2971
    //
    // Administrator must declare a configuration variable called
    // $imap_id_command_args in config/config_local.php which must
    // be an array, where each key is an attibute to be sent in the
    // IMAP ID command to the server.  Values will be sent as-is
    // except if the value is "###REMOTE ADDRESS###" (without quotes)
    // in which case the current user's real IP address will be
    // substituted.  If "###X-FORWARDED-FOR###" is used and a
    // "X-FORWARDED-FOR" header is present in the client request,
    // the contents of that header are used (careful, this can be
    // forged).  If "###X-FORWARDED-FOR OR REMOTE ADDRESS###" is
    // used, then the "X-FORWARDED-FOR" header is used if it is
    // present in the request, otherwise, the client's connecting
    // IP address is used.  The following attributes will always be
    // added unless they are specifically overridden with a blank
    // value:
    //    name, vendor, support-url, version
    // A parsed representation of server's response is made available
    // to plugins as both a global and session variable named
    // "imap_server_id_response" (a simple key/value array) unless
    // response parsing is turned off by way of setting a variable
    // named $do_not_parse_imap_id_command_response in
    // config/config_local.php to TRUE, in which case, the stored
    // response will be the unparsed IMAP response.
    //
    global $imap_id_command_args, $do_not_parse_imap_id_command_response;
    if (!empty($imap_id_command_args) && is_array($imap_id_command_args)
     && sqimap_capability($imap_stream, 'ID')) {

        static $args = array();
        if (empty($args)) {
            if (!isset($imap_id_command_args['name']))
                $imap_id_command_args['name'] = 'SquirrelMail';
            if (!isset($imap_id_command_args['vendor']))
                $imap_id_command_args['vendor'] = 'SquirrelMail Project Team';
            if (!isset($imap_id_command_args['support-url']))
                $imap_id_command_args['support-url'] = 'https://squirrelmail.org';
            if (!isset($imap_id_command_args['version'])) {
                global $version;
                $imap_id_command_args['version'] = $version;
            }
            foreach ($imap_id_command_args as $key => $value) {
                $key = trim($key);
                $value = trim($value);
                if ($key === '' || $value === '')
                   continue;
                if ($value === '###REMOTE ADDRESS###' && sqGetGlobalVar('REMOTE_ADDR', $remote_addr, SQ_SERVER))
                    $value = $remote_addr;
                else if ($value === '###X-FORWARDED-FOR###' && sqGetGlobalVar('HTTP_X_FORWARDED_FOR', $remote_addr, SQ_SERVER))
                    $value = $remote_addr;
                else if ($value === '###X-FORWARDED-FOR OR REMOTE ADDRESS###') {
                    if (sqGetGlobalVar('HTTP_X_FORWARDED_FOR', $remote_addr, SQ_SERVER))
                        $value = $remote_addr;
                    else if (sqGetGlobalVar('REMOTE_ADDR', $remote_addr, SQ_SERVER))
                        $value = $remote_addr;
                }
                else if ($value === '###REMOTE ADDRESS###' && sqGetGlobalVar('REMOTE_ADDR', $remote_addr, SQ_SERVER)) {
                    $value = $remote_addr;
                }
                $args[] = '"' . str_replace(array('"', '\\'), array('\\"', '\\\\'), $key)
                               . '" "' . str_replace(array('"', '\\'), array('\\"', '\\\\'), $value) . '"';
            }
        }
        $read_ary = sqimap_run_command($imap_stream, 'ID (' . implode(' ', $args) . ')', false, $response, $message);
        if (!empty($read_ary) && is_array($read_ary)) {
            global $imap_server_id_response;
            if ($do_not_parse_imap_id_command_response)
               $imap_server_id_response = $read_ary;
            else
            {
               $imap_server_id_response = array();

               // NOTE that this parser ignores closing ) sign, so
               //      technically some kind of malformed server
               //      response could cause extra junk to be included here
               foreach ($read_ary as $info)
               {
                  $parsed_info = explode('(', $info, 2);
                  if (!empty($parsed_info[1]))
                  {
                     // find opening quote for the next key name
                     while ($parsed_info = explode('"', $parsed_info[1], 2))
                     {
                        if (empty($parsed_info[1]))
                           break;
                        else
                        {
                           // find closing quote for the key name
                           $pos = strpos($parsed_info[1], '"');
                           if ($pos === FALSE)
                              break;
                           else
                           {
                              $key = substr($parsed_info[1], 0, $pos);
                              $parsed_info[1] = substr($parsed_info[1], $pos + 1);

                              // find opening quote for the key's value
                              $parsed_info = explode('"', $parsed_info[1], 2);
                              if (empty($parsed_info[1]))
                                 break;
                              else
                              {
                                 // find closing quote for the key's value
                                 $pos = strpos($parsed_info[1], '"');
                                 if ($pos === FALSE)
                                    break;
                                 else
                                 {
                                    $imap_server_id_response[$key] = substr($parsed_info[1], 0, $pos);
                                    $parsed_info[1] = substr($parsed_info[1], $pos + 1);
                                 }
                              }
                           }
                        }
                     }
                  }
               }
            }
            sqsession_register($imap_server_id_response, 'imap_server_id_response');
        }
    }

    return $imap_stream;
}

/**
 * Simply logs out the IMAP session
 * @param stream imap_stream the IMAP connection to log out.
 * @return void
 */
function sqimap_logout ($imap_stream) {
    /* Logout is not valid until the server returns 'BYE'
     * If we don't have an imap_stream we're already logged out */
    if(isset($imap_stream) && $imap_stream) {
        sqimap_run_command($imap_stream, 'LOGOUT', false, $response, $message);
        fclose($imap_stream);
    }
}

/**
 * Connects to the IMAP server and returns a resource identifier for use with
 * the other SquirrelMail IMAP functions. Does NOT login!
 * @param string server hostname of IMAP server
 * @param int port port number to connect to
 * @param integer $tls whether to use plain text(0), TLS(1) or STARTTLS(2) when connecting.
 * @param array $stream_options Stream context options, see config_local.php
 *                           for more details (OPTIONAL)
 * @param boolean $hide Whether or not to show error output
 * @return array with two elements: imap-stream resource identifier
 *               and IMAP server response string
 *
 * @since 1.4.23 and 1.5.0 (usable only in 1.5.1 or later) (1.4.23 has a couple subtle differences from 1.5.x)
 */
function sqimap_create_stream($server,$port,$tls=0,$stream_options=array(),$hide=FALSE) {
    global $squirrelmail_language;

    if (strstr($server,':') && ! preg_match("/^\[.*\]$/",$server)) {
        // numerical IPv6 address must be enclosed in square brackets
        $server = '['.$server.']';
    }

    // NB: Using "ssl://" ensures the highest possible TLS version
    // will be negotiated with the server (whereas "tls://" only
    // uses TLS version 1.0)
    //
    if ($tls == 1) {
        if ((check_php_version(4,3)) and (extension_loaded('openssl'))) {
            if (function_exists('stream_socket_client')) {
                $server_address = 'ssl://' . $server . ':' . $port;
                $ssl_context = @stream_context_create($stream_options);
                $connect_timeout = ini_get('default_socket_timeout');
                // null timeout is broken
                if ($connect_timeout == 0)
                    $connect_timeout = 15;
                $imap_stream = @stream_socket_client($server_address, $error_number, $error_string, $connect_timeout, STREAM_CLIENT_CONNECT, $ssl_context);
            } else {
                $imap_stream = @fsockopen('ssl://' . $server, $port, $error_number, $error_string, 15);
            }
        } else {
            require_once(SM_PATH . 'functions/display_messages.php');
            logout_error( sprintf(_("Error connecting to IMAP server: %s."), $server).
                '<br />'.
                _("TLS is enabled, but this version of PHP does not support TLS sockets, or is missing the openssl extension.").
                '<br /><br />'.
                _("Please contact your system administrator and report this error."),
                          sprintf(_("Error connecting to IMAP server: %s."), $server));
        }
    } else {
        $imap_stream = @fsockopen($server, $port, $error_number, $error_string, 15);
    }


    /* Do some error correction */
    if (!$imap_stream) {
        if (!$hide) {
            set_up_language($squirrelmail_language, true);
            require_once(SM_PATH . 'functions/display_messages.php');
            logout_error( sprintf(_("Error connecting to IMAP server: %s."), $server).
                "<br />\r\n$error_number : $error_string<br />\r\n",
                sprintf(_("Error connecting to IMAP server: %s."), $server) );
        }
        exit;
    }
    $server_info = fgets ($imap_stream, 1024);

    /**
     * Implementing IMAP STARTTLS (rfc2595) in php 5.1.0+
     * http://www.php.net/stream-socket-enable-crypto
     */
    if ($tls === 2) {
        if (function_exists('stream_socket_enable_crypto')) {
            // check starttls capability, don't use cached capability version
            if (! sqimap_capability($imap_stream, 'STARTTLS', false)) {
                // imap server does not declare starttls support
                sqimap_error_box(sprintf(_("Error connecting to IMAP server: %s."), $server),
                                 '','',
                                 _("IMAP STARTTLS is enabled in SquirrelMail configuration, but used IMAP server does not support STARTTLS."));
                exit;
            }

            // issue starttls command and check response
            sqimap_run_command($imap_stream, 'STARTTLS', false, $starttls_response, $starttls_message);
            // check response
            if ($starttls_response!='OK') {
                // starttls command failed
                sqimap_error_box(sprintf(_("Error connecting to IMAP server: %s."), $server),
                                 'STARTTLS',
                                 _("Server replied:") . ' ',
                                 $starttls_message);
                exit();
            }

            // start crypto on connection. suppress function errors.
            if (@stream_socket_enable_crypto($imap_stream,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                // starttls was successful

                /**
                 * RFC 2595 requires to discard CAPABILITY information after successful
                 * STARTTLS command. We don't follow RFC, because SquirrelMail stores CAPABILITY
                 * information only after successful login (src/redirect.php) and cached information
                 * is used only in other php script connections after successful STARTTLS. If script
                 * issues sqimap_capability() call before sqimap_login() and wants to get initial
                 * capability response, script should set third sqimap_capability() argument to false.
                 */
                //sqsession_unregister('sqimap_capabilities');
            } else {
                /**
                 * stream_socket_enable_crypto() call failed. Possible issues:
                 * - broken ssl certificate (uw drops connection, error is in syslog mail facility)
                 * - some ssl error (can reproduce with STREAM_CRYPTO_METHOD_SSLv3_CLIENT, PHP E_WARNING
                 *   suppressed in stream_socket_enable_crypto() call)
                 */
                sqimap_error_box(sprintf(_("Error connecting to IMAP server: %s."), $server),
                                 '','',
                                 _("Unable to start TLS."));
                /**
                 * Bug: stream_socket_enable_crypto() does not register SSL errors in
                 * openssl_error_string() or stream notification wrapper and displays
                 * them in E_WARNING level message. It is impossible to retrieve error
                 * message without own error handler.
                 */
                exit;
            }
        } else {
            // php install does not support stream_socket_enable_crypto() function
            sqimap_error_box(sprintf(_("Error connecting to IMAP server: %s."), $server),
                             '','',
                             _("IMAP STARTTLS is enabled in SquirrelMail configuration, but used PHP version does not support functions that allow to enable encryption on open socket."));
            exit;
        }
    }
    return array($imap_stream, $server_info);
}

/**
 * Retreive the CAPABILITY string from the IMAP server.
 * If capability is set, returns only that specific capability,
 * else returns array of all capabilities.
 */
function sqimap_capability($imap_stream, $capability='', $use_cache=TRUE) {

    global $sqimap_capabilities;
    $capabilities = $sqimap_capabilities; // don't overwrite global value

    if (!$use_cache || !is_array($capabilities)) {
        $read = sqimap_run_command($imap_stream, 'CAPABILITY', true, $a, $b);

        $c = explode(' ', $read[0]);
        for ($i=2; $i < count($c); $i++) {
            $cap_list = explode('=', $c[$i]);
            if (isset($cap_list[1])) {
                // FIX ME. capabilities can occure multiple times.
                // THREAD=REFERENCES THREAD=ORDEREDSUBJECT
                $capabilities[$cap_list[0]] = $cap_list[1];
            } else {
                $capabilities[$cap_list[0]] = TRUE;
            }
        }
    }

    // this was terrible design in the first place, but we have
    // to do this since there may be expectations in the calling
    // code that the global value gets set if it was empty
    if (!is_array($sqimap_capabilities)) {
       $sqimap_capabilities = $capabilities;
    }

    if ($capability) {
        if (isset($capabilities[$capability])) {
                return $capabilities[$capability];
        } else {
                return false;
        }
    }
    return $capabilities;
}

/**
 * Returns the delimeter between mailboxes: INBOX/Test, or INBOX.Test
 */
function sqimap_get_delimiter ($imap_stream = false) {
    global $sqimap_delimiter, $optional_delimiter;

    /* Use configured delimiter if set */
    if((!empty($optional_delimiter)) && $optional_delimiter != 'detect') {
        return $optional_delimiter;
    }

    /* Do some caching here */
    if (!$sqimap_delimiter) {
        if (sqimap_capability($imap_stream, 'NAMESPACE')
            /*
             * According to something that I can't find, this is supposed to work on all systems
             * OS: This won't work in Courier IMAP.
             * OS: According to rfc2342 response from NAMESPACE command is:
             * OS: * NAMESPACE (PERSONAL NAMESPACES) (OTHER_USERS NAMESPACE) (SHARED NAMESPACES)
             * OS: We want to lookup all personal NAMESPACES...
             */
         && ($read = sqimap_run_command($imap_stream, 'NAMESPACE', true, $a, $b))
         && preg_match('/\* NAMESPACE +(\( *\(.+\) *\)|NIL) +(\( *\(.+\) *\)|NIL) +(\( *\(.+\) *\)|NIL)/i', $read[0], $data)
         && preg_match('/^\( *\((.*)\) *\)/', $data[1], $data2)) {
            $pn = $data2[1];
            $pna = explode(')(', $pn);
            $delnew = array();
            while (list($k, $v) = each($pna)) {
                $lst = explode('"', $v);
                if (isset($lst[3])) {
                    $delnew[$lst[1]] = $lst[3];
                } else {
                    $delnew[$lst[1]] = '';
                }
            }
            $sqimap_delimiter = array_shift($delnew);
        } else {
            fputs ($imap_stream, ". LIST \"INBOX\" \"\"\r\n");
            $read = sqimap_read_data($imap_stream, '.', true, $a, $b);
            $quote_position = strpos ($read[0], '"');
            $sqimap_delimiter = substr ($read[0], $quote_position+1, 1);
        }
    }
    return $sqimap_delimiter;
}


/**
 * Gets the number of messages in the current mailbox.
 */
function sqimap_get_num_messages ($imap_stream, $mailbox) {
    $read_ary = sqimap_run_command ($imap_stream, "EXAMINE \"$mailbox\"", false, $result, $message);
    for ($i = 0; $i < count($read_ary); $i++) {
        if (preg_match('/[^ ]+ +([^ ]+) +EXISTS/', $read_ary[$i], $regs)) {
            return $regs[1];
        }
    }
    return false; //"BUG! Couldn't get number of messages in $mailbox!";
}

/**
 * Parses an address string.
FIXME: the original author should step up and document this - the following is a guess based on a couple simple tests of *using* the function, not knowing the code inside
 *
 * @param string $address Generic email address(es) in any format, including 
 *                        possible personal information as well as the 
 *                        actual address (such as "Jose" <jose@example.org>
 *                        or "Jose" <jose@example.org>, "Keiko" <keiko@example.org>)
 * @param int $max        The most email addresses to parse out of the given string
 *
 * @return array An array with one sub-array for each address found in the
 *               given string.  Each sub-array contains two (?) entries, the
 *               first containing the actual email address, the second
 *               containing any personal information that was in the address
 *               string
 *
 */
function parseAddress($address, $max=0) {
    $aTokens = array();
    $aAddress = array();
    $iCnt = strlen($address);
    $aSpecials = array('(' ,'<' ,',' ,';' ,':');
    $aReplace =  array(' (',' <',' ,',' ;',' :');
    $address = str_replace($aSpecials,$aReplace,$address);
    $i = 0;
    while ($i < $iCnt) {
        $cChar = $address{$i};
        switch($cChar)
        {
        case '<':
            $iEnd = strpos($address,'>',$i+1);
            if (!$iEnd) {
               $sToken = substr($address,$i);
               $i = $iCnt;
            } else {
               $sToken = substr($address,$i,$iEnd - $i +1);
               $i = $iEnd;
            }
            $sToken = str_replace($aReplace, $aSpecials,$sToken);
            $aTokens[] = $sToken;
            break;
        case '"':
            $iEnd = strpos($address,$cChar,$i+1);
            if ($iEnd) {
                // skip escaped quotes
                $prev_char = $address{$iEnd-1};
                while ($prev_char === '\\' && substr($address,$iEnd-2,2) !== '\\\\') {
                    $iEnd = strpos($address,$cChar,$iEnd+1);
                    if ($iEnd) {
                        $prev_char = $address{$iEnd-1};
                    } else {
                        $prev_char = false;
                    }
                }
            }
            if (!$iEnd) {
                $sToken = substr($address,$i);
                $i = $iCnt;
            } else {
                // also remove the surrounding quotes
                $sToken = substr($address,$i+1,$iEnd - $i -1);
                $i = $iEnd;
            }
            $sToken = str_replace($aReplace, $aSpecials,$sToken);
            if ($sToken) $aTokens[] = $sToken;
            break;
        case '(':
            $iEnd = strrpos($address,')');
            if (!$iEnd || $iEnd < $i) {
                $sToken = substr($address,$i);
                $i = $iCnt;
            } else {
                $sToken = substr($address,$i,$iEnd - $i + 1);
                $i = $iEnd;
            }
            $sToken = str_replace($aReplace, $aSpecials,$sToken);
            $aTokens[] = $sToken;
            break;
        case ',':
        case ';':
        case ';':
        case ' ':
            $aTokens[] = $cChar;
            break;
        default:
            $iEnd = strpos($address,' ',$i+1);
            if ($iEnd) {
                $sToken = trim(substr($address,$i,$iEnd - $i));
                $i = $iEnd-1;
            } else {
                $sToken = trim(substr($address,$i));
                $i = $iCnt;
            }
            if ($sToken) $aTokens[] = $sToken;
        }
        ++$i;
    }
    $sPersonal = $sEmail = $sComment = $sGroup = '';
    $aStack = $aComment = array();
    foreach ($aTokens as $sToken) {
        if ($max && $max == count($aAddress)) {
            return $aAddress;
        }
        $cChar = $sToken{0};
        switch ($cChar)
        {
          case '=':
          case '"':
          case ' ':
            $aStack[] = $sToken;
            break;
          case '(':
            $aComment[] = substr($sToken,1,-1);
            break;
          case ';':
            if ($sGroup) {
                $sEmail = trim(implode(' ',$aStack));
                $aAddress[] = array($sGroup,$sEmail);
                $aStack = $aComment = array();
                $sGroup = '';
                break;
            }
          case ',':
            if (!$sEmail) {
                while (count($aStack) && !$sEmail) {
                    $sEmail = trim(array_pop($aStack));
                }
            }
            if (count($aStack)) {
                $sPersonal = trim(implode('',$aStack));
            } else {
                $sPersonal = '';
            }
            if (!$sPersonal && count($aComment)) {
                $sComment = implode(' ',$aComment);
                $sPersonal .= $sComment;
            }
            $aAddress[] = array($sEmail,$sPersonal);
            $sPersonal = $sComment = $sEmail = '';
            $aStack = $aComment = array();
            break;
          case ':':
            $sGroup = implode(' ',$aStack); break;
            $aStack = array();
            break;
          case '<':
            $sEmail = trim(substr($sToken,1,-1));
            break;
          case '>':
            /* skip */
            break;
          default: $aStack[] = $sToken; break;
        }
    }
    /* now do the action again for the last address */
    if (!$sEmail) {
        while (count($aStack) && !$sEmail) {
            $sEmail = trim(array_pop($aStack));
        }
    }
    if (count($aStack)) {
        $sPersonal = trim(implode('',$aStack));
    } else {
        $sPersonal = '';
    }
    if (!$sPersonal && count($aComment)) {
        $sComment = implode(' ',$aComment);
        $sPersonal .= $sComment;
    }
    $aAddress[] = array($sEmail,$sPersonal);
    return $aAddress;
}


/**
 * Returns the number of unseen messages in this folder.
 */
function sqimap_unseen_messages ($imap_stream, $mailbox) {
    $read_ary = sqimap_run_command ($imap_stream, "STATUS \"$mailbox\" (UNSEEN)", false, $result, $message);
    $i = 0;
    $regs = array(false, false);
    while (isset($read_ary[$i])) {
        if (preg_match('/UNSEEN\s+([0-9]+)/i', $read_ary[$i], $regs)) {
            break;
        }
        $i++;
    }
    return $regs[1];
}

/**
 * Returns the number of unseen/total messages in this folder
 */
function sqimap_status_messages ($imap_stream, $mailbox) {
    $read_ary = sqimap_run_command ($imap_stream, "STATUS \"$mailbox\" (MESSAGES UNSEEN RECENT)", false, $result, $message);
    $i = 0;
    $messages = $unseen = $recent = false;
    $regs = array(false,false);
    while (isset($read_ary[$i])) {
        if (preg_match('/UNSEEN\s+([0-9]+)/i', $read_ary[$i], $regs)) {
            $unseen = $regs[1];
        }
        if (preg_match('/MESSAGES\s+([0-9]+)/i', $read_ary[$i], $regs)) {
            $messages = $regs[1];
        }
        if (preg_match('/RECENT\s+([0-9]+)/i', $read_ary[$i], $regs)) {
            $recent = $regs[1];
        }
        $i++;
    }
    return array('MESSAGES' => $messages, 'UNSEEN'=>$unseen, 'RECENT' => $recent);
}


/**
 * Saves a message to a given folder -- used for saving sent messages
 */
function sqimap_append ($imap_stream, $sent_folder, $length) {
    fputs ($imap_stream, sqimap_session_id() . " APPEND \"$sent_folder\" (\\Seen) {".$length."}\r\n");
    $tmp = fgets ($imap_stream, 1024);
    sqimap_append_checkresponse($tmp, $sent_folder);
}

function sqimap_append_done ($imap_stream, $folder='') {
    fputs ($imap_stream, "\r\n");
    $tmp = fgets ($imap_stream, 1024);
    sqimap_append_checkresponse($tmp, $folder);
}

function sqimap_append_checkresponse($response, $folder) {

    if (preg_match("/(.*)(BAD|NO)(.*)$/", $response, $regs)) {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');

        $reason = $regs[3];
        if ($regs[2] == 'NO') {
           $string = "<b><font color=\"$color[2]\">\n" .
                  _("ERROR: Could not append message to") ." $folder." .
                  "</b><br />\n" .
                  _("Server responded:") . ' ' .
                  $reason . "<br />\n";
           if (preg_match("/(.*)(quota)(.*)$/i", $reason, $regs)) {
              $string .= _("Solution:") . ' ' .
            _("Remove unneccessary messages from your folders. Start with your Trash folder.")
              ."<br />\n";
           }
           $string .= "</font>\n";
           error_box($string,$color);
        } else {
           $string = "<b><font color=\"$color[2]\">\n" .
                  _("ERROR: Bad or malformed request.") .
                  "</b><br />\n" .
                  _("Server responded:") . ' ' .
                  $reason . "</font><br />\n";
           error_box($string,$color);
           exit;
        }
    }
}

function sqimap_get_user_server ($imap_server, $username) {
   if (substr($imap_server, 0, 4) != "map:") {
       return $imap_server;
   }
   $function = substr($imap_server, 4);
   return $function($username);
}

/**
 * This is an example that gets IMAP servers from yellowpages (NIS).
 * you can simple put map:map_yp_alias in your $imap_server_address
 * in config.php use your own function instead map_yp_alias to map your
 * LDAP whatever way to find the users IMAP server.
 */
function map_yp_alias($username) {
   $safe_username = escapeshellarg($username);
   $yp = `ypmatch $safe_username aliases`;
   return chop(substr($yp, strlen($username)+1));
}

/**
 * Function to display an error related to an IMAP query.
 * @param string title the caption of the error box
 * @param string query the query that went wrong
 * @param string message_title optional message title
 * @param string message optional error message
 * @param string $link an optional link to try again
 * @return void
 * @since 1.4.23 and 1.5.0
 */
function sqimap_error_box($title, $query = '', $message_title = '', $message = '', $link = '')
{
    global $color, $squirrelmail_language;

    set_up_language($squirrelmail_language);
//FIXME: NO HTML IN CORE!
    $string = "<font color=\"$color[2]\"><b>\n" . $title . "</b><br />\n";
    $cmd = explode(' ',$query);
    $cmd= strtolower($cmd[0]);

    if ($query != '' &&  $cmd != 'login')
        $string .= _("Query:") . ' ' . sm_encode_html_special_chars($query) . '<br />';
    if ($message_title != '')
        $string .= $message_title;
    if ($message != '')
        $string .= sm_encode_html_special_chars($message);
//FIXME: NO HTML IN CORE!
    $string .= "</font><br />\n";
    if ($link != '')
        $string .= $link;
    error_box($string);
}

