<?php
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');

class ChatForm extends moodleform {

    public function definition() {
        global $CFG,$OUTPUT,$USER;
        $mform = $this->_form;
        
        $idGrupo = isset($this->_customdata['idGrupo'])? $this->_customdata['idGrupo']:null;
        $nome = isset($this->_customdata['nome'])? $this->_customdata['nome']:null;
        $validarNome = isset($this->_customdata['validarNome'])? $this->_customdata['validarNome']:true;
        
        $mform->addElement('hidden', 'id');
        
        $attrNome = array();
        
        if(!is_null($idGrupo)){
        	$attrNome['disabled'] = 'disabled';
        }
        
        $mform->addElement('text', 'nome', get_string('labelNome','block_webgd_community'),$attrNome);
        if(!is_null($idGrupo)){
        	$mform->addElement('hidden', 'id',$idGrupo);
        }
        
        if(!is_null($nome)){
        	 $mform->setDefault('nome',$nome);
        }
        
        if($validarNome){
        	$mform->addRule('nome', get_string('labelValidacaoNome','block_webgd_community'), 'required', null, 'client');
        }
        
        $chatDao = new ChatWebgdDao();
		$usuarios = $chatDao->findUser($USER->id,$idGrupo);
		
		$listaUsuarios = '';
        foreach ( $usuarios as $usuario ) {
        	$imsUser = $OUTPUT->user_picture($usuario, array('size'=>16, 'alttext'=>false, 'link'=>false));
			$listaUsuarios .= '<div class="user-select-group"><input id="id_user_'.$usuario->id.'" type="checkbox" value="'.$usuario->id.'" name="usuarios_grupo[]"><label for="id_user_'.$usuario->id.'">'.$imsUser.$usuario->firstname.' '.$usuario->lastname.'</span></div>';
		}
		
		$mform->addElement('html', '<div class="fitemtitle"><label for="id_nome">'.get_string('selecioneUsuarios','block_chat_webgd').'</label></div>');
    	$mform->addElement('html', '<div class="select-users">'.$listaUsuarios.'</div>');
        
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] = &$mform->createElement('button', 'cancelar', get_string('cancelar','block_webgd_community'), 'onclick=location.href="'.$CFG->wwwroot.'/blocks/webgd_community/index.php"');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
    }
    
    function validation($data, $files) {
        return array();
    }
}