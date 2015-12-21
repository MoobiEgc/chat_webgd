<?php

require_once(dirname(__FILE__) . '../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/form/ChatForm.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');
global $CFG, $DB, $USER;
require_login(1);
echo '<style>
        .user-select-group{
            width:200px;
            float:left;
        }
        .select-users{
            border-top: 1px solid #e5e5e5;
            height:200px;
            overflow:auto;
            padding:10px;
            background:#f5f5f5;
        }    
      </style>';

echo $OUTPUT->header('themeselector');

echo $OUTPUT->heading(get_string('CriarGrupo', 'block_chat_webgd'));

$chatDao = new ChatWebgdDao();

$id = optional_param('id', 0, PARAM_INT);
$valoresDefault = array();
$linkForm = $CFG->wwwroot . '/blocks/chat_webgd/index.php';
$grupo = null;

if (!is_null($id)) {

    $grupo = $chatDao->findGroupById($id);

    if ($grupo) {
        $valoresDefault['idGrupo'] = $id;
        $valoresDefault['nome'] = $grupo->nome;
        $valoresDefault['validarNome'] = false;
        $linkForm .= '?id=' . $id;
    }
} else {
    $aux = explode("_", $id);
    if (!$aux[0]) {
        $id = $aux;
    } else {
        $id = $aux[0];
    }
}

$mform = new ChatForm($linkForm, $valoresDefault);

$usuarios = $chatDao->findUser();
if (!$id || ($id && $grupo)) {
    if ($data = $mform->get_data()) {

        $grupoChat = new stdClass();
        $grupoChat->nome = $data->nome;
        $grupoChat->data_registro = time();

        if (!$id) {
            $id = $DB->insert_record('chatwebgd_grupo', $grupoChat);

            $chatDao->inserirUsuarioGrupo($id, $USER->id);
        }

        if ($id) {
            $usuarios = optional_param_array('usuarios_grupo', array(), PARAM_TEXT);
            if (count($usuarios)) {
                foreach ($usuarios as $usuario) {
                    if ($chatDao->verificaUsuarioInativo($id, $usuario)) {
                        $chatDao->ativarUsuario($id, $usuario);
                    } else {
                        $chatDao->inserirUsuarioGrupo($id, $usuario);
                    }
                }
            }
            echo '<meta http-equiv="refresh" content="0; url=' . $CFG->wwwroot . '">';
        }
    } else {
        $mform->display();
    }
} else {
    echo get_string('ErroAoSalvarGrupo', 'block_chat_webgd');
}
echo $OUTPUT->footer();
