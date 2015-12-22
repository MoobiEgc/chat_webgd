<?php

require_once('../../config.php');
global $USER, $CFG;
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
require_login(1);

$acao = optional_param('acao', '', PARAM_TEXT);
switch ($acao) {
    case 'inserir':
        $grupoChatDao = new ChatWebgdDao();

        $mensagemChat->user_id = $USER->id;
        $mensagemChat->chatwebgd_grupo_id = optional_param('para', 0, PARAM_INT);
        $mensagemChat->mensagem = strip_tags(optional_param('mensagem', '', PARAM_TEXT));
        $mensagemChat->data_registro = time();

        if ($grupoChatDao->inserirMensagem($mensagemChat)) {
            echo '<li><span>' . $USER->firstname . ' '.get_string('disse','block_chat_webgd').':</span><p>' . $mensagemChat->mensagem . '</p></li>';
        }
        break;

    case 'verificar':
        $retorno = array('nao_lidos' => array(), 'mensagens' => array(), 'novas_janelas' => array());
        $Allids = optional_param_array('ids', '', PARAM_INT);

        $ids = array();
        $nomeGrupo = array();

        if ($Allids != '') {
            foreach ($Allids as $key) {
                $aux = explode("_", $key);
                if ($aux[0]) {
                    array_push($ids, $aux[0]);
                }
            }
        }

        $grupoChatDao = new ChatWebgdDao();
        $gruposNovos = $grupoChatDao->buscaMensagensNaoLidas($USER->id);
        foreach ($gruposNovos as $groupbygroup) {
            array_push($ids, $groupbygroup->chatwebgd_grupo_id);
            $nomeGrupo[$groupbygroup->chatwebgd_grupo_id] = $groupbygroup->nome_grupo;
        }
        $ids = array_unique($ids);

        foreach ($ids as $idGrupo) {
            $grupoChatDao = new ChatWebgdDao();

            $totalNaoLidas = $grupoChatDao->countMensagensNaoLidas($idGrupo, $USER->id);

            if ($totalNaoLidas > 0) {
                $retorno['nao_lidos'][] = $idGrupo . "_grupo";
                $mensagens = $grupoChatDao->buscaMensagens($idGrupo, $USER->id);

                $mensagem = '';
                $msg_aux = array_reverse($mensagens, TRUE);
                foreach ($msg_aux as $msg) {
                    $mensagem .= '<li><span>' . $msg->nome_usuario . ' '.get_string('disse','block_chat_webgd').':</span><p>' . html_entity_decode(nl2br(stripslashes($msg->mensagem))) . '</p></li>';
                }
                if ($nomeGrupo[$idGrupo]) {
                    $retorno['novas_janelas'][$idGrupo . "_grupo"] = $nomeGrupo[$idGrupo];
                }
                $retorno['mensagens'][$idGrupo . "_grupo"] = $mensagem;
            }
        }
        $retorno = json_encode($retorno);
        echo $retorno;
        break;
    case 'mudar_status':
        $idGrupo = optional_param('grupo', 0, PARAM_INT);


        $grupoChatDao = new ChatWebgdDao();
        $grupoChatDao->marcarMensagensLidas($idGrupo, $USER->id);
        break;
}
?>

