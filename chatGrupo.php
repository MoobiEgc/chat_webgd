<?php

require_once('../../config.php');
global $USER, $CFG;
require_once($CFG->dirroot.'/blocks/chat_webgd/class/ChatWebgdDao.php');

$acao = $_POST['acao'];
	
	switch($acao){
                case 'inserir':
                        $grupoChatDao = new ChatWebgdDao();
	
                        $mensagemChat->user_id = $USER->id;
                        $mensagemChat->chatwebgd_grupo_id = $_POST['para'];
                        $mensagemChat->mensagem = strip_tags($_POST['mensagem']);
                        $mensagemChat->data_registro = time();

                        if($grupoChatDao->inserirMensagem($mensagemChat)){
                            echo '<li><span>'.$USER->firstname.' disse:</span><p>'.$mensagemChat->mensagem.'</p></li>';
                        }                       
		break;
                
                case 'verificar':
                    $retorno = array('nao_lidos'=>array(),'mensagens'=>array(), 'novas_janelas'=>array());
                    
                    $Allids = isset($_POST['ids'])?$_POST['ids']:'';
                    $ids = array();
                    $nomeGrupo = array();
                    
                    if($Allids != ''){
                        foreach ($Allids as $key) {
                            $aux = explode("_",$key);                        
                            if($aux[0]){
                                array_push($ids, $aux[0]);                            
                            }
                        }                                                           
                    }
                    
                    $grupoChatDao = new ChatWebgdDao();
                    $gruposNovos = $grupoChatDao->buscaMensagensNaoLidas($USER->id);
                    foreach($gruposNovos as $groupbygroup){
                        array_push($ids, $groupbygroup->chatwebgd_grupo_id);
                        $nomeGrupo[$groupbygroup->chatwebgd_grupo_id] = $groupbygroup->nome_grupo;
                    }
                    $ids = array_unique($ids);
                    
                    foreach($ids as $idGrupo){
                                $grupoChatDao = new ChatWebgdDao();

                                $totalNaoLidas = $grupoChatDao->countMensagensNaoLidas($idGrupo,$USER->id);
                                
                                if($totalNaoLidas > 0){
                                        $retorno['nao_lidos'][] = $idGrupo."_grupo";
                                        $mensagens = $grupoChatDao->buscaMensagens($idGrupo,$USER->id);

                                        $mensagem = '';
                                        $msg_aux = array_reverse($mensagens, TRUE);
                                        foreach ( $msg_aux as $msg ){
                                                $mensagem .= '<li><span>'.$msg->nome_usuario.' disse:</span><p>'.html_entity_decode (nl2br(stripslashes($msg->mensagem))).'</p></li>';
                                        }
                                        if($nomeGrupo[$idGrupo]){
                                            $retorno['novas_janelas'][$idGrupo."_grupo"] = $nomeGrupo[$idGrupo];
                                        }
                                        $retorno['mensagens'][$idGrupo."_grupo"] = $mensagem;
                                }
                        }
                    $retorno = json_encode($retorno);
                    echo $retorno;
            break;
            case 'mudar_status':
                   
                    $idGrupo = $_POST['grupo'];

                    $grupoChatDao = new ChatWebgdDao();
                    $grupoChatDao->marcarMensagensLidas($idGrupo,$USER->id);
            break;
    }

?>

