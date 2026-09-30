<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.' can\'t run alone');

// the update type is the key that comes with update_id (message, callback_query, ...)
$updateType = null;
foreach(array_keys(is_array($update) ? $update : array()) as $key){
    if($key != 'update_id'){
        $updateType = $key;
        break;
    }
}

if(isset(BOT['allowed_updates']) && $updateType !== null)
    if(!in_array($updateType, BOT['allowed_updates']))
        throw new Exception ('invalid update');


// the callback update contain the message update 
if($updateType == 'callback_query'){
    // the clicker data
    $callFromId = $update["callback_query"]['from']['id'];
    $callId = $update["callback_query"]["id"];
    $callData = $update["callback_query"]["data"];

    // update the update to $update[updateType]{update body}
    $update['callback_query'] = $update['callback_query']['message'] ?? array();
}else{
    $callFromId = null;
    $callId = null;
    $callData = null;
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

$ent = $update[$updateType]['entities'] ?? $update[$updateType]['caption_entities'] ?? null;

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
$phid = null;
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
// the entities offset and length are in UTF-16 code units, so the text is handled as UTF-16
$realtext = null;
if($ent != null && $message !== null){
    $marks = array("code" => "`", "pre" => "```", "bold" => "*", "italic" => "_");
    $inserts = array();
    foreach($ent as $e){
        if(!isset($marks[$e['type']]))
            continue;
        $inserts[] = array($e['offset'], count($inserts), $marks[$e['type']]);
        $inserts[] = array($e['offset'] + $e['length'], count($inserts), $marks[$e['type']]);
    }
    // insert from the end, so the offsets of the rest stay valid
    usort($inserts, function($a, $b){
        return $b[0] == $a[0] ? $b[1] - $a[1] : $b[0] - $a[0];
    });

    $utf16 = mb_convert_encoding($message, 'UTF-16LE', 'UTF-8');
    foreach($inserts as $insert){
        $utf16 = substr_replace($utf16, mb_convert_encoding($insert[2], 'UTF-16LE', 'UTF-8'), $insert[0] * 2, 0);
    }
    $realtext = mb_convert_encoding($utf16, 'UTF-8', 'UTF-16LE');
}
