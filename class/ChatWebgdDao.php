<?php

class ChatWebgdDao {

    private $DB;
    private $CFG;

    function __construct() {
        global $DB, $CFG;
        $this->DB = $DB;
        $this->CFG = $CFG;
    }

    public function findUser($idUsuarioLogado = null, $idGrupo = null) {
        $sql = "SELECT u.id,u.firstname,u.lastname FROM {user} u ";

        $params = array();
        $where = "";

        if (!is_null($idUsuarioLogado)) {
            $params['userlogado'] = $idUsuarioLogado;
            $where = " WHERE u.id <> :userlogado";
        }


        if (!is_null($idGrupo)) {

            $where = ($where == "") ? " WHERE " : $where . " AND ";

            $where .= "  u.id NOT IN(
                        SELECT gu.user_id
                          FROM {chatwebgd_grupo_usuario} gu
                         WHERE gu.chatwebgd_grupo_id = :idgrupo 
                                                       AND gu.ativo = 1
                    )";
            $params['idgrupo'] = $idGrupo;
        }

        $sql .= $where;

        return $this->DB->get_records_sql($sql, $params);
    }

    public function findGroupUser($idUser, $idChat = null) {

        $params = array('iduser' => $idUser);

        $sql = "SELECT cwg.* 
                  FROM {chatwebgd_grupo} cwg
                  JOIN {chatwebgd_grupo_usuario} cwgu ON cwg.id = cwgu.chatwebgd_grupo_id
                 WHERE cwgu.user_id = :iduser 
                       AND cwgu.ativo = 1";

        if (!is_null($idChat)) {
            $params['idchat'] = $idChat;
            $sql .= " AND cwg.id = :idchat";

            return $this->DB->get_record_sql($sql, $params);
        }


        return $this->DB->get_records_sql($sql, $params);
    }

    public function findGroupById($idGrupo) {
        $params = array('idrupo' => $idGrupo);

        $sql = "SELECT cwg.* 
                  FROM {chatwebgd_grupo} cwg
                 WHERE cwg.id = :idrupo";

        return $this->DB->get_record_sql($sql, $params);
    }

    private function inserirMensagemUsuarios($idMensagemGrupo, $mensagemGrupo) {
        $sql = "SELECT cgu.id
                  FROM {chatwebgd_grupo_usuario} cgu
                 WHERE cgu.chatwebgd_grupo_id = :idgrupo 
                       AND cgu.ativo = 1";

        $params = array('idgrupo' => $mensagemGrupo->chatwebgd_grupo_id);


        $usuarios = $this->DB->get_records_sql($sql, $params);

        if ($usuarios) {

            @$mensagemUsuario->chatwebgd_mensagem_grupo_id = $idMensagemGrupo;

            foreach ($usuarios as $value) {

                $mensagemUsuario->chatwebgd_grupo_usuario_id = $value->id;

                if ($mensagemGrupo->user_id == $value->id) {
                    $mensagemUsuario->lido = 1;
                }

                if (!$this->DB->insert_record('chatwebgd_mensagem_usuario', $mensagemUsuario)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function inserirMensagem($mensagemGrupo) {

        if ($id = $this->DB->insert_record('chatwebgd_mensagem_grupo', $mensagemGrupo)) {
            if ($this->inserirMensagemUsuarios($id, $mensagemGrupo)) {
                return true;
            }
        }
        return false;
    }

    public function buscaMensagens($idGrupo, $idUsuario) {
        $sql = "  SELECT mg.id,u.id as user_id,mg.mensagem,mg.data_registro, CONCAT(u.firstname,' ',u.lastname) AS nome_usuario
                    FROM {chatwebgd_mensagem_usuario} mu
                    JOIN {chatwebgd_mensagem_grupo} mg ON mu.chatwebgd_mensagem_grupo_id = mg.id
                    JOIN {chatwebgd_grupo_usuario} gu ON gu.id = mu.chatwebgd_grupo_usuario_id
                    JOIN {user} u ON mg.user_id = u.id                                
                   WHERE mg.chatwebgd_grupo_id = :idgrupo 
                         AND gu.user_id = :iduser 
                ORDER BY mg.id DESC";

        $params = array('idgrupo' => $idGrupo, 'iduser' => $idUsuario);
        $mensagens = $this->DB->get_records_sql($sql, $params, 0, 25);
        return $mensagens;
    }

    public function buscaMensagensNaoLidas($idUsuario) {
        $sql = "SELECT mg.chatwebgd_grupo_id,webgrup.nome as nome_grupo,u.id as user_id,
                         mg.mensagem,mg.data_registro, CONCAT(u.firstname,' ',u.lastname) AS nome_usuario
                  FROM {chatwebgd_mensagem_usuario} mu
                  JOIN {chatwebgd_mensagem_grupo} mg ON mu.chatwebgd_mensagem_grupo_id = mg.id
                  JOIN {chatwebgd_grupo_usuario} gu ON gu.id = mu.chatwebgd_grupo_usuario_id
                  JOIN {user} u ON mg.user_id = u.id
                  JOIN {chatwebgd_grupo} webgrup ON mg.chatwebgd_grupo_id = webgrup.id
                 WHERE gu.user_id = :iduser 
              GROUP BY mg.chatwebgd_grupo_id 
              ORDER BY mg.id desc";

        $params = array('iduser' => $idUsuario);
        $mensagens = $this->DB->get_records_sql($sql, $params, 0, 25);
        return $mensagens;
    }

    public function marcarMensagensLidas($idGrupo, $idUsuario) {
        $sql = "SELECT gu.id
          FROM {chatwebgd_grupo_usuario} gu
         WHERE gu.user_id = :iduser 
                       AND gu.chatwebgd_grupo_id = :idgrupo";

        $params = array('iduser' => $idUsuario, 'idgrupo' => $idGrupo);
        $idUserGrupo = $this->DB->get_record_sql($sql, $params);

        if ($idUserGrupo->id) {
            $this->DB->execute("UPDATE {chatwebgd_mensagem_usuario} SET lido = 1 WHERE lido = 0 AND chatwebgd_grupo_usuario_id = " . $idUserGrupo->id);
        }
    }

    public function countMensagensNaoLidas($idGrupo, $idUsuario) {
        $sql = "  SELECT COUNT(mu.id) AS total
                    FROM {chatwebgd_mensagem_usuario} mu
                    JOIN {chatwebgd_grupo_usuario} gu ON gu.id = mu.chatwebgd_grupo_usuario_id
                   WHERE gu.user_id = :iduser 
                         AND gu.chatwebgd_grupo_id = :idgrupo 
                         AND mu.lido = 0";

        $params = array('iduser' => $idUsuario, 'idgrupo' => $idGrupo);
        $naoLidas = $this->DB->get_record_sql($sql, $params);

        return $naoLidas->total;
    }

    public function verificaUsuarioInativo($idGrupo, $idUsuario) {
        $sql = "SELECT cgu.id 
                  FROM {chatwebgd_grupo_usuario} cgu
                 WHERE cgu.user_id = :iduser 
                       AND cgu.chatwebgd_grupo_id = :idgrupo";

        $params = array('iduser' => $idUsuario, 'idgrupo' => $idGrupo);
        $usuarioInativo = $this->DB->get_record_sql($sql, $params);

        return ($usuarioInativo->id > 0);
    }

    public function ativarUsuario($idGrupo, $idUsuario) {
        return $this->DB->execute("UPDATE {chatwebgd_grupo_usuario} SET ativo = 1 WHERE user_id = " . $idUsuario . " AND chatwebgd_grupo_id = " . $idGrupo);
    }

    public function inserirUsuarioGrupo($idGrupo, $idUsuario) {
        $param = array('user' => $idUsuario, 'grupo' => $idGrupo);
        return $this->DB->execute("INSERT INTO {chatwebgd_grupo_usuario}(user_id,chatwebgd_grupo_id) value(:user,:grupo)", $param);
    }

    public function desativarUsuario($idGrupo, $idUsuario) {
        $param = array('user' => $idUsuario, 'grupo' => $idGrupo);
        return $this->DB->execute("UPDATE {chatwebgd_grupo_usuario} SET ativo = 0 WHERE user_id = :user AND chatwebgd_grupo_id = :grupo", $param);
    }

}
