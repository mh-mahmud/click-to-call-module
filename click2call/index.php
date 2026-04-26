<?php

//$debug = 1;

include('accounts.php');

$param = isset($_REQUEST['param']) ? trim($_REQUEST['param']) : '';
$request = json_decode(base64_decode($param));
//var_dump(print_r($request));
//var_dump($_REQUEST);    
//var_dump($_POST);
/*
$str = base64_decode($param);

echo $str;echo "\n";

//$p = json_decode('{"account_id":"901007","agent_id":"3005","dial":"19725342207","time":"45282017124520","hash":"e4bdcb43172bf340cae41418f9991925"}');
//print_r($p);
print_r($request);
*/
//die($request);

if ($request && isset($request->account_id) && isset($request->dial) && isset($request->time)) {
    if (strlen($request->account_id) != 6) send_error(401, "Unauthorized");

    $time_diff = time() - $request->time;

    if ($time_diff < -3600 || $time_diff > 3600) send_error(480, "The URL has expired");

    $account = isset($accounts[$request->account_id]) ? $accounts[$request->account_id] : null;
    //echo $account;
    if ($account) {
        $hash = md5($request->account_id . $request->agent_id . $request->dial . $request->time . $account['key']);
        if ($hash == $request->hash) {

            if (strlen($request->dial) <= 8) send_error(482, "Dial number is too small");

            if (!ctype_digit($request->dial)) send_error(483, "Dial number is invalid");

            //$request->agent_id = '2004';
            if (strlen($request->agent_id) != 4 || !ctype_digit($request->agent_id)) send_error(481, "Agent ID is invalid");

            include('db_conf.php');
            db_conn($account['db_suffix']);

            $dbName = "cc" . $account['db_suffix'];

            $sql = "SELECT agent_id, seat_id, skillout  FROM $dbName".".agents WHERE agent_id='$request->agent_id'";
            $agent = db_select($sql);

            if (empty($agent)) send_error(484, "Agent $request->agent_id not found");

            if (empty($agent->seat_id)) send_error(470, "Agent $request->agent_id not in service");

            //$msg = "WWW\r\nType: SIP_NOTIFY\r\nseat_id: \r\nagent_id: $request->agent_id\r\nskill_id: $request->skillout\r\nevent: DIAL\r\nevent_body: $request->dial";
            $msg = "WWW\r\nType: SIP_NOTIFY\r\nseat_id: \r\nagent_id: $request->agent_id\r\nskill_id: AL\r\nevent: DIAL\r\nevent_body: $request->dial";
            //$msg = "WWW\r\nType: SIP_NOTIFY\r\nseat_id: 102\r\nagent_id: $request->agent_id\r\nskill_id: AB\r\nevent: DIAL\r\nevent_body: $request->dial";
            //var_dump($msg);
			$db_notification = db_select("SELECT db_ip, db_port FROM $dbName".".settings");
            if ($db_notification) {
				//var_dump("SELECT updatedb('$db_notification->db_ip', '$db_notification->db_port', '$msg');");
                //db_select("SELECT updatedb('$db_notification->db_ip', '$db_notification->db_port', '$msg');");
				if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
                    //return false;
                }

                if ( ! socket_sendto($sock, $msg , strlen($msg) , 0 , $db_notification->db_ip , $db_notification->db_port)) {
                    //return false;
                }
            }

            //echo "200 OK";
            send_error(200, "OK");
            exit;
        }
    }
}

send_error(401, "Unauthorized");


function send_error($code, $msg)
{
    echo "$code $msg";
    exit();
}
