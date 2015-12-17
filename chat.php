<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
global $USER, $CFG, $DB, $OUTPUT;
require_login(1);
$acao = $_POST['acao'];

switch ($acao) {
    case 'inserir':
        $para = $_POST['para'];
        $mensagem = strip_tags($_POST['mensagem']);

        $msgObj = new stdClass;
        $msgObj->mensagem = $mensagem;
        $msgObj->user_id = $USER->id;
        $msgObj->para_id = $para;
        $msgObj->lido = 0;
        $msgObj->data = time();

        $salvo = $DB->insert_record('chatwebgd_mensagem', $msgObj);

        if ($salvo) {
            echo '<li><span>' . $USER->firstname . ' disse:</span><p>' . $mensagem . '</p></li>';
        }

        break;

    case 'verificar':

        $Allids = isset($_POST['ids']) ? $_POST['ids'] : '';
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
            $where = ' WHERE m.user_id in (' . implode(',', $ids) . ') AND m.para_id= :user_id GROUP BY m.user_id limit 5';
        }

        $retorno['mensagens'] == '';
        $sql="SELECT m.user_id,u.firstname FROM {$this->CFG->prefix}chatwebgd_mensagem m
									INNER JOIN {$this->CFG->prefix}user u ON m.user_id = u.id ". $where;
        $verificar = $DB->get_records_sql($sql,$params);

        if ($verificar) {

            foreach ($verificar as $value) {
                $retorno['nao_lidos'][] = $value->user_id;
                $params=array('para_id'=> $USER->id,'user_id'=>$value->user_id);

                $sql = "SELECT m.*,u.firstname FROM {$this->CFG->prefix}chatwebgd_mensagem m
							INNER JOIN {$this->CFG->prefix}user u ON u.id = m.user_id
							WHERE m.para_id = :para_id  AND m.user_id= :user_id OR m.para_id = :user_id  AND m.user_id= :para_id" ;

                $selecionar = $DB->get_records_sql($sql,$params);

                $mensagem = '';
                $nomemsg = '';
                foreach ($selecionar as $msg) {
                    $mensagem .= '<li><span>' . $msg->firstname . ' disse:</span><p>' . $msg->mensagem . '</p></li>';
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
        $user = $_POST['user'];

        $param = array(1, $user, $USER->id);
        $DB->execute("UPDATE `{$this->CFG->prefix}chatwebgd_mensagem` SET lido = ? WHERE user_id = ? AND para_id = ?", $param);

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


        $retorno .= '<hr /><img src="' . $CFG->wwwroot . '/blocks/chat_webgd/pix/chat_grupo.png" width="40">&nbsp;&nbsp;MEUS GRUPOS <a href="' . $CFG->wwwroot . '/blocks/chat_webgd/index.php" class="criar-grupo-chat"> + </a><ul class="list">';

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

          $id = $_POST['id'];

          $mensagem = '';
          $params=array('user_id'=>$USER->id,'para_id'=>$id);
          $sql = 'SELECT m.*, u.firstname FROM {$this->CFG->prefix}chatwebgd_mensagem m
  									INNER JOIN {$this->CFG->prefix}user u ON m.user_id = u.id where (m.user_id = :user_id and m.para_id = :para_id) or (m.user_id = :para_id and m.para_id = :user_id)
                    order by m.data asc limit 20';

          $verificar = $DB->get_records_sql($sql,$params);

          if ($verificar) {

              foreach ($verificar as $value) {
                  $mensagem .= '<li><span>' . $value->firstname . ' disse:</span><p>' . $value->mensagem . '</p></li>';

              }

          }

          echo $mensagem;
          break;
}
?>
