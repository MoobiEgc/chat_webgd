<?php

require_once('../../config.php');
global $USER, $CFG;
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
require_login(1);
$idGrupo = required_param('id', PARAM_TEXT);
$carregarMsg = optional_param('carregarMsg', false, PARAM_BOOL);

if ($idGrupo) {

    $grupoChatDao = new ChatWebgdDao();

    $retornar = array();

    $totalNaoLidas = $grupoChatDao->countMensagensNaoLidas($idGrupo, $USER->id);

    if ($carregarMsg) {
        $retornar['carregarMsg'] = true;
    } else {
        $retornar['carregarMsg'] = false;
    }
    $retornar['totalNaoLidas'] = $totalNaoLidas;

    if ($totalNaoLidas > 0 || $carregarMsg) {
        $mensagens = $grupoChatDao->buscaMensagens($idGrupo, $USER->id);

        $mensagem = '';
        $msg_aux = array_reverse($mensagens, TRUE);
        foreach ($msg_aux as $msg) {

            if ($msg->user_id == $USER->id) {
                $mensagem .= '<div class="bubble2">';
            } else {
                $mensagem .= '<div class="bubble">';
            }

            $mensagem .= '<span class="personName">' . $msg->nome_usuario . '</span></br>';
            $mensagem .= '<span class="personSay">' . html_entity_decode(nl2br(stripslashes($msg->mensagem))) . '</span></br>';
            $mensagem .= '<div class="timeSay">' . date('d/m/Y H:i:s', $msg->data_registro) . '</div></div>';
        }
        $retornar['mensagens'] = $mensagem;
    }

    echo json_encode($retornar);
}
?>