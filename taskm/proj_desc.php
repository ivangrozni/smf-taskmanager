<?php include 'header.php'?>
<?php
$proj_id = $_GET['proj_id'];
//echo $proj_id;

$q_proj = "SELECT * FROM Projects T1 INNER JOIN Members T2 on T1.id_coord=T2.id_member WHERE id_proj=$proj_id";

$qr_proj = mysqli_query($con, $q_proj);

while($row = mysqli_fetch_array($qr_proj)) {
    //echo "<br>";
    //echo $row['id_proj']."<br>";
    echo "<h2>".$row['p_name']."</h2><br>";
    echo "Zacetak projekta:\t". $row['start']."<br>";
    echo "Konec projekta:\t" . $row['end']."<br>";
    echo "Koordinator:\t" . $row['name']."<br>";
    echo "<h3>Opis</h3>\n" . $row['p_desc']."<br>";
}


echo "<br>";

$proj_name = "testni projekt zaenkrat";

//echo "Ogledujes si informacije o projeku $proj_name, ki ima id $proj_id";

    echo "<h3> Zadolzitve </h3>";

$result2 = mysqli_query($con, "SELECT * FROM Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 ON T1.id_author=T3.id_member WHERE T1.id_proj=$proj_id GROUP BY id_sec ORDER BY deadline ");

   echo "<table border='1'>";
   echo "<tr> <td><b>zadolzitev</b></td> <td><b>projekt</b></td><td>rok</td> <td><b>stanje</b></td> <td>pomembnost</td> <td>avtor</td> </tr>";

while($row = mysqli_fetch_array($result2)) {
    $p_id = $row['id_proj'];
    $t_id = $row['id_prim'];
   echo "<tr>";
   //echo "<td>" .$row['t_name']. "</td>";
   echo "<td><a href=\"t_desc.php?t_id=".$t_id."\">". $row['t_name'] ." </a></td>";
   echo "<td>" .$row['p_name'] . "</td>";
   echo "<td>" .$row['deadline'] . "</td>";
   echo "<td>" .$row['state'] . "</td>";
   echo "<td>" .$row['priority'] . "</td>";
   echo "<td>" .$row['name'] . "</td>";
   echo "</tr>";
}
echo "</table>";

echo "<br><a href=\"add_task_form.php\"> Nova zadolzitev </a> ";

?>
<?php include 'footer.php'?>