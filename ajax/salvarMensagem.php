<?php

require_once('../../config.php');
global $USER, $CFG;
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');
require_login(1);
$mensagem = required_param('mensagem',PARAM_TEXT);
$idGrupo = required_param('id',PARAM_TEXT);

if($mensagem && $idGrupo){
	
	$grupoChatDao = new ChatWebgdDao();
	
	$mensagemChat->user_id = $USER->id;
	$mensagemChat->chatwebgd_grupo_id = $idGrupo;
	$mensagemChat->mensagem = $mensagem;
	$mensagemChat->data_registro = time();
	
	$retorno = array();
	if($grupoChatDao->inserirMensagem($mensagemChat)){
		$retorno['enviado'] = true;
	}else{
		$retorno['enviado'] = false;
	}
	echo json_encode($retorno);
}

?>