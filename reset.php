<?php
$mysqlDatabaseName ='deco3801';
$mysqlUserName ='deco3801';
$mysqlPassword ='hh2z2WG2q';
$mysqlHostName ='localhost';
$mysqlImportFilename ='sql.sql';
$con = mysql_connect($mysqlHostName, $mysqlUserName, $mysqlPassword);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

$sql = "DROP TABLE `Assignments`, `Comment`, `Course`, `Files`, `Question`, `Review`, `Submission`, `Testing`";
mysql_query($sql,$con);
mysql_close($con);

//DONT EDIT BELOW THIS LINE
//Export the database and output the status to the page
$command='mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
exec($command,$output=array(),$worked);
switch($worked){
    case 0:
        echo 'Import file <b>' .$mysqlImportFilename .'</b> successfully imported to database <b>' .$mysqlDatabaseName .'</b>';
        break;
    case 1:
        echo 'There was an error during import. Please make sure the import file is saved in the same folder as this script and check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' .$mysqlDatabaseName .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' .$mysqlUserName .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' .$mysqlHostName .'</b></td></tr><tr><td>MySQL Import Filename:</td><td><b>' .$mysqlImportFilename .'</b></td></tr></table>';
        break;
}
?>