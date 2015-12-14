<?php
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');

class ChatGrupoForm extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG,$OUTPUT;
        $mform = $this->_form;
        
        
        $mform->addElement('hidden','idGrupo','',array('id'=>'idGrupo'));
		$buttonarray=array();
		$buttonarray[] = $mform->createElement('textarea', 'mensagem', '',array('rows'=>5,'style'=>'width:50%;margin-left:-170px;', 'onKeyPress'=>"TeclaEnter(event)"));
		$buttonarray[] = $mform->createElement('button', 'submitbutton', get_string('Enviar'),array('style'=>'height:100px;'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
    }
    
    function validation($data, $files) {
        return array();
    }
}