<?php
if ($_FILES["file"]["error"] == 0) {
  $file = "storage/99/user1/attempt1/" . $_FILES["file"]["name"];
  move_uploaded_file($_FILES["file"]["tmp_name"], $file);
  $zip = new ZipArchive;
  
  $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
  
  $r = $zip->open($file);
  
  if($r === TRUE) {
      $zip->extractTo($path);
      $zip->close();
      unlink($file);
  }
  header('Location: submit.php');
}
?>
