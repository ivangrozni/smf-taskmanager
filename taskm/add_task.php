<?php include 'header.php'?>

<?php
echo "<h2> Nova Zadolzitev</h2>";

$id_sec = $_POST['id_sec'];
$id_proj = $_POST['id_proj'];
$id_author = $_POST['id_author'];
$t_name = $_POST['t_name'];
$t_desc = $_POST['t_desc'];
$deadline = $_POST['deadline'];
$in_date = $_POST['in_date'];
$priority = $_POST['priority'];

echo "Datum: ".$in_date."<br><br>";
$q_str = "INSERT INTO Tasks (id_sec, id_proj, id_author, t_name, t_desc, deadline, in_date, priority) VALUES ($id_sec, $id_proj, $id_author, \"$t_name\", \"$t_desc\", \"$deadline\", \"$in_date\", $priority ) ";

if (mysqli_query($con, $q_str)) {
    $rowsAffected = mysqli_affected_rows($con);
    //echo "Random foo bar<br> $rowsAffected <br>"; //debugano
    if ($rowsAffected == 0){
        echo "Ni sprememb";
    } elseif ($rowsAffected == 1){
        echo "Uspesno sprejetje ustvarjena naloga $t_name.";
    } elseif ($rowsAffected > 1){
        echo "Spremenjenih je bilo $rowsAffected vrstic. Kontaktiraj administratorja.";
    }
    else{
        echo "Napaka! ". mysqli_error($con);
    }
}

echo "<h3> Zadolzitev ".$t_name." vnesena, $id_author! </h3>";
echo "<a href=t_index.php> nazaj na zacetek </a>";




?>

<?php include 'footer.php'?>