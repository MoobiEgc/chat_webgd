<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
global $USER, $CFG, $DB, $OUTPUT;
require_login(1);

$acao = optional_param('acao', '', PARAM_TEXT);
switch ($acao) {
    case 'inserir':
        $para = optional_param('para', 0, PARAM_INT);
        $mensagem = strip_tags(optional_param('mensagem', '', PARAM_TEXT));

        $msgObj = new stdClass;
        $msgObj->mensagem = $mensagem;
        $msgObj->user_id = $USER->id;
        $msgObj->para_id = $para;
        $msgObj->lido = 0;
        $msgObj->data = time();

        $salvo = $DB->insert_record('chatwebgd_mensagem', $msgObj);

        if ($salvo) {
            echo '<li><span>' . $USER->firstname . ' '.get_string('disse','block_chat_webgd').':</span><p>' . $mensagem . '</p></li>';
        }

        break;

    case 'verificar':
        $Allids = optional_param_array('ids', '', PARAM_INT);
        
        $ids = '';
        if ($Allids != '') {
            foreach ($Allids as $key) {
                $aux = explode("_", $key);
                if (!$aux[0]) {
                    array_push($ids, $aux);
                }
            }
        }

        $retorno = array('nao_lidos' => array(), 'mensagens' => array(), 'novas_janelas' => array());

        $where = '';
        $params=array('user_id'=>$USER->id);
        if ($ids == '') {
            $where = ' WHERE m.para_id = :user_id AND m.lido=0 GROUP BY m.user_id ';
        } else {
            $where = ' WHERE m.user_id in (' . implode(',', $ids) . ') AND m.para_id= :user_id GROUP BY m.user_id';
        }

        $retorno['mensagens'] == '';
        $sql="SELECT m.user_id,u.firstname 
                FROM {chatwebgd_mensagem} m
                JOIN {user} u ON m.user_id = u.id ". $where;
        $verificar = $DB->get_records_sql($sql,$params,0,5);

        if ($verificar) {

            foreach ($verificar as $value) {
                $retorno['nao_lidos'][] = $value->user_id;
                //$params=array('para_id'=> $USER->id,'user_id'=>$value->user_id);
                $params=array($USER->id,$value->user_id,$value->user_id,$USER->id);
                $sql = "SELECT m.*,u.firstname 
                          FROM {chatwebgd_mensagem} m
              JOIN {user} u ON u.id = m.user_id
             WHERE m.para_id = ?  
                               AND m.user_id= ? 
                               OR m.para_id = ?  
                               AND m.user_id= ?" ;

                $selecionar = $DB->get_records_sql($sql,$params);

                $mensagem = '';
                $nomemsg = '';
                foreach ($selecionar as $msg) {
                    $mensagem .= '<li><span>' . $msg->firstname . ' '.get_string('disse','block_chat_webgd').':</span><p>' . $msg->mensagem . '</p></li>';
                    $nomemsg = $msg->firstname;
                }
                $retorno['novas_janelas'][$value->user_id] = $msg->firstname;
                $retorno['mensagens'][$value->user_id] = $mensagem;
            }
        }

        $retorno = json_encode($retorno);
        echo $retorno;
        break;

    case 'mudar_status':
        
        $user=  optional_param('user', 0, PARAM_INT);
        $param = array(1, $user, $USER->id);
        $DB->execute("UPDATE {chatwebgd_mensagem} SET lido = ? WHERE user_id = ? AND para_id = ?", $param);

        break;

    case 'atualizar_user_online':
        $timetoshowusers = 500; //Seconds default
        $now = time();
        $timefrom = $now - 3600;
        // $timefrom = 100 * floor(($now - $timetoshowusers) / 100);

        $params = array();

        $userfields = user_picture::fields('u', array('username'));
        $params['now'] = $now;
        $params['timefrom'] = $timefrom;
        $params['user_id']=$USER->id;
        $sql = "SELECT $userfields
                  FROM {user} u
                 WHERE u.lastaccess > :timefrom
                       AND u.lastaccess <= :now
                       AND u.deleted = 0
                       AND u.id <> :user_id";

        if ($users = $DB->get_records_sql($sql, $params, 0, 50)) {
            foreach ($users as $user) {
                $users[$user->id]->fullname = fullname($user);
            }
        } else {
            $users = array();
        }

        $retorno = "<ul class='list'>\n";

        if (!empty($users)) {

            foreach ($users as $user) {
                $retorno .= '<li class="listentry">';

                $retorno .= '<div class="user">';
                $retorno .= '<a href="javascript:void(0);"  id="' . $user->id . '" nome="' . $user->fullname . '" class="comecar">';
                $retorno .= $OUTPUT->user_picture($user, array('size' => 16, 'alttext' => false, 'link' => false));
                $retorno .= $user->fullname . '</a></div>';
                $retorno .= "</li>\n";
            }
        } else {
            $retorno .= "<li class=\"info\">" . get_string("none") . "</li>";
        }

        $retorno .= "</ul><div class='clearer'><!-- --></div>";

        $chatDao = new ChatWebgdDao();
        $listaGrupos = $chatDao->findGroupUser($USER->id);


        $retorno .= '<hr /><img src="' . $CFG->wwwroot . '/blocks/chat_webgd/pix/chat_grupo.png" width="40">&nbsp;&nbsp;'.get_string('meusGrupos','block_chat_webgd').' <a href="' . $CFG->wwwroot . '/blocks/chat_webgd/index.php" class="criar-grupo-chat"> + </a><ul class="list">';

        foreach ($listaGrupos as $grupo) {
            $retorno .= '<li class="listentry">';
            $retorno .= '<div class="user">';
            $retorno .= '<a href="javascript:void(0);" class="comecarGrupo" nome="' . $grupo->nome . '" id="' . $grupo->id . '_grupo">';
            $retorno .= $grupo->nome . '</a></div>';
            $retorno .= "</li>";
        }
        $retorno .= "</ul>";

        $retorno .= '</div>';

        echo $retorno;

        break;

      case 'historico':
          $id=  optional_param('id', 0, PARAM_INT);
          

          $mensagem = '';
          //$params=array('user_id'=>$USER->id,'para_id'=>$id);
          $params=array($USER->id,$id,$id,$USER->id);
      
          $sql = "SELECT m.*, u.firstname 
                    FROM {chatwebgd_mensagem} m 
                    JOIN {user} u ON m.user_id = u.id 
                   WHERE (m.user_id = ? AND m.para_id = ?) 
                         OR (m.user_id = ? AND m.para_id = ?) 
                ORDER BY m.data";
          
          $verificar = $DB->get_records_sql($sql,$params,0,20);

          if ($verificar) {

              foreach ($verificar as $value) {
                  $mensagem .= '<li><span>' . $value->firstname . ' '.get_string('disse','block_chat_webgd').':</span><p>' . $value->mensagem . '</p></li>';

              }

          }

          echo $mensagem;
          break;
}
?>
