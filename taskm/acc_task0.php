<?php include 'header.php'?>

<?php
$id_worker = $_POST['id_worker'];
$id_prim = $_POST['id_prim'];
$t_name = $_POST['t_name'];

$start_date = date("Y-m-d");
echo $start_date;



$q_str = "UPDATE Tasks SET start_date = \"$start_date\", id_worker = $id_worker, state=1 WHERE id_prim = $id_prim ";

if (mysqli_query($con, $q_str)) {
    $rowsAffected = mysqli_affected_rows($con);
    echo "Random foo bar <br> $rowsAffected <br>";
    if ($rowsAffected == 0){
        echo "Ni sprememb";
    } elseif ($rowsAffected == 1){
        echo "Uspesno sprejetje naloge.";
    } elseif ($rowsAffected > 1){
        echo "Spremenjenih je bilo $rowsAffected vrstic. Kontaktiraj administratorja.";
    }
    else{
        echo "Napaka! ". mysqli_error($con);
    }

}


echo "<h3> Zadolzitev ".$t_name." sprejeta, $id_worker! </h3>";
echo "<a href=t_index.php> nazaj na zacetek </a>";

?>

<?php include 'footer.php'?>