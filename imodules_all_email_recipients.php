<?php
/* test 506146 */
error_reporting(E_ERROR | E_WARNING);
$curl = curl_init();
$email_header_id = $argv[1];
    echo " \n imodules_all_email_recipients.php starting. \n";
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages/$argv[1]/recipients?startAt=0&maxResults=1",
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
echo "total count is :" . $total . "\n";
$tns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = REPLACE_ORACLE_HOST)(PORT = 1521)) (CONNECT_DATA = (SID = REPLACE_ORACLE_SID)))";
$user = "REPLACE_ORACLE_USER";
$pass = 'REPLACE_ORACLE_USER_PW';
$db_conn = oci_connect($user, $pass, $tns);
$cmdstr = "delete from IM_EMAIL_RECIPIENTS_TMP";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
OCI_Free_Statement($st);
$cmdstr = "insert into pitt_advance.IM_EMAIL_RECIPIENTS_TMP (id, emailaddress, firstname, lastname, classyear, memberid, constituentid, dateadded, lastupdated, email_header_id) 
values (:v01, :v02, :v03, :v04, :v05, :v06, :v07, :v08, :v09, :v10)";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
for ($x = 0; $x <= $total; $x = $x+1000) {
    echo "The email_header_id is : $email_header_id and starting at: $x \n";
$curl2 = curl_init();
curl_setopt_array($curl2, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages/$argv[1]/recipients?startAt=" . $x . "&maxResults=1000",
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
OCI_Bind_By_Name($st, ":v01", $vals2['id']);
OCI_Bind_By_Name($st, ":v02", $vals2['emailAddress']);
OCI_Bind_By_Name($st, ":v03", $vals2['firstName']);
OCI_Bind_By_Name($st, ":v04", $vals2['lastName']);
OCI_Bind_By_Name($st, ":v05", $vals2['classYear']);
OCI_Bind_By_Name($st, ":v06", $vals2['memberId']);
OCI_Bind_By_Name($st, ":v07", $vals2['constituentId']);
OCI_Bind_By_Name($st, ":v08", $vals2['dateAdded']);
OCI_Bind_By_Name($st, ":v09", $vals2['lastUpdated']);
OCI_Bind_By_Name($st, ":v10", $email_header_id);
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);
}
/*print_r(	$array['data']); */
}
} 
OCI_Free_Statement($st);
$cmdstr = "begin pitt_advance.iModules_emailAPI.imodules_all_email_recipients; end;";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
OCI_Free_Statement($st);
}
    echo " \n imodules_all_email_recipients.php all done. \n";
oci_close($db_conn); 
?>
