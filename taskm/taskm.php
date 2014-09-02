<html>
<head>
<title>Taskm</title>
</head>
<body>
<h1> Task Manager</h1>
<?php



$con=mysqli_connect(localhost,"jaka","ljubimtjasokoprivec","taskman");

if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$poizvedba = "SELECT * FROM Projects";
$result = mysqli_query($con,$poizvedba);

   echo "<table border='1'>";
   echo "<tr> <td>id</td><td>name</td></tr>";
while($row = mysqli_fetch_array($result)) {
  //echo "random";
   echo "<tr>";
   echo "<td>" .$row['id_proj']. "</td>"; 
  echo "<td>" .$row['p_name']. "</td>";
  echo "</tr>";
}
echo "</table>";


mysqli_close($con);



?>

</body>
</html>
