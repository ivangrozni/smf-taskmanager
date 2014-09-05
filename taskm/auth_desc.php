<?php include 'header.php'?>
<?php

//predigra
$id_member = $_GET['id_member'];

//queryji za tabele:
$sql_worker_open = "SELECT * from Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 on T1.id_author=T3.id_member WHERE id_worker=$id_member AND state = 1 ORDER BY deadline";

$sql_worker_done = "SELECT * from Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 on T1.id_author=T3.id_member WHERE id_worker=$id_member AND state = 2 ORDER BY deadline";

$sql_worker_drop = "SELECT * from Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 on T1.id_author=T3.id_member WHERE id_worker=$id_member AND state = 3 ORDER BY deadline";

$sql_worker_fail = "SELECT * from Tasks T1 INNER JOIN Projects T2 ON T1.id_proj=T2.id_proj INNER JOIN Members T3 on T1.id_author=T3.id_member WHERE id_worker=$id_member AND state = 4 ORDER BY deadline";

$result_worker_open = mysqli_query($con, $sql_worker_open);
$result_worker_done = mysqli_query($con, $sql_worker_done);
$result_worker_drop = mysqli_query($con, $sql_worker_drop);
$result_worker_fail = mysqli_query($con, $sql_worker_fail);

$glava_tabele = "<tr><td><b>primarni id</b></td><td><b>sekundarni id</b></td><td><b>rok</b></td><td><b>zadolzitev</b></td><td><b>projekt</b></td><td><b>pomembnost</b></td> <td><b>avtor</b></td> <td><b>zakljuci</b></td> </tr>";

//podatki o memberju (kasneje bo vec kot samo ime)
$sql_basic_info = "SELECT name FROM Members WHERE id_member = $id_member";
$resultbasic_info = mysqli_query($con, $sql_basic_info);
while ($row = mysqli_fetch_array($resultbasic_info)) {
    $member_name = $row['name'];
}

echo "<h2>Epska zgodovina osebe " .$member_name ."</h2>";

//tabela neopravljenih taskov memberja
echo "<h3>Neopravljeni taski:</h3>";
//preveri, ce query sploh kaj vrne
$num_rows = mysqli_num_rows($result_worker_open);
if($num_rows == 0)
{
    echo "<br>Ni nalog za prikaz!";
}
    else 
{
//zacetek tabele:
    echo "<table border='1'>";
    echo "$glava_tabele";

//zacetek zanke
$result_worker_open = mysqli_query($con, $sql_worker_open);
    while ($row = mysqli_fetch_array($result_worker_open)) {
    $id_proj = $row['id_proj'];
    $id_member = $row['id_member'];
//glava zanke
    echo "<tr>";
    echo "<td>" .$row['id_prim']."</td>";
    echo "<td>" .$row['id_sec']."</td>";
    echo "<td>" .$row['deadline']."</td>";
    echo "<td>" .$row['t_name']."</td>";
    echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
    echo "<td>" .$row['priority']."</td>";
    echo "<td>" .$row['name']."</td>";
    echo "<td><form method=\"post\" name=\"end_task\" action=\"end_task_form.php\"> <input type=\"hidden\" name=\"id_prim\" value=".$row['id_prim']."><input type=\"submit\" value=\"zakljuci\" style=\"height:5px; width:75px;\"></form></td>";
    echo "</tr>";
}
    echo "</table>";
}


//tabela opravljenih taskov memberja
    echo "<h3>Opravljeni taski:</h3>";
//preveri, ce query sploh kaj vrne
$num_rows = mysqli_num_rows($result_worker_done);
if($num_rows == 0)
{
    echo "<br>ojoj, ta proletarec pa se ni opravil nobene naloge!";
}
    else 
{
//zacetek tabele:
    echo "<table border='1'>";
    echo $glava_tabele;

//zacetek zanke
$result_worker_done = mysqli_query($con, $sql_worker_done);
    while ($row = mysqli_fetch_array($result_worker_done)) {
    $id_proj = $row['id_proj'];
    $id_member = $row['id_member'];
//glava zanke
    echo "<tr>";
    echo "<td>" .$row['id_prim']."</td>";
    echo "<td>" .$row['id_sec']."</td>";
    echo "<td>" .$row['deadline']."</td>";
    echo "<td>" .$row['t_name']."</td>";
    echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
    echo "<td>" .$row['priority']."</td>";
    echo "<td>" .$row['name']."</td>";
    echo "</tr>";
}
    echo "</table>";
}


//tabela skenslanih taski memberja
    echo "<h3>Skenslani taski:</h3>";
//preveri, ce query sploh kaj vrne
$num_rows = mysqli_num_rows($result_worker_drop);
if($num_rows == 0)
{
    echo "<br>Ni podatkov za prikaz.";
}
    else 
{
//zacetek tabele:
    echo "<table border='1'>";
    echo $glava_tabele;

//zacetek zanke
$result_worker_drop = mysqli_query($con, $sql_worker_drop);
    while ($row = mysqli_fetch_array($result_worker_drop)) {
    $id_proj = $row['id_proj'];
    $id_member = $row['id_member'];
//glava zanke
    echo "<tr>";
    echo "<td>" .$row['id_prim']."</td>";
    echo "<td>" .$row['id_sec']."</td>";
    echo "<td>" .$row['deadline']."</td>";
    echo "<td>" .$row['t_name']."</td>";
    echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
    echo "<td>" .$row['priority']."</td>";
    echo "<td>" .$row['name']."</td>";
    echo "</tr>";
}
    echo "</table>";
}


//tabela failanih taskov memberja
    echo "<h3>Failani taski:</h3>";
//preveri, ce query sploh kaj vrne
$num_rows = mysqli_num_rows($result_worker_fail);
if($num_rows == 0)
{
    echo "<br>pohvalno, nobenega faila!";
}
    else 
{
//zacetek tabele:
    echo "<table border='1'>";
    echo $glava_tabele;

//zacetek zanke
$result_worker_done = mysqli_query($con, $sql_worker_fail);
    while ($row = mysqli_fetch_array($result_worker_fail)) {
    $id_proj = $row['id_proj'];
    $id_member = $row['id_member'];
//glava zanke
    echo "<tr>";
    echo "<td>" .$row['id_prim']."</td>";
    echo "<td>" .$row['id_sec']."</td>";
    echo "<td>" .$row['deadline']."</td>";
    echo "<td>" .$row['t_name']."</td>";
    echo "<td><a href=\"proj_desc.php?proj_id=".$id_proj."\">". $row['p_name'] ." </a></td>";
    echo "<td>" .$row['priority']."</td>";
    echo "<td>" .$row['name']."</td>";
    echo "</tr>";
}
    echo "</table>";
}


?>
<?php include 'footer.php'?>