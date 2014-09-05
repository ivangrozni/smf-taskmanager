<?php include 'header.php'?>
<?php

$id_prim = $_POST['id_prim'];
$q_task = "SELECT * FROM Tasks WHERE id_prim=$id_prim ";
$result_t = mysqli_query($con, $q_task);
$datum = date("Y-m-d");

while ($t_row=mysqli_fetch_array($result_t)) {
    echo "<h2>".$t_row['t_name']."</h2>";
    echo "<p>".$t_row['t_desc']."</p>";
    echo "<p>Rok: ".$t_row['deadline']."</p>";
    echo "<p>Zacetek: ".$t_row['start_date']. " Konec: $datum </p>";
}


echo "<form method=\"post\" name=\"end_task\" action=\"end_task.php\">";
echo "Komentar: <textarea name=\"comment\" rows=\"5\" cols=\"50\" > </textarea> <br><br>"; //SVEDER
echo "Nacin zakljucka: <select name=\"state\"> ";
echo "<option value=\"2\"> 2 </optin> ";
echo "<option value=\"3\"> 3 </optin> ";
echo "<option value=\"4\"> 4 </optin> ";
echo "</select> (2-uspesno, 3-neuspesno, 4-preklicano)<br><br>";//SVEDER
echo "<input type=\"hidden\" name=\"id_prim\" value=$id_prim >";
echo "<input type=\"hidden\" name=\"end_date\" value=$datum >";
echo "<input type=\"submit\" value=\"zakljuci\" >";
echo "</form>";


//$q_end="UPDATE Tasks SET state=$state, end_date=\"$e_date\", comment=\"$comment\" WHERE id_prim=$id_prim ";




?>
<?php include 'footer.php'?>