<?php include 'header.php'?>

<?php
// Ni preverjanj in preprecevanja praznih polj!!!

echo "<h2> Nov Projekt</h2>";

$in_date = date("Y-m-d");
echo $in_date."<br><br>";//rrrr SVEDER
echo "<form method=\"post\" action=\"add_proj.php\">";  // IZBERI ACTION!!!

echo "Projekt: <input type=\"text\" name=\"p_name\" /> <br><br> "; //SVEDER
echo "Zacetek: <input type=\"text\" name=\"start\"> <br><br>";
echo "Konec: <input type=\"text\" name=\"end\"> <br><br>";
echo "Opis: <textarea name=\"p_desc\" rows=\"5\" cols=\"50\" > </textarea> <br><br>"; //SVEDER

// Member bo seveda potem skrita  spremenljivka
$q_mem = "SELECT * FROM Members "; // Tukaj bo potem samo name in id_prim
$result_member = mysqli_query($con, $q_mem);
echo "Koordinator: <select name=\"id_coord\">"; // izberi avtorja
while ($mem_row = mysqli_fetch_array($result_member)) {
    echo "<option value=\"".$mem_row['id_member']."\"> ".$mem_row['name']."</option>";
}
echo "</select><br><br>";//SVEDER
echo "<input type=\"submit\"  value=\"Ustvari\">";
echo "</form>";

?>

<?php include 'footer.php'?>