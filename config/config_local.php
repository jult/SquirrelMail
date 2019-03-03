<?php

/**
 * Local config overrides.
 *
 * You can override the config.php settings here.
 * Don't do it unless you know what you're doing.
 * Use standard PHP syntax, see config.php for examples.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: config_local.php 18 2019-03-01 09:09:09Z jult $
 */

$config_use_color = 2;
$frame_top     = '_top';

$motd = "";

$squirrelmail_default_language = 'en_US';
$default_charset       = 'utf-8';
$lossy_encoding        = false;

$imapServerAddress      = '127.0.0.1';
$imapPort               = 143;
$useSendmail            = false;
$smtpServerAddress      = '127.0.0.1';
$smtpPort               = 25;
$pop_before_smtp        = false;
$pop_before_smtp_host   = '';
$imap_server_type       = 'dovecot';
$invert_time            = false;
$optional_delimiter     = '.';
$encode_header_key      = '';

$default_folder_prefix          = '';
$trash_folder                   = 'Trash';
$sent_folder                    = 'Sent';
$draft_folder                   = 'Drafts';
$default_move_to_trash          = true;
$default_move_to_sent           = true;
$default_save_as_draft          = true;
$show_prefix_option             = false;
$list_special_folders_first     = true;
$use_special_folder_color       = true;
$auto_expunge                   = true;
$default_sub_of_inbox           = false;
$show_contain_subfolders_option = false;
$default_unseen_notify          = 2;
$default_unseen_type            = 1;
$auto_create_special            = true;
$delete_folder                  = false;
$noselect_fix_enable            = false;

$dir_hash_level           = 0;
$default_left_size        = '146';
$force_username_lowercase = true;
$default_use_priority     = true;
$hide_sm_attributions     = true;
$default_use_mdn          = true;
$edit_identity            = true;
$edit_name                = true;
$hide_auth_header         = false;
$allow_thread_sort        = true;
$allow_server_sort        = true;
$allow_charset_search     = true;
$uid_support              = true;
$edit_reply_to            = true;
$disable_thread_sort      = false;
$disable_server_sort      = false;
$allow_charset_search     = true;
$allow_advanced_search    = 2;

$show_alternative_names   = false;
$aggressive_decoding   = false;
$lossy_encoding        = false;

$time_zone_type           = 0;
$config_location_base     = '';

$disable_plugins          = false;

$session_name          = 'SQMSESSID';
$only_secure_cookies     = false;
$disable_security_tokens = false;
$use_transparent_security_image = true;
$use_iframe = false;
$ask_user_info = true;
$use_icons = true;
$use_php_recode = false;
$use_php_iconv = false;
$buffer_output = false;
$allow_remote_configtest = false;
$secured_config = true;
$browser_rendering_mode  = 'quirks';
$max_token_age_days = 2;
$newmail_allowsound = true;

$user_theme_default = 55;
$theme_css = 'jultcoolbig';
$theme_default = 55;
$theme[54]['PATH'] = SM_PATH . 'themes/forestnight.php';
$theme[54]['NAME'] = 'Forest Night (jult)';
$theme[55]['PATH'] = SM_PATH . 'themes/jult_dark.php';
$theme[55]['NAME'] = 'jult dark';
