 <?php
require_once('includes/handlers.php');

$handler = new PCRHandler();
$handler->uploadRepo($_POST["assignment_id"], $_POST["url"], $_POST["user"], $_POST["pass"]);

header("Location: submit.php");
?>
