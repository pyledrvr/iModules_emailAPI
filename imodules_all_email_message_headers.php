<?php
error_reporting(E_ERROR | E_WARNING);
$curl = curl_init();
echo " \n imodules_all_email_message_headers.php starting. \n";
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages?fromTimestamp=0&toTimestamp=9999703780000&startAt=0&maxResults=1",
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
$cmdstr = "delete from pitt_advance.IM_email_headers_TMP";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
OCI_Free_Statement($st);
$cmdstr = "insert into pitt_advance.IM_email_headers_TMP (id, subcommunityid, emailname, fromname, fromaddress, subjectline, preheader, sentcount, categoryname, scheduleddatetimestamp, actualsendtimestamp, dateadded) 
values (:v01, :v02, :v03, :v04, :v05, :v06, :v07, :v08, :v09, :v10, :v11, :v12)";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
for ($x = 0; $x <= $total; $x = $x+1000) {
    echo "The number is: $x \n";
$curl2 = curl_init();
curl_setopt_array($curl2, array(
  CURLOPT_URL => "https://emapi.imodules.com/v2/messages?fromTimestamp=0&toTimestamp=9999703780000&startAt=" . $x . "&maxResults=1000",
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
OCI_Bind_By_Name($st, ":v02", $vals2['subCommunityId']);
OCI_Bind_By_Name($st, ":v03", $vals2['emailName']);
OCI_Bind_By_Name($st, ":v04", $vals2['fromName']);
OCI_Bind_By_Name($st, ":v05", $vals2['fromAddress']);
OCI_Bind_By_Name($st, ":v06", $vals2['subjectLine']);
OCI_Bind_By_Name($st, ":v07", $vals2['preHeader']);
OCI_Bind_By_Name($st, ":v08", $vals2['sentCount']);
OCI_Bind_By_Name($st, ":v09", $vals2['categoryName']);
OCI_Bind_By_Name($st, ":v10", $vals2['scheduledDateTimestamp']);
OCI_Bind_By_Name($st, ":v11", $vals2['actualSendTimestamp']);
OCI_Bind_By_Name($st, ":v12", $vals2['dateAdded']);
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);
}
/*print_r(	$array['data']); */
}
} 
}
OCI_Free_Statement($st);
/* if you exec with one as the first parameter, it will only get the stats for that one. */
if (ISSET($argv[1])) {  
$cmdstr = "delete from IM_email_headers_TMP where upper(IM_email_headers_TMP.Emailname) not like '%$argv[1]%' or IM_email_headers_TMP.Emailname is null";
echo " \n $cmdstr \n";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
OCI_Free_Statement($st);}
$cmdstr = "begin pitt_advance.iModules_emailAPI.im_emails_all_headers_setup; end;";
$st = OCI_Parse($db_conn, $cmdstr);
if (!$st) {
    error_log("Unable to parse");
    error_log(OCI_Error($db_conn));
}
$exerror = OCI_Execute($st, OCI_COMMIT_ON_SUCCESS);  
echo "iModules_emailAPI.im_emails_all_headers_setup \n";
OCI_Free_Statement($st);
echo " \n imodules_all_email_message_headers.php all done. \n";
oci_close($db_conn); 
?>