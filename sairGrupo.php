<?php
require_once(dirname(__FILE__) . '../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/form/ChatForm.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');
global $CFG,$DB,$USER;

echo $OUTPUT->header('themeselector');

echo $OUTPUT->heading("Sair do grupo");

$chatDao = new ChatWebgdDao();

$id = optional_param('id',null,PARAM_INT);
$aux = explode("_",$id);
if(!$aux[0]){
    $id = $aux;
}else{
    $id = $aux[0];
}
$grupo = $chatDao->findGroupUser($USER->id,$id);

if($grupo){
	if($chatDao->desativarUsuario($id,$USER->id)){
		echo get_string('voceSaiuGrupo','block_chat_webgd');
	}else{
                echo get_string('ErroAoEfetuarAcao','block_chat_webgd');
	}
}else{
	echo get_string('ErroGrupoNaoEncontrado','block_chat_webgd');
}

echo $OUTPUT->footer();
?>