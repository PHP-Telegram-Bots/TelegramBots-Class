<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.'can\'t run alone');

$updateType = array_keys($update)[1];

if(isset(BOT['allowed_updates']))
    if(!in_array($updateType, BOT['allowed_updates']))
        throw new Exception ('invalid update');


// the callback update contain the message update 
if($updateType == 'callback_query'){
    // the clicker data
    $callFromId = $update["callback_query"]['from']['id'];
    $callId = $update["callback_query"]["id"];
    $callData = $update["callback_query"]["data"];

    // update the update to $update[updateType]{update body}
    $update['callback_query'] = $update['callback_query']['message'];
}else{
    $data = null;
}

// global vars for all kinds of updates
$userName = $update[$updateType]["chat"]["username"]                        ?? null;
$chatId = $update[$updateType]["chat"]["id"]                                ?? null;
$FirstName = $update[$updateType]["chat"]["first_name"]            	        ?? null;
$LastName = $update[$updateType]["chat"]["last_name"]              	        ?? null;

$fromId = $update[$updateType]["from"]["id"]                		        ?? null;
$fromUserName = $update[$updateType]["from"]["username"]                    ?? null;
$fromFirstName = $update[$updateType]["from"]["first_name"]                 ?? null;
$fromLastName = $update[$updateType]["from"]["last_name"]                   ?? null;

$chatType = $update[$updateType]["chat"]["type"]                            ?? null;
$message = $update[$updateType]["text"] ?? $update[$updateType]['caption']  ?? null;
$messageId = $update[$updateType]['message_id']                             ?? null;
$title = $update[$updateType]["chat"]["title"]                              ?? null;

$cap = $update[$updateType]['caption']                                      ?? null;

// forward
$forwrdId = $update[$updateType]['forward_from']['id']                      ?? null;
$forwrdFN = $update[$updateType]['forward_from']['first_name']              ?? null;
$forwrdLN = $update[$updateType]['forward_from']['last_name']               ?? null;
$forwrdUN = $update[$updateType]['forward_from']['username']                ?? null;
$fwdFrom = $update[$updateType]['forward_from_chat']['id']                  ?? null;

// replay
$rtmid = $update[$updateType]['reply_to_message']['message_id']             ?? null;
$rtmt = $update[$updateType]['reply_to_message']['text']                    ?? null;

//Inline
$inlineQ = $update["inline_query"]["query"]                                 ?? null;
$InlineQId = $update["inline_query"]["id"]                                  ?? null;
$fromId = $update["inline_query"]["from"]["id"]						        ?? null;

$ent = $update[$updateType]['entities']                                     ?? null;

$buttons = $update[$updateType]["reply_markup"]["inline_keyboard"]          ?? null;


// general data for all kind of files
// there is also varibals for any kind below, you can use them both or delete one of them
$general_file = null;
$fileTypes = ['photo', 'video', 'document', 'audio', 'sticker', 'voice', 'video_note'];
foreach($fileTypes as $type){
    if(isset($update[$updateType][$type])){
        if($type == "photo"){
            $general_file = $update[$updateType]['photo'][count($update[$updateType][$type])-1];
        }else
            $general_file = $update[$updateType][$type];
    }
}

// Individual variables

//photo
$tphoto = $update[$updateType]['photo']                                ?? null;
if(!empty($tphoto))
    $phid = $update[$updateType]['photo'][count($tphoto)-1]['file_id'] ?? null;
//audio
$auid = $update[$updateType]['audio']['file_id']                       ?? null;
$duration = $update[$updateType]['audio']['duration']                  ?? null;
$autitle = $update[$updateType]['audio']['title']                      ?? null;
$performer = $update[$updateType]['audio']['performer']                ?? null;
//document
$did = $update[$updateType]['document']['file_id']                     ?? null;
$dfn = $update[$updateType]['document']['file_name']                   ?? null;
//video
$vidid = $update[$updateType]['video']['file_id']                      ?? null;
//voice 
$void = $update[$updateType]['voice']['file_id']                       ?? null;
//video_note
$vnid = $update[$updateType]['video_note']['file_id']                  ?? null;
//contact
$conph = $update[$updateType]['contact']['phone_number']               ?? null;
$conf = $update[$updateType]['contact']['first_name']                  ?? null;
$conl = $update[$updateType]['contact']['last_name']                   ?? null;
$conid = $update[$updateType]['contact']['user_id']                    ?? null;
//location
$locid1 = $update[$updateType]['location']['latitude']                 ?? null;
$locid2 = $update[$updateType]['location']['longitude']                ?? null;
//Sticker
$stid = $update[$updateType]['sticker']['file_id']                     ?? null;
//Venue
$venLoc1 = $update[$updateType]['venue']['location']['latitude']       ?? null;
$venLoc2 = $update[$updateType]['venue']['location']['longitude']      ?? null;
$venTit = $update[$updateType]['venue']['title']                       ?? null;
$venAdd = $update[$updateType]['venue']['address']                     ?? null;


// if thete ent in text its revers it to markdown and add `/```/*/_ to text
$realtext = null;
if($ent != null){
    $i = 0;
    $realtext = $message;
    foreach($ent as $e){
        if($e['type'] == "code")
            $replacment = "`";
        if($e['type'] == "pre")
            $replacment = "```";
        if($e['type'] == "bold")
            $replacment = "*";
        if($e['type'] == "italic")
            $replacment = "_";
        
        $realtext = Helpers::entToRealTxt($realtext, $replacment, $e['offset'], $e['length'], $i);
        $i += strlen($replacment)*2;
    }
}
