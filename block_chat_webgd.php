<?php

require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/includes/bbb-api.php');

/**
 * This block needs to be reworked.
 * The new roles system does away with the concepts of rigid student and
 * teacher roles.
 */
class block_chat_webgd extends block_base {

    function init() {
        global $USER, $CFG;
        if ($USER->id) {
            $videoLibras = $CFG->wwwroot . '/blocks/chat_webgd/videos/chat.mp4';
            $imgLibras = $CFG->wwwroot . '/theme/moobi/pix/icons/mao-libras.png';
            $this->title = '<img src="'.$CFG->wwwroot.'/blocks/chat_webgd/pix/chat_individual.png" width="40">&nbsp;&nbsp;'.get_string('pluginname', 'block_chat_webgd').'<a class="hand" id="mainHand" style="margin-right:30px; margin-top:5px; float:right;" href="' . $videoLibras . '"><img src="'. $imgLibras .'" /></a>';
        }
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $USER, $CFG, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if ($USER->id) {
            $timetoshowusers = 500; //Seconds default
            $now = time();
            $timefrom = $now - 3600;
            // $timefrom = 100 * floor(($now - $timetoshowusers) / 100);

            $params = array();


            $userfields = user_picture::fields('u', array('username'));
            $params['now'] = $now;
            $params['timefrom'] = $timefrom;
            $params['id'] = $USER->id;
            $sql = "SELECT $userfields
                          FROM {user} u
                         WHERE u.lastaccess > :timefrom
                               AND u.lastaccess <= :now
                               AND u.deleted = 0
                               AND u.id <> :id";

            if ($users = $DB->get_records_sql($sql, $params, 0, 50)) {
                foreach ($users as $user) {
                    $users[$user->id]->fullname = fullname($user);
                }
            } else {
                $users = array();
            }

            $this->content->text .= '<div id="contatos">';
            if (!empty($users)) {

                $this->content->text .= "<ul class='list'>\n";

                foreach ($users as $user) {
                    $this->content->text .= '<li class="listentry">';

                    $this->content->text .= '<div class="user">';
                    $this->content->text .= '<a href="javascript:void(0);"  id="' . $user->id . '" nome="' . $user->fullname . '" class="comecar">';
                    $this->content->text .= $OUTPUT->user_picture($user, array('size' => 16, 'alttext' => false, 'link' => false)) . $user->fullname . '</a></div>';
                    $this->content->text .= "</li>\n";
                }
                $this->content->text .= '</ul><div class="clearer"><!-- --></div>';
            } else {
                $this->content->text .= "<div class=\"info\">" . get_string("none") . "</div>";
            }

            $chatDao = new ChatWebgdDao();
            $listaGrupos = $chatDao->findGroupUser($USER->id);


            $this->content->text .= '<hr /><img src="' . $CFG->wwwroot . '/blocks/chat_webgd/pix/chat_grupo.png" width="40">&nbsp;&nbsp;MEUS GRUPOS <a href="' . $CFG->wwwroot . '/blocks/chat_webgd/index.php" class="criar-grupo-chat"> + </a><ul class="list">';


            foreach ($listaGrupos as $grupo) {
                $this->content->text .= '<li class="listentry">';
                $this->content->text .= '<div class="user">';
                $this->content->text .= '<a href="javascript:void(0);" class="comecarGrupo" nome="' . $grupo->nome . '" id="' . $grupo->id . '_grupo">';
                $this->content->text .= $grupo->nome . '</a></div>';
                $this->content->text .= "</li>";
            }
            $this->content->text .= "</ul>";

            $this->content->text .= '</div>';





            $this->content->text .= '<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/chat_webgd/js/jquery.js"></script>
             <script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/chat_webgd/js/functions.js"></script>
             <script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/chat_webgd/js/chat.js"></script>
             <link href="' . $CFG->wwwroot . '/blocks/chat_webgd/styles.css" type="text/css" rel="stylesheet">';
        }

        return $this->content;
    }

}
