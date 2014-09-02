<?php include 'header.php'?>

<?php

$t_id = $_GET['t_id'];

$t_querry = "SELECT * FROM Tasks T1 INNER JOIN Members T2 ON T1.id_author=T2.id_member INNER JOIN Projects T3 ON T1.id_proj=T3.id_proj WHERE id_prim=$t_id";

$t_result = mysqli_query($con, $t_querry);
while($row = mysqli_fetch_array($t_result)){

    $state = $row['state'];
    $id_prim = $row['id_prim'];
    $t_name = $row['t_name'];

    echo "<h2>".$row['t_name']."</h2><br>";
    echo "Stanje:\t".$state."<br>";
    echo "Projekt: " . $row['p_name'] . "<br>";
    echo "Rok: " . $row['deadline'] . "<br>";
    echo "Avtor: " . $row['name'] . "    Datum vnosa zadolzitve:  " . $row['in_date'] . "<br>";
    echo "<h3>Opis: </h3>" .$row['t_desc'] . "<br>";

    if ($state == 0){
        
        echo "<form method=\"post\" action=\"acc_task0.php\">";
        echo "<select name=\"id_worker\">";
        echo "<option value=\"1\">Marx</option>";
        echo "<option value=\"2\">Lenin</option>";
        echo "<option value=\"3\">Rosa</option>";
        echo "<option value=\"4\">Kautsky</option>";
        echo "<option value=\"5\">Castro</option>";
        echo "<option value=\"6\">Marko P</option>";
        echo "<option value=\"7\">K Stane</option>";
        echo "<option value=\"12\">Novelli</option>";
        echo "</select>";
        // tole bo potem hidden, ker doloca userja oz workerja
        echo "<input type=\"hidden\" name=\"id_prim\" value=$id_prim >";
        echo "<input type=\"hidden\" name=\"t_name\" value=$t_name >";
        echo "<input type=\"submit\"  value=\"Sprejmem\">";
        echo "</form>";

        //echo "Sprejmi zadolzitev - link/gumb, ki pripelje do forme + stanje = 0";

    }
    elseif ($state == 1){
        //echo "<h3>Stanje je ena!</h3>"; //debuged! :)
        $id_sec = $row['id_sec']; // Izpise se izvajalce

        $result_nr = mysqli_query($con, "SELECT * FROM Tasks T1 INNER JOIN Members T2 ON T1.id_worker=T2.id_member WHERE id_sec= $id_sec " );
        //echo "<h3>query gre skozi!</h3>"; // debuged
        $num_rows = mysqli_num_rows($result_nr);
        //echo $num_rows; // debuged
        echo "<h4>Izvajalci (".$num_rows."): </h4> <ul>";
        while ($inrow = mysqli_fetch_array($result_nr)) {
            echo "<li>" . $inrow['name'] . " ( ".$inrow['start_date']." )</li>";
        }
        echo "</ul>";

        // FORMA za vnos podatkov
        echo "<form method=\"post\" action=\"acc_task1.php\">";
        echo "<select name=\"id_worker\">";
        echo "<option value=\"1\">Marx</option>";
        echo "<option value=\"2\">Lenin</option>";
        echo "<option value=\"3\">Rosa</option>";
        echo "<option value=\"4\">Kautsky</option>";
        echo "<option value=\"5\">Castro</option>";
        echo "<option value=\"6\">Marko P</option>";
        echo "<option value=\"7\">K Stane</option>";
        echo "<option value=\"12\">Novelli</option>";
        echo "</select>";
        // tole bo potem hidden, ker doloca userja oz workerja
        //echo "<input type=\"hidden\" name=\"id_prim\" value=$id_prim >"; // tale je auto inc...
        echo "<input type=\"hidden\" name=\"id_sec\" value=".$row['id_sec']." >";
        echo "<input type=\"hidden\" name=\"id_proj\" value=".$row['id_proj']." >";
        echo "<input type=\"hidden\" name=\"id_author\" value=".$row['id_author']." >";
        echo "<input type=\"hidden\" name=\"t_name\" value=".$row['t_name']." >";
        echo "<input type=\"hidden\" name=\"t_desc\" value=".$row['t_desc']." >";
        echo "<input type=\"hidden\" name=\"in_date\" value=".$row['in_date']." >";
        echo "<input type=\"hidden\" name=\"deadline\" value=".$row['deadline']." >";
        echo "<input type=\"hidden\" name=\"priority\" value=".$row['priority']." >";
        echo "<input type=\"submit\"  value=\"Sprejmem\">";
        echo "</form>";
        //echo "Sprejmi zadolzitev - link/gumb, ki pripelje do forme stanje = 1";
    }
    elseif ($state>=2){ // izpise se izvajalce in njihove commente in kdaj so zakljucili
        $id_sec = $row['id_sec']; //
        $result_nr = mysqli_query($con, "SELECT * FROM Tasks T1 INNER JOIN Members T2 ON T1.id_worker=T2.id_member WHERE id_sec= $id_sec " );
        $num_rows = mysqli_num_rows($result_nr);

        echo "<h4>Izvajalci (".$num_rows."): </h4> <ul>";
        while ($inrow = mysqli_fetch_array($result_nr)) {
            echo "<li>" . $inrow['name'] . " ( od: ".$inrow['start_date']." do: ".$inrow['end_date'].") - ".$inrow['end_comment']."  </li>";
        }
        echo "</ul>";
        


    }
    

}

echo "<br><br>Fajn bi bilo imeti se koordinatorja projekta zravn LEFT JOIN?";



//echo "Zacetak projekta:\t". $row['start']."<br>";
//echo "Konec projekta:\t" . $row['end']."<br>";
//echo "Koordinator:\t" . $row['name']."<br>";
//echo "<h3>Opis</h3>\n" . $row['p_desc']."<br>";


?>

<?php include 'footer.php'?>