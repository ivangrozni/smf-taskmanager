<?php include 'header.php'?>

<?php
echo "<h2> Nov Projekt </h2>";

$p_name = $_POST['p_name'];
$start = $_POST['start'];
$end = $_POST['end'];
$p_desc = $_POST['p_desc'];
$id_coord = $_POST['id_coord'];

echo date("Y-m-d H-i-s") . "<br><br>";

$q_proj = "INSERT INTO Projects (p_name, p_desc, id_coord, start, end) VALUES (\"$p_name\", \"$p_desc\", $id_coord, \"start\", \"end\") ";

if (mysqli_query($con, $q_proj)){
    $rowsAffected = mysqli_affected_rows($con);
    if ($rowsAffected == 0){
        echo "Ni sprememb";
    } elseif ($rowsAffected == 1){
        echo "Uspesno ustvarjen projekt $p_name.";
    } elseif ($rowsAffected > 1){
        echo "Spremenjenih je bilo $rowsAffected vrstic. Kontaktiraj administratorja.";
    }
    else{
        echo "Napaka! ". mysqli_error($con);
    }
}

echo "<h3> Projekt ".$p_name." ustvarjen, $id_coord! </h3>";
echo "<a href=t_index.php> nazaj na zacetek </a>";




?>

<?php include 'footer.php'?>