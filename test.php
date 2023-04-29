<?php
$csvFile = fopen("charge.csv", "r");
while(!feof($csvFile)) {
    $row = fgetcsv($csvFile);
    if (!empty($row)) {
        var_dump($row);
    }
}
