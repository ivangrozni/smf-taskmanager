<?php include 'header.php'?>

<?php
$id_worker = $_POST['id_worker'];
$id_sec = $_POST['id_sec'];
$id_proj = $_POST['id_proj'];
$id_author = $_POST['id_author'];
$t_name = $_POST['t_name'];
$t_desc = $_POST['t_desc'];
$in_date = $_POST['in_date'];
$deadline = $_POST['deadline'];
$priority = $_POST['priority'];

$start_date = date("Y-m-d");
echo $start_date . "<br>";
echo $in_date.", ".$deadline . ", worker: " . $id_worker;

//$q_str = "INSERT INTO Tasks ()  VALUES start_date = $start_date, id_worker = $id_worker, state=1 WHERE id_prim = $id_prim ";
$q_str = "INSERT INTO Tasks (id_sec, id_proj, id_author, t_name, t_desc, in_date, deadline, priority, state, id_worker, start_date) VALUES ( $id_sec, $id_proj, $id_author, \"$t_name\", \"$t_desc\", \"$in_date\", \"$deadline\", $priority, 1, $id_worker, \"$start_date\") ";

if (mysqli_query($con, $q_str)) {
    $rowsAffected = mysqli_affected_rows($con);
    //echo "Random foo bar<br> $rowsAffected <br>"; //debugano
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