<?php
error_reporting(E_ERROR | E_WARNING);
include("imodules_all_email_local.php"); 
echo " \n imodules_all_email_clicks_driver.php starting. \n";
setlocale(LC_MONETARY, 'en_US');
$conn = oci_connect($user, $pass, $tns);
$stmt = oci_parse($conn, "select email_header_id from $schema.imodules_all_email_clicksv");
oci_execute($stmt);
$nrows = oci_fetch_all($stmt, $results);
if ($nrows > 0) {
for ($i = 0; $i < $nrows; $i++) {
        echo "\n" . "\n" . $results["EMAIL_HEADER_ID"][$i] . "\n"	   ;
		$last_line = system('php ./imodules_all_email_clicks.php ' . $results["EMAIL_HEADER_ID"][$i], $retval);
		echo 'Last line of the output: ' . $last_line . "\n" . 'Return value: ' . $retval . "\n";
      }  
} else {
   echo "No email_header_ids at this time.\n";
}
oci_free_statement($stmt);
oci_close($conn);
    echo " \n imodules_all_email_opens_driver.php all done. \n";
?> 