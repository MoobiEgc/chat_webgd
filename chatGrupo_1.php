<?php
require_once(dirname(__FILE__) . '../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/form/ChatGrupoForm.php');
global $USER,$CFG;

echo $OUTPUT->header('themeselector');

echo '<style>
    .todo-chat{width:80%;height:300px;background:#f5f5f5;overflow: auto;padding:10px;}
    
    .bubble:before {
        background-color: #f2f2f2;
        box-shadow: -2px 2px 2px 0 rgba(178, 178, 178, 0.4);
        content: " ";
        display: block;
        height: 16px;
        left: -9px;
        position: absolute;
        top: 11px;
        transform: rotate(29deg) skew(-35deg);
        width: 20px;
    }
.bubble {
    background-color: #f2f2f2;
    border-color: #cdecb0;
    border-radius: 5px;
    box-shadow: 0 0 6px #b2b2b2;
    display: inline-block;
    margin: 10px;
    padding: 10px 18px;
    position: relative;
    vertical-align: top;
    width: 60%;
}
.personName {
    color: #5586e5;
    font-weight: 600;
    padding-left: 0;
}
		.personSay {
    color: #000;
    font-weight: 600;
    padding-left: 0;
}
.timeSay {
    color: #A0522D;
    margin-bottom:1px;
    font-size:10px;
    margin-bottom: -12px;
    margin-left: -8px;
}
		.bubble2:before {
    background-color: #dfeecf;
    box-shadow: -2px 2px 2px 0 rgba(178, 178, 178, 0.4);
    content: " ";
    display: block;
    float: right;
    height: 19px;
    left: 26px;
    position: relative;
    top: 11px;
    transform: rotate(205deg) skew(-35deg);
    width: 20px;
}
.bubble2 {
    background-color: #dfeecf;
    border-color: #cdecb0;
    border-radius: 5px;
    box-shadow: 0 0 6px #b2b2b2;
    display: inline-block;
    float: right;
    margin: 10px;
    padding: 10px 18px;
    position: relative;
    vertical-align: top;
    width: 60%;
}

.opcoes-grupo {
	list-style-type: none;
	font-weight:bold;
}
.opcoes-grupo li {
	float: left;
	margin-right:10px;
}
.opcoes-grupo li img{
	margin-right:5px;
}

</style>';
	  


$id = optional_param('id',0,PARAM_INT);
$chatDao = new ChatWebgdDao();

$grupo = $chatDao->findGroupUser($USER->id,$id);

if($grupo){
	echo $OUTPUT->heading(get_string('Grupo','block_chat_webgd').': '.$grupo->nome);
	
	echo '<ul class="opcoes-grupo">
			<li><a href="'.$CFG->wwwroot.'/blocks/chat_webgd/index.php?id='.$id.'"><img src="'.$CFG->wwwroot.'/theme/image.php/essential/core/1406937317/t/assignroles">'.get_string('chamarUsuario','block_chat_webgd').'</a></li>
			<li><a href="'.$CFG->wwwroot.'/blocks/chat_webgd/sairGrupo.php?id='.$id.'"><img src="'.$CFG->wwwroot.'/theme/image.php/essential/core/1406937317/t/delete">'.get_string('sairGrupo','block_chat_webgd').'</a></li>
		  </ul>';
	
	echo '<div class="todo-chat" id="mensagens-chat">';
	echo '</div>';
	$form = new ChatGrupoForm();
	$form->set_data(array('idGrupo'=>$id));
	$form->display();
	echo '<script src="'.$CFG->wwwroot.'/blocks/chat_webgd/js/emoticons/jquery.corners.js" type="text/javascript"></script>';
        echo '<script src="'.$CFG->wwwroot.'/blocks/chat_webgd/js/emoticons/jquery.emoticons.js" type="text/javascript"></script>';
        echo '<script src="'.$CFG->wwwroot.'/blocks/chat_webgd/js/chat_grupo.js" type="text/javascript"/>';
        
}else{
	redirect($CFG->wwwroot,get_string('GrupoNaoEncontrado','block_chat_webgd'));
}
 
echo $OUTPUT->footer();
?>