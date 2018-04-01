<?php
error_reporting(E_ERROR | E_WARNING);
echo " \n imodules_all_email_recipients_driver.php starting \n";
setlocale(LC_MONETARY, 'en_US');
$tns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = REPLACE_ORACLE_HOST)(PORT = 1521)) (CONNECT_DATA = (SID = REPLACE_ORACLE_SID)))";
$user = "IMODULES_BATCH";
$pass = 'REPLACE_ORACLE_USER_PW';
$conn = oci_connect($user, $pass, $tns);
$stmt = oci_parse($conn, "select email_header_id from pitt_advance.imodules_all_email_recipientsv");
oci_execute($stmt);
$nrows = oci_fetch_all($stmt, $results);
if ($nrows > 0) {
for ($i = 0; $i < $nrows; $i++) {
        echo "\n" . "\n" . $results["EMAIL_HEADER_ID"][$i] . "\n"	   ;
		$last_line = system('php ./imodules_all_email_recipients.php ' . $results["EMAIL_HEADER_ID"][$i], $retval);
		echo 'Last line of the output: ' . $last_line . "\n" . 'Return value: ' . $retval . "\n";
      }  
} else {
   echo "No email_header_ids at this time.\n";
}
oci_free_statement($stmt);
oci_close($conn);
echo " \n imodules_all_email_recipients_driver.php all done. \n";
?> 