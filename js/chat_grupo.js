var home = window.location;

var CFG_CHAT_GRUPO = home + '/blocks/chat_webgd/';

$(function () {

    $('.bubble').emoticons();
    $('.bubble2').emoticons();

    $("#mensagens-chat").scroll(function () {
        if ($('#mensagens-chat')[0].scrollTop + $('#mensagens-chat')[0].clientHeight == $('#mensagens-chat')[0].scrollHeight) {
            getMensagensGrupo(true);
        }
    });

    $('#id_submitbutton').click(function () {

        var mensagem = $('#id_mensagem');

        if ($.trim(mensagem.val()) != '') {

            var idGrupo = $('#idGrupo').val();

            $.ajax({
                type: "POST",
                url: 'blocks/ajax/salvarMensagem.php',
                data: 'id=' + idGrupo + '&mensagem=' + mensagem.val(),
                success: function (retorno) {
                    $('#id_mensagem').prop("disabled", false);
                    $('#id_mensagem').focus();
                    mensagem.val('');
                    var objJson = JSON.parse(retorno);
                    getMensagensGrupo();
                }
            });
        }
    });

    setInterval(function () {
        getMensagensGrupo(false);
    }, 2000);

    getMensagensGrupo(true);

});

function getMensagensGrupo(naoLidas) {
    var top = $('#mensagens-chat')[0].scrollTop;
    var topheight = $('#mensagens-chat')[0].scrollHeight;
    var idGrupo = $('#idGrupo').val();
    $.ajax({
        type: "POST",
        url: 'blocks/ajax/buscarMensagem.php',
        data: 'id=' + idGrupo + '&carregarMsg=' + naoLidas,
        success: function (retorno) {

            var objJson = JSON.parse(retorno);
            if (objJson.totalNaoLidas > 0 || objJson.carregarMsg) {
                if ($('#mensagens-chat')[0].scrollTop + $('#mensagens-chat')[0].clientHeight == $('#mensagens-chat')[0].scrollHeight) {
                    $('#mensagens-chat').html(objJson.mensagens).animate({scrollTop: $('#mensagens-chat')[0].scrollHeight});
                    $('.bubble').emoticons();
                    $('.bubble2').emoticons();
                }/*else{
                 $('#mensagens-chat').html(objJson.mensagens).scrollTop(top-($('#mensagens-chat')[0].scrollHeight-topheight));
                 }*/
                marcarMensagensLidasGrupo();
            }
        }
    });
}

function marcarMensagensLidasGrupo() {
    var idGrupo = $('#idGrupo').val();
    $.ajax({
        type: "POST",
        url: 'blocks/ajax/mensagemLida.php',
        data: 'id=' + idGrupo
    });
}

function TeclaEnter(event) {
    if (event.keyCode == 13 && event.shiftKey != true)
    {
        $('#id_mensagem').prop("disabled", true);
        $('#id_submitbutton').click();
    }
}