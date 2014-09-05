<?php include 'header.php' ?>

<?php
//Prva stran, prikazuje projekte in zadolzitve

//$proj_id ='$_GET[proj_id]';

$result1 = mysqli_query($con, "SELECT * FROM Projects T1 INNER JOIN Members T2 ON T1.id_coord=T2.id_member ");

// (SELECT COUNT(*) FROM Tasks WHERE Tasks.id_proj=Projects.id_proj) count  // kao
// SELECT p_name AS projekt, p_desc AS opis, start, end AS rok, name AS koordinator FROM
//Projects T1 INNER JOIN Members T2 ON T1.id_coord=T2.id_member;

   echo "<h2> Projekti </h2>";
echo "<p><a href=\"add_proj_form.php\"> Nov projekt </a></p> ";
   echo "<table border='1'>";
   echo "<tr> <td><b>id_proj</b></td> <td><b>projekt</b></td><td>zacetek</td> <td>rok</td> <td>koordinator</td> <td>st taskov</td> </tr>";

while($row = mysqli_fetch_array($result1)) {
$id_proj = $row['id_proj'];
$id_member = $row['id_member'];
$result_nr = mysqli_query($con, "SELECT * FROM Tasks WHERE id_proj= $id_proj " );
$num_rows = mysqli_num_rows($result_nr);
   
   echo "<tr>";
   echo "<td>" .$row['id_proj']. "</td>"; 
   //echo "<td>" .$row['p_name'] . "</td>";
   //echo "<a href=\"submit_docs.php?prop_id=".$prop_id."\">Click </a>";
   echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
   echo "<td>" .$row['start'] . "</td>";
   echo "<td>" .$row['end'] . "</td>";
   echo "<td><a href=\"auth_desc.php?id_member=".$id_member."\">" .$row['name'] . "</a></td>";
   echo "<td>" .$num_rows . "</td>";
   echo "</tr>";
}
echo "</table>";


echo "<h2> Zadolzitve </h2>";
echo "<p><a href=\"add_task_form.php\"> Nova naloga </a></p>";
//$result2 = mysqli_query($con, "SELECT * FROM Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 ON T1.id_author=T3.id_member GROUP BY id_sec ORDER BY deadline ");  // TA JE PRAVA
$result2 = mysqli_query($con, "SELECT * FROM Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 ON T1.id_author=T3.id_member GROUP BY id_sec ORDER BY id_sec ");  // ZA DEBUG

echo "<br><table border='1'>";
echo "<tr> <td><b> id_prim </b></td> <td><b> id_sec </b></td> <td><b>zadolzitev</b></td> <td><b>projekt</b></td><td>rok</td> <td><b>stanje</b></td> <td>pomembnost</td> <td>avtor</td> </tr>"; // prvi dve za debug

while($row = mysqli_fetch_array($result2)) {
    $id_proj = $row['id_proj'];
$id_member = $row['id_member'];
    $t_id=$row['id_prim'];
    echo "<tr>";
    echo "<td>".$row['id_prim']." </td>";   // debug
    echo "<td>".$row['id_sec']." </td>";   // debug
    //echo "<td>" .$row['t_name']. "</td>";
    echo "<td><a href=\"t_desc.php?t_id=".$t_id."\">". $row['t_name'] ." </a></td>";
    //echo "<td>" .$row['p_name'] . "</td>";
    echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
    echo "<td>" .$row['deadline'] . "</td>";
    echo "<td>" .$row['state'] . "</td>";
    echo "<td>" .$row['priority'] . "</td>";
   echo "<td><a href=\"auth_desc.php?id_member=".$id_member."\">" .$row['name'] . "</a></td>";
    echo "</tr>";
}
echo "</table>";



?>

<?php include 'footer.php' ?>