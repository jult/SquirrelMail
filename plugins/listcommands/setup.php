<?php

/**
 * setup.php
 *
 * Copyright (c) 1999-2019 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Implementation of RFC 2369 for SquirrelMail.
 * When viewing a message from a mailinglist complying with this RFC,
 * this plugin displays a menu which gives the user a choice of mailinglist
 * commands such as (un)subscribe, help and list archives.
 *
 * $Id: setup.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package plugins
 * @subpackage listcommands
 */

function squirrelmail_plugin_init_listcommands () {
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['read_body_header']['listcommands'] = 'plugin_listcommands_menu';
}

function plugin_listcommands_menu() {
    global $passed_id, $passed_ent_id, $color, $mailbox,
           $message, $compose_new_win, $startMessage;

    /**
     * Array of commands we can deal with from the header. The Reply option
     * is added later because we generate it using the Post information.
     */
    $fieldsdescr = array('post'        => _("Post to List"),
                         'reply'       => _("Reply to List"),
                         'subscribe'   => _("Subscribe"),
                         'unsubscribe' => _("Unsubscribe"),
                         'archive'     => _("List Archives"),
                         'owner'       => _("Contact Listowner"),
                         'help'        => _("Help"));
    $output = array();

    foreach ($message->rfc822_header->mlist as $cmd => $actions) {

        /* I don't know this action... skip it */
        if ( !array_key_exists($cmd, $fieldsdescr) ) {
            continue;
        }

        /* proto = {mailto,href} */
        $aActionKeys = array_keys($actions);
        // note that we only use the first cmd/action, ignore the rest
        $proto = array_shift($aActionKeys);
        $act   = array_shift($actions);

        if ($proto == 'mailto') {

            $identity = '';

            if (($cmd == 'post') || ($cmd == 'owner')) {
                $url = 'src/compose.php?' .
                (isset($startMessage)?'startMessage='.$startMessage.'&amp;':'');
            } else {
                $url = "plugins/listcommands/mailout.php?action=$cmd&amp;";

                // try to find which identity the mail should come from
                include_once(SM_PATH . 'functions/identity.php');
                $idents = get_identities();
                // ripped from src/compose.php
                $identities = array();
                if (count($idents) > 1) {
                    foreach($idents as $nr=>$data) {
                        $enc_from_name = '"'.$data['full_name'].'" <'. $data['email_address'].'>';
                        $identities[] = $enc_from_name;
                    }

                    $identity_match = $message->rfc822_header->findAddress($identities);
                    if ($identity_match !== FALSE) {
                        $identity = $identity_match;
                    }
                }
            }

            // if things like subject are given, peel them off and give
            // them to src/compose.php as is (not encoded)
            if (strpos($act, '?') > 0) {
               list($act, $parameters) = explode('?', $act, 2);
               $parameters = '&amp;identity=' . $identity . '&amp;' . $parameters;
            } else {
               $parameters = '&amp;identity=' . $identity;
            }

            $url .= 'send_to=' . urlencode($act) . $parameters;

            $output[] = makeComposeLink($url, $fieldsdescr[$cmd]);

            if ($cmd == 'post') {
                $url .= '&amp;passed_id='.$passed_id.
                    '&amp;mailbox='.urlencode($mailbox).
                    (isset($passed_ent_id)?'&amp;passed_ent_id='.$passed_ent_id:'');
                $url .= '&amp;smaction=reply';
                
                $output[] = makeComposeLink($url, $fieldsdescr['reply']);
            }
        } else if ($proto == 'href') {
            $output[] = '<a href="' . $act . '" target="_blank">'
                      . $fieldsdescr[$cmd] . '</a>';
        }
    }

    if (count($output) > 0) {
        echo '<tr>';
        echo html_tag('td', '<b>' . _("Mailing List") . ':&nbsp;&nbsp;</b>',
                      'right', '', 'valign="middle" width="20%"') . "\n";
        echo html_tag('td', '<small>' . implode('&nbsp;|&nbsp;', $output) . '</small>',
                      'left', $color[0], 'valign="middle" width="80%"') . "\n";
        echo '</tr>';
    }
}

