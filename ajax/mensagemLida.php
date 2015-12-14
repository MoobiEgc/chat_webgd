<?php
require_once('../../config.php');
global $USER, $CFG;
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');

$idGrupo = required_param('id',PARAM_TEXT);

if($idGrupo){
	$grupoChatDao = new ChatWebgdDao();
	$mensagens = $grupoChatDao->marcarMensagensLidas($idGrupo,$USER->id);
}
?>
