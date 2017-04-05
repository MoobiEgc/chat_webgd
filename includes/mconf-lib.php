<?php

require_once('./bbb-api.php');
require_once('../../../config.php');
require_once($CFG->dirroot . '/blocks/chat_webgd/class/ChatWebgdDao.php');

global $USER, $DB;

//$action = $_POST['action'];
$action = optional_param('action', '', PARAM_TEXT);
//$meetingId = $_POST['meetingId'];
$meetingId = optional_param('meetingId', 0, PARAM_INT);
//$meetingName = $_POST['meetingName'];
$meetingName = optional_param('meetingName', '', PARAM_TEXT);

switch ($action) {
    case 'isMeetingRunning':isMeetingRunning($meetingId, $USER);
        break;
    case 'joinAsAteendee':joinAsAtendee($meetingId, $USER->firstname);
        break;
    case 'createAndModerate':createAndModerate($meetingId, $meetingName, $USER, $DB);
        break;
}

//-------------------------------------------------

function isMeetingRunning($meetingId, $USER) {

    $bbb = new BigBlueButton();

    $itsAllGood = true;

    if ($meetingId == null) {
        // O chat é individual
        $meetingId = $USER->firstname;
    }

    try {
        $result = $bbb->isMeetingRunningWithXmlResponseArray($meetingId);
    } catch (Exception $e) {
        echo false;
        $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        echo $result['running'];
    }
}

//-------------------------------------------------

function joinAsAtendee($meetingId, $userName) {
    $bbb = new BigBlueButton();

    $joinParams = array(
        'meetingId' => $meetingId, // REQUIRED - We have to know which meeting to join.
        'username' => $userName, // REQUIRED - The user display name that will show in the BBB meeting.
        'password' => 'ap', // REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '', // OPTIONAL - string
        'userId' => '', // OPTIONAL - string
        'webVoiceConf' => ''        // OPTIONAL - string
    );

    // Get the URL to join meeting:
    $allGood = true;
    try {
        $result = $bbb->getJoinMeetingURL($joinParams);
    } catch (Exception $e) {
        echo '';
        $allGood = false;
    }

    if ($allGood == true) {
        //Output resulting URL. Send user there...
        echo $result;
    }
}

//-------------------------------------------------

function createAndModerate($meetingId, $meetingName, $USER, $DB) {

    $groupChat = true;

    $id = explode("_", $meetingId);



    if (sizeof($id) < 3) {
        //chat individual
        $meetingId = $USER->firstname;
        $meetingName = "Sala do " . $USER->firstname;
        $groupChat = false;
    }

    createMeeting($meetingId, $meetingName);
    joinAsModerator($meetingId, $USER->firstname);


    if ($groupChat) {
        // set message to chat

        @$grupoChatDao = new ChatWebgdDao();

        @$mensagemChat->user_id = $USER->id;
        @$mensagemChat->chatwebgd_grupo_id = $id[0];
        @$mensagemChat->mensagem = "Foi criada uma sala de bate papo para esse grupo! Clique "
                . "<a class='video_link' href='#' rel='$meetingId'>aqui</a> "
                . "para entrar!";

        @$mensagemChat->data_registro = time();

        @$grupoChatDao->inserirMensagem($mensagemChat);
    } else {
        $mensagem = "Foi criada uma sala de bate papo por seu amigo! Clique "
                . "<a class='video_link' href='#' rel='$meetingId'>aqui</a> "
                . "para entrar!";
        $msgObj = new stdClass;
        $msgObj->mensagem = $mensagem;
        $msgObj->user_id = $USER->id;
        $msgObj->para_id = $id[0];
        $msgObj->lido = 0;
        $msgObj->data = time();

//        $salvo = $DB->insert_record('chatwebgd_mensagem', $msgObj);
        $DB->insert_record('chatwebgd_mensagem', $msgObj);

//        if ($salvo) {
//            echo '<li><span>' . $USER->firstname . ' disse:</span><p>' . $mensagem . '</p></li>';
//        }
    }
}

//-------------------------------------------------

function createMeeting($meetingId, $meetingName) {
    $bbb = new BigBlueButton();

    $creationParams = array(
        'meetingId' => $meetingId, // REQUIRED
        'meetingName' => $meetingName, // REQUIRED
        'attendeePw' => 'ap', // Match this value in getJoinMeetingURL() to join as attendee.
        'moderatorPw' => 'mp', // Match this value in getJoinMeetingURL() to join as moderator.
        'welcomeMsg' => 'Seja bem vindo(a) a sala de conferência do Moodle Acessível do grupo Moobi!', // ''= use default. Change to customize.
        'dialNumber' => '', // The main number to call into. Optional.
        'voiceBridge' => '', // 5 digit PIN to join voice conference.
        'webVoice' => '', // Alphanumeric to join voice. Optional.
        'logoutUrl' => '', // Default in bigbluebutton.properties. Optional.
        'maxParticipants' => '-1', // Optional. -1 = unlimitted. Not supported in BBB. [number]
        'record' => 'false', // New. 'true' will tell BBB to record the meeting.
        'duration' => '0', // Default = 0 which means no set duration in minutes. [number]
    );


// Create the meeting and get back a response:
    $itsAllGood = true;
    try {
        $result = $bbb->createMeetingWithXmlResponseArray($creationParams);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
        $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        // If it's all good, then we've interfaced with our BBB php api OK:
        if ($result == null) {
            // If we get a null response, then we're not getting any XML back from BBB.
            echo "Failed to get any response. Maybe we can't contact the BBB server.";
        } else {
            // We got an XML response, so let's see what it says:
            if ($result['returncode'] == 'SUCCESS') {
                // Then do stuff ...
            } else {
                echo "Erro ao criar a webconferência";
            }
        }
    }
}

//-------------------------------------------------

function joinAsModerator($meetingId, $userName) {
    // Instatiate the BBB class:
    $bbb = new BigBlueButton();

    /* ___________ JOIN MEETING w/ OPTIONS ______ */
    /* Determine the meeting to join via meetingId and join it.
     */

    $joinParams = array(
        'meetingId' => $meetingId, // REQUIRED - We have to know which meeting to join.
        'username' => $userName, // REQUIRED - The user display name that will show in the BBB meeting.
        'password' => 'mp', // REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '', // OPTIONAL - string
        'userId' => '', // OPTIONAL - string
        'webVoiceConf' => ''        // OPTIONAL - string
    );

// Get the URL to join meeting:
    $itsAllGood = true;
    try {
        $result = $bbb->getJoinMeetingURL($joinParams);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
        $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        //Output results to see what we're getting:
        echo $result;
    }
}

?>