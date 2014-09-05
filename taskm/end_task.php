<?php include 'header.php'?>
<?php

$id_prim = $_POST['id_prim'];
$comment = $_POST['comment'];
$end_date = $_POST['end_date'];
$state = $_POST['state'];

echo date("Y-m-d H-i-s") . "<br><br>";
// Naredil bom funkcijo, ki se izvrsi po formi
$q_end="UPDATE Tasks SET state=$state, end_date=\"$end_date\", end_comment=\"$comment\" WHERE id_prim=$id_prim ";

if (mysqli_query($con, $q_end)) {
    $rowsAffected = mysqli_affected_rows($con);
    echo "Random foo bar <br> $rowsAffected <br>";
    if ($rowsAffected == 0){
        echo "Ni sprememb";
    } elseif ($rowsAffected == 1){
        echo "Uspesna zakljucitev naloge.";
    } elseif ($rowsAffected > 1){
        echo "Spremenjenih je bilo $rowsAffected vrstic. Kontaktiraj administratorja.";
    }
    else{
        echo "Napaka! ". mysqli_error($con);
    }

}


echo "<h3> Zadolzitev ".$id_prim." opravljena! </h3>";
echo "<a href=t_index.php> nazaj na zacetek </a>";



?>
<?php include 'footer.php'?>