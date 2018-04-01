<?php
/* test 506146 */
error_reporting(E_ERROR | E_WARNING);
$curl = curl_init(); 
echo " \n imodules_all_email_clicks.php starting. " . date(DATE_RFC2822) . "\n";
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages/$argv[1]/clicks?startAt=0&maxResults=1",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 180,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "accept: application/json;charset=UTF-8",
    "authorization: Bearer REPLACE_ME_WITH_access_token",
    "cache-control: no-cache",
    "postman-token: f331a4b9-aa68-90cf-bd2e-06065d7dc890"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
  echo "cURL Error #:" . $err;
} else {
$array = json_decode($response, true);
//If json_decode failed, the JSON is invalid.
if(!is_array($array)){
    throw new Exception('Received content contained invalid JSON!');
}
$total = $array['total'];
echo "total is :" . $total . "\n";
$tns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = REPLACE_ORACLE_HOST)(PORT = 1521)) (CONNECT_DATA = (SID = REPLACE_ORACLE_SID)))";
$user = "REPLACE_ORACLE_USER";
$pass = 'REPLACE_ORACLE_USER_PW';
$db_conn = oci_connect($user, $pass, $tns);
$cmdstr = "delete from pitt_advance.IM_EMAIL_clicks_TMP";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
OCI_Free_Statement($st);
$cmdstr = "insert into pitt_advance.IM_EMAIL_clicks_TMP (email_header_id, recipientId, timestamp, ipAddress) 
values (:v01, :v02, :v03, :v04)";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
for ($x = 0; $x <= $total; $x = $x+1000) {
    echo "The email_header_id is : $argv[1] and starting at: $x \n";
$curl2 = curl_init();
curl_setopt_array($curl2, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages/$argv[1]/clicks?startAt=" . $x . "&maxResults=1000",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "accept: application/json;charset=UTF-8",
    "authorization: Bearer REPLACE_ME_WITH_access_token",
    "cache-control: no-cache",
    "postman-token: f331a4b9-aa68-90cf-bd2e-06065d7dc890"
  ),
));
$response2 = curl_exec($curl2);
$err2 = curl_error($curl2);
curl_close($curl2);
if ($err2) {
  echo "cURL Error #:" . $err2;
} else {
$array2 = json_decode($response2, true);
//If json_decode failed, the JSON is invalid.
if(!is_array($array2)){
    throw new Exception('Received content contained invalid JSON!');
}
$vals1 = $array2['data'];
foreach($vals1 as $k => $vals2) {
OCI_Bind_By_Name($st, ":v01", $argv[1]);
OCI_Bind_By_Name($st, ":v02", $vals2['recipientId']);
OCI_Bind_By_Name($st, ":v03", $vals2['timestamp']);
OCI_Bind_By_Name($st, ":v04", $vals2['ipAddress']);
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);
}
/*print_r(	$array['data']); */
}
}  
OCI_Free_Statement($st);
$cmdstr = "begin pitt_advance.imodules_proc.imodules_all_email_clicks; end;";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
}
OCI_Free_Statement($st);
oci_close($db_conn); 
    echo " \n imodules_all_email_clicks.php all done. " . date(DATE_RFC2822) . "\n";
?>
