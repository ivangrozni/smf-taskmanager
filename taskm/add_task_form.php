<?php include 'header.php'?>

<?php
// Ni preverjanj in preprecevanja praznih polj!!!

echo "<h2> Nova Zadolzitev</h3>";

$in_date = date("Y-m-d");
echo $in_date."<br><br>";//rrrr SVEDER

echo "<form method=\"post\" action=\"add_task.php\">";  // IZBERI ACTION!!!

echo "Zadolzitev: <input type=\"text\" name=\"t_name\" /> <br><br> "; //SVEDER
//echo "<input type=\"date\" value=\"2010-12-16;\"> <br><br>"
echo "Rok: <input type=\"text\" name=\"deadline\"> <br><br>";
//echo "Rok: <input type=\"text\" name=\"cal1Date\" id=\"cal1Date\" autocomplete=\"off\" size=\"35\"  /> <br><br>"; //SVEDER
// echo(($_GET['cal1Date']) ? urldecode($_GET['cal1Date']) : '') 
//value=". (($_GET['cal1Date']) ? urldecode($_GET['cal1Date']) : '') .
echo "<div id=\"cal1Container\"></div>";

echo "Opis: <textarea name=\"t_desc\" rows=\"5\" cols=\"50\" > </textarea> <br><br>"; //SVEDER

$q_proj = "SELECT * FROM Projects ";
$result_proj = mysqli_query($con, $q_proj);
echo "Projekt: <select name=\"id_proj\"> ";
while ($proj_row = mysqli_fetch_array($result_proj)) {
    echo "<option value=\"".$proj_row['id_proj']."\">".$proj_row['p_name']." </option> ";
}
echo "</select><br><br>";//SVEDER

$q_mem = "SELECT * FROM Members "; // Tukaj bo potem samo name in id_prim
$result_member = mysqli_query($con, $q_mem);
echo "Avtor: <select name=\"id_author\">"; // izberi avtorja
while ($mem_row = mysqli_fetch_array($result_member)) {
    echo "<option value=\"".$mem_row['id_member']."\"> ".$mem_row['name']."</option>";
}
echo "</select><br><br>";//SVEDER

echo "Pomembnost: <select name=\"priority\"> ";
echo "<option value=\"1\"> 1 </optin> ";
echo "<option value=\"2\"> 2 </optin> ";
echo "<option value=\"3\"> 3 </optin> ";
echo "</select><br><br>";//SVEDER


//Poiscimo max id_prim
$q_prim = "SELECT MAX(id_prim) AS maxus FROM Tasks ";
$result_max = mysqli_query($con, $q_prim);
$row_prim = mysqli_fetch_array($result_max);
echo "maximalni id_prim: ".$row_prim['maxus']."<br><br>";//SVEDER
// Tukaj bi potrebovali mal error handlinga, za primer prazne baze...
// Vsaj en if, ki id_prim postavi na 1, ce nic ne dobi iz baze...
// da picku struze v radiator
$id_prim = $row_prim['maxus'] + 1;

// tole bo potem hidden, ker doloca userja oz workerja
echo "<input type=\"hidden\" name=\"id_sec\" value=$id_prim >";
echo "<input type=\"hidden\" name=\"in_date\" value=$in_date >";
echo "<input type=\"submit\"  value=\"Ustvari\">";
echo "</form>";






?>

<?php include 'footer.php'?>