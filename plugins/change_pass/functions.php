<?php
/*
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: functions.php,v 1.8 2009/12/09 00:21:11 indiri69 Exp $
 */

function change_pass_option_link_do() {
    global $optpage_blocks;

    sq_change_text_domain('change_pass');
    $optpage_blocks[] = array(
        'name' => _("Change Password"),
        'url'  => sqm_baseuri() . 'plugins/change_pass/options.php',
        'desc' => _("Use this to change your email password."),
        'js'   => FALSE
    );
    sq_change_text_domain('squirrelmail');
}

function change_pass_dochange($change_pass_old, $change_pass_new, $debug = false) {
    global $username, $imapServerAddress;
    $poppass_server = $imapServerAddress;
    $poppass_port   = 106;

    if(!@include(SM_PATH . 'config/config_change_pass.php')) {
        @include(SM_PATH . 'plugins/change_pass/config.php');
    }

    $error_messages = array();

    $pop_socket = @fsockopen($poppass_server, $poppass_port, $errno, $errstr);
    if (!$pop_socket) {
        sq_change_text_domain('change_pass');
        $error_messages[] = sprintf(_("Connection error: %s"), "$errstr ($errno)");
        sq_change_text_domain('squirrelmail');
        return $error_messages;
    }

    // Look for a 2xx result to continue
    $result = change_pass_readfb($pop_socket, $debug);
    if(preg_match('/^2\d\d/', $result)) {
        // Send the username whose password to change
        if(fwrite($pop_socket, "user $username\r\n")) {
            $result = change_pass_readfb($pop_socket, $debug);
            if(preg_match('/^[23]\d\d/', $result)) {
                // Send the users current password
                if(fwrite($pop_socket, "pass $change_pass_old\r\n")) {
                    $result = change_pass_readfb($pop_socket, $debug);
                    if(preg_match('/^[23]\d\d/', $result)) {
                        // Send the new password
                        if(fwrite($pop_socket, "newpass $change_pass_new\r\n")) {
                            $result = change_pass_readfb($pop_socket, $debug);
                            if(!preg_match('/^2\d\d/', $result)) {
                                sq_change_text_domain('change_pass');
                                $error_messages[] = _("Password change was not successful!");
                                sq_change_text_domain('squirrelmail');
                            } else {
                                change_pass_closeport($pop_socket, $debug);
                                sqauth_save_password($change_pass_new);
                                session_write_close();
                                header('Location: ' . sqm_baseuri() . 'src/options.php?optmode=submit&optpage=change_pass&plugin_change_pass=1&smtoken=' . sm_generate_security_token());
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
    change_pass_closeport($pop_socket, $debug);
    return $error_messages;
}

function change_pass_closeport($pop_socket, $debug = false) {
    if ($debug) {
        $messages[] =  "Closing Connection";
    }
    fputs($pop_socket, "quit\r\n");
    fclose($pop_socket);
}

function change_pass_readfb($pop_socket, $debug = false) {
   $strResp = '';
   $result  = '';

   if (!feof($pop_socket)) {
      $strResp = fgets($pop_socket, 1024);
      $result  = substr(trim($strResp), 0, 3);  // 200, 300, 500
      if($debug) {
          $messages[] = "--> $strResp";
      }
   }
   return $result;
}

function change_pass_info_real() {
    return array(
        'english_name' => 'Change Password',
        'authors'      => array(
            'Richie Low' => array(),
            'Tyler Akins' => array(),
            'Seth Randall' => array(
                'email' => 'indiri69@users.sourceforge.net',
                'sm_site_username' => 'randall',
            ),
        ),
        'version' => '3.1',
        'required_sm_version' => '1.4.0',
        'requires_configuration' => 0,
        'requires_source_patch'  => 0,
        'required_plugins'       => array(),
        'per_version_requirements' => array(
            '1.5.0'  => SQ_INCOMPATIBLE,
            '1.4.20' => array(
                'required_plugins' => array()
            ),
            '1.4.0'  => array(
                'required_plugins' => array(
                    'compatibility' => array(
                        'version'  => '2.0.16',
                        'activate' => FALSE
                    )
                )
            )
        ),
        'summary' => 'Change passwords using a poppass daemon',
        'details' => 'Works with a compatible poppass daemon to allow users to change their passwords.'
    );
}

function change_pass_loadinfo_real() {
    global $optpage, $optpage_name;

    if ($optpage == 'change_pass') {
        // i18n: is displayed after "Successfully Saved Options:"
        sq_change_text_domain('change_pass');
        $optpage_name = _("User's Password");
        sq_change_text_domain('squirrelmail');
    }
}
