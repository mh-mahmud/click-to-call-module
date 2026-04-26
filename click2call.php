<?php

//if ($_SERVER['REMOTE_ADDR'] != '72.48.199.118') exit;

        if (isset($_POST['ts']) && isset($_POST['number'])) {

                $dial = $_POST['number'];
                //if (strlen($dial) == 10) $dial = '880' . $dial;
				$dial = '0'.substr($dial, -10);
                $time = time ();

                //CC-DEMO
                $agentId = '1011';
                $account_id = '550141';
                $share_key = '3aa4dea2eb942a2016bd7658bb89576f';

                $hash = MD5 ( $account_id . $agentId . $dial . "$time$share_key" );

                $obj_dial = new stdClass ();
                $obj_dial->account_id = $account_id;
                $obj_dial->agent_id = $agentId;
                $obj_dial->dial = $dial;
                $obj_dial->time = $time;
                $obj_dial->hash = $hash;
                $JSON_OBJ = json_encode ( $obj_dial );
                $request_string = base64_encode ( $JSON_OBJ );
//                $API_URL = 'https://click2call.gplex.net/' . $request_string;
		//$API_URL = 'http://192.168.10.20/click2call/' . $request_string;
		$API_URL = 'http://192.168.244.154/click2call?param=' . $request_string;
		// $API_URL = 'http://192.168.11.66/' . $request_string;
		//var_dump($API_URL);
                $response = file_get_contents ( $API_URL );
	//var_dump($response);
	//die();
                if ($response == "200 OK") {
                        echo "Calling " . $dial . "..";
                } else {
                        echo substr ( $response, 4 );
                }

                exit;
        }


?>

Number: <input type="text" id="d_number" name="d_number" value="" maxlength="11" /> &nbsp;
<button type="button" onclick="DialNumber()">Dial</button>
<script>

var xhr = new XMLHttpRequest();
xhr.open("POST", '/click2call.php', true);

//Send the proper header information along with the request
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
        // Request finished. Do processing here.
        console.log(this);
        alert(this.responseText);
		console.log(this.responseText);
        location.reload();
    }
}

function DialNumber()
{
   var ts = '<?php echo time();?>';
   var number=document.getElementById("d_number").value;
   xhr.send("number="+number+"&ts=" + ts);
}
</script>
