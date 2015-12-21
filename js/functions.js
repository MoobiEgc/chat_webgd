var CFG_CHAT = window.location.origin + '/moodle/blocks/chat_webgd/';
var CFG_BASE = window.location.origin + '/moodle/';

var title = document.title;
var blinking = false;
var intervalId = 'null';

function flashTitle(pageTitle, newMessageTitle) {
    if (document.title == pageTitle) {
        document.title = newMessageTitle;
    }
    else {
        document.title = pageTitle;
    }
}

function toggletoBlinkTitle(status) {

    if (status && intervalId == 'null') {
        intervalId = setInterval("flashTitle(title, 'Nova mensagem!')", 1500);
        //console.log('start ' + intervalId);
    } else if(!status){
        if (intervalId != 'null') {
            //console.log('clear ' + intervalId);
            clearInterval(intervalId);
            intervalID = 'null';
            document.title = title;
        }
    }
}

$(document).ready(function () {

    var janelas_chat = $('<div></div>').attr('id', 'janelas');
    $('body').append('<div style="position:absolute; top:0; right:0;" id="retorno">').append(janelas_chat);

    var janelas = new Array();



    function add_janelas(id, nome, grupo, mensagem, img) {
        var html_add = '';

        if (grupo == 1) {
            var imagem = '<a id=' + id + '_mconf target="blank" class="camera-chat"></a>';
            var chamar_user = '<a href="' + CFG_BASE + '/blocks/chat_webgd/index.php?id=' + id + '" class="chamar_grupo"></a>';
            var confirm = "'Tem certeza que deseja sair da comunidade?'";
            var sair_grupo = '<a href="' + CFG_BASE + '/blocks/chat_webgd/sairGrupo.php?id=' + id + '" class="sair_grupo" onClick="return confirm(' + confirm + ');">sair</a></li>';
            html_add = '<div class="janela" id="jan_' + id + '"><div class="topo topoGrupo" id="' + id + '"><span class="grpname">' + nome.substr(0,8)+"..."+ '</span><a href="javascript:void(0);" id="fecharGrupo">X</a>' + imagem + chamar_user + sair_grupo + '</div><div id="corpo"><div class="mensagens"><ul class="listarGrupo"></ul></div><input type="text" class="mensagem" id="' + id + '" maxlength="255" /></div></div>';
        } else {
            var imagem = '<a id=' + id + '_mconf target="blank" class="camera-chat"></a>';
            html_add = '<div class="janela" id="jan_' + id + '"><div class="topo" id="' + id + '"><img style="height:90%; margin 1px" src="'+img+'" /><span class="grpname">' + nome.substr(0,8)+"..." + '</span><a href="javascript:void(0);" id="fechar">X</a>' + imagem + '</div><div id="corpo"><div class="mensagens"><ul class="listar"></ul></div><input type="text" class="mensagem" id="' + id + '" maxlength="255" /></div></div>';
        }

        if ($('#jan_' + id).length == 0) {
            $('#janelas').append(html_add);
        }

        if (grupo == 1) {
            $('#jan_' + id + ' ul.listarGrupo').html(mensagem).animate({scrollTop: $('#jan_' + id + ' ul.listarGrupo')[0].scrollHeight});
        } else {
            $('#jan_' + id + ' ul.listar').html(mensagem).animate({scrollTop: $('#jan_' + id + ' ul.listar')[0].scrollHeight});
        }
    }
    $('.comecar').live('click', function () {
        var id = $(this).attr('id');
        var nome = $(this).attr('nome');
        var img = $(this).children('img').attr("src");

        janelas.push(id);
        for (var i = 0; i < janelas.length; i++) {
            if (janelas[i] == undefined) {
                janelas.splice(i, 1);
                i--;
            }
        }
        
        $.post(CFG_CHAT + 'chat.php', {id: id, acao: 'historico'}, function (x) {
          add_janelas(id, nome, 0, x, img);
        });
        $(this).removeClass('comecar');
        return false;
    });

    $('.comecarGrupo').live('click', function () {
        var id = $(this).attr('id');
        var nome = $(this).attr('nome');
        janelas.push(id);
        for (var i = 0; i < janelas.length; i++) {
            if (janelas[i] == undefined) {
                janelas.splice(i, 1);
                i--;
            }
        }

        add_janelas(id, nome, 1, '');
        $(this).removeClass('comecarGrupo');
        return false;
    });

    function abrir_janelas(x) {

        $('#contatos ul li a').each(function () {
            var link = $(this);
            var id = link.attr('id');
            if (id == x) {
                link.click();
            }
        });

        //lister de rolagem
        $('ul.listar').on('scroll', function () {
            marcarLido(x);
        });
    }

    function atualiza_contatos() {
        $.post(CFG_CHAT + 'chat.php', {acao: 'atualizar_user_online'}, function (x) {
            $('#contatos').html(x);
        });
    }



    var antes = -1;
    var depois = 0;
    function verificar() {

        beforeSend: antes = depois;
        
        $.post(CFG_CHAT + 'chat.php', {ids: janelas, acao: 'verificar'}, function (x) {

            if (x.nao_lidos != '') {
                var arr = x.nao_lidos;
                for (i in arr) {
                    if (!($('#jan_' + arr[i] + ' ul.listar').length && $('#jan_' + arr[i] + ' ul.listar').is(':visible'))) {
                        abrir_janelas(arr[i]);
                    }
                }
            }

            if (janelas.length > 0) {

                var mens = x.mensagens;
                if (mens != '') {

                    //brilhar titulo
                    toggletoBlinkTitle(true);

                    for (i in mens) {
                        if ($('#jan_' + i + ' ul.listar').length) {
                            $('#jan_' + i + ' ul.listar').html(mens[i]).animate({scrollTop: $('#jan_' + i + ' ul.listar')[0].scrollHeight});
                        } else {
                            //abrir janela com mensagens antigas nao lidas
                            var id = i;
                            var nome = x.novas_janelas[i];
                            janelas.push(id);
                            for (var i = 0; i < janelas.length; i++) {
                                if (janelas[i] == undefined) {
                                    janelas.splice(i, 1);
                                    i--;
                                }
                            }

                            add_janelas(id, nome, 0, mens[i]);
                        }
                    }
                }
            }
            depois += 1;

        }, 'jSON');

    }
    //verificar();

    function verificarGrupo() {
        //beforeSend: antes = depois;
        $.post(CFG_CHAT + 'chatGrupo.php', {ids: janelas, acao: 'verificar'}, function (x) {
            //console.log(x);
            if (x.nao_lidos != '') {
                var arr = x.nao_lidos;
                for (i in arr) {
                    if (!($('#jan_' + arr[i] + ' ul.listarGrupo').length && $('#jan_' + arr[i] + ' ul.listarGrupo').is(':visible'))) {
                        abrir_janelas(arr[i]);
                    }
                }
            }

            if (janelas.length > 0) {
                var mens = x.mensagens;
                if (mens != '') {
                    for (i in mens) {
                        $('#jan_' + i + ' ul.listarGrupo').html(mens[i]).animate({scrollTop: $('#jan_' + i + ' ul.listarGrupo')[0].scrollHeight});
                    }
                }
            }

        }, 'jSON');

    }
    //verificarGrupo();


    $('.janela').live('click', function () {
        var id = $(this).children('.topo').attr('id');
        marcarLido(id);
    });

    $('a#fechar').live('click', function () {
        var id = $(this).parent().attr('id');
        $(this).parent().parent().remove();
        $('#contatos a#' + id + '').addClass('comecar');

        var n = janelas.length;
        for (i = 0; i < n; i++) {
            if (janelas[i] != undefined) {
                if (janelas[i] == id) {
                    delete janelas[i];
                }
            }
        }

        $.post(CFG_CHAT + 'chat.php', {acao: 'mudar_status', user: id});
    });

    $('a#fecharGrupo').live('click', function () {
        var id = $(this).parent().attr('id');
        $(this).parent().parent().remove();
        $('#contatos a#' + id + '').addClass('comecarGrupo');

        var n = janelas.length;
        for (i = 0; i < n; i++) {
            if (janelas[i] != undefined) {
                if (janelas[i] == id) {
                    delete janelas[i];
                }
            }
        }
        var aux = id.split("_");
        var id = aux[0];
        $.post(CFG_CHAT + 'chatGrupo.php', {acao: 'mudar_status', grupo: id});
    });

    $('body').delegate('.topo', 'click', function () {
        var pai = $(this).parent();
        var isto = $(this);

        if (pai.children('#corpo').is(':hidden')) {
            isto.removeClass('fixar');
            pai.children('#corpo').toggle(100);
        } else {
            isto.addClass('fixar');
            pai.children('#corpo').toggle(100);
        }
    });

    setInterval(function () {
        //if(antes != depois){
        verificar();
        verificarGrupo();
        atualiza_contatos();
        //}
    }, 2000);

    function marcarLido(id) {


        var aux = id.split("_");
        var id = aux[0];
        if (aux.length > 1) {
            $.post(CFG_CHAT + 'chatGrupo.php', {acao: 'mudar_status', grupo: id});
        } else {
//            console.log('lido chat individual');
            $.post(CFG_CHAT + 'chat.php', {acao: 'mudar_status', user: id});

            //parar de brilhar titutlo
            toggletoBlinkTitle(false);

        }
    }

    $('body').on('click', '.camera-chat', function () {

        var id = $(this).attr('id');

        //verifica se é um grupo
        if (id.endsWith('grupo_mconf')) {
            var meetingName = $(this).siblings('.grpname').text();
            mConfGroup(id, meetingName);
        } else {
            if (id.endsWith('_mconf')) {

                mConfIndividual(id);
            }

        }

    });

    function mConfGroup(meetingId, meetingName) {
        $.post(CFG_CHAT + 'includes/mconf-lib.php', {action: 'isMeetingRunning', meetingId: meetingId, meetingName: meetingName}, function (isRunning) {

            if (isRunning == 'true') {
                joinAsAtendee(meetingId, meetingName);
            } else {
                createAndModerate(meetingId, meetingName);
            }
        });
    }
    ;

    function mConfIndividual(id) {
        $.post(CFG_CHAT + 'includes/mconf-lib.php', {action: 'isMeetingRunning', meetingId: id, meetingName: ""}, function (isRunning) {
            if (isRunning == 'true') {
//                alert('a reunião está rodando');
                joinAsAtendee(id, "");
            } else {
                createAndModerate(id, "");
            }
        });

    }
    ;

    function joinAsAtendee(meetingId, meetingName) {
        $.post(CFG_CHAT + 'includes/mconf-lib.php',
                {action: 'joinAsAteendee', meetingId: meetingId, meetingName: meetingName}, function (url) {
            console.log('Nice! sending to: ' + url);

            var win = window.open(url, 'Videochat ' + meetingName);
            if (win) {
                //Browser has allowed it to be opened
                win.focus();
            } else {
                //Broswer has blocked it
                alert('Seu navegador precisa permitir popups para esse site');
            }
        });
    }
    ;

    function createAndModerate(meetingId, meetingName) {
        $.post(CFG_CHAT + 'includes/mconf-lib.php',
                {action: 'createAndModerate', meetingId: meetingId, meetingName: meetingName}, function (url) {

            var win = window.open(url, 'Videochat ' + meetingName);
            if (win) {
                //Browser has allowed it to be opened
                win.focus();
            } else {
                //Broswer has blocked it
                alert('Seu navegador precisa permitir popups para esse site');
            }
        });
    }
    ;



    $('body').on('click', '.video_link', function () {
        var id = $(this).attr('rel');
        joinAsAtendee(id, "");
    });

});
