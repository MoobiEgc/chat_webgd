$(function () {
    $('body').delegate('.mensagem', 'keydown', function (e) {
        var idBruto = $(this).attr('id');
        var aux = idBruto.split("_");
        var to = aux[0];
        var campo = $(this);
        var mensagem = campo.val();

        if (e.keyCode == 13) {
            if (mensagem != '') {
                if (aux.length > 1) {
                    $.post(CFG_CHAT + 'chatGrupo.php', {
                        acao: 'inserir',
                        mensagem: mensagem,
                        para: to
                    }, function (retorno) {
                        $('#jan_' + idBruto + ' ul.listarGrupo').append(retorno).animate({scrollTop: $('#jan_' + idBruto + ' ul.listarGrupo')[0].scrollHeight});
                        campo.val('');
                    });
                } else {
                    $.post(CFG_CHAT + 'chat.php', {
                        acao: 'inserir',
                        mensagem: mensagem,
                        para: to
                    }, function (retorno) {
                        $('#jan_' + to + ' ul.listar').append(retorno).animate({scrollTop: $('#jan_' + to + ' ul.listar')[0].scrollHeight});
                        campo.val('');
                    });
                }

            }
        }
    });

});