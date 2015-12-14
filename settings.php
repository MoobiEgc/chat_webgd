<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_online_users_timetosee', get_string('timetosee', 'block_chat_webgd'),
                   get_string('configtimetosee', 'block_chat_webgd'), 5, PARAM_INT));
}