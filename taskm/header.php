<?php
ini_set('display_errors', 'On');

echo "<html>";
echo "<head>";
echo "<title>Taskm</title>";
echo "</head>";
echo "<body>";
echo "<h1> Task Manager</h1>";

$con=mysqli_connect('localhost','jaka','ljubimtjasokoprivec','taskman');

if (mysqli_connect_errno()) {
   echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

?>