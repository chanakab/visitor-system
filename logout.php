<?php
session_start();
session_destroy();
header("Location: counter.php");
?>
