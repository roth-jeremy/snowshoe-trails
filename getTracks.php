<?php
error_reporting(0);

require_once 'GeoManager.php';
$conn = new Connection("host=ec2-23-21-244-254.compute-1.amazonaws.com port=5432 dbname=d6ibh7bqa237ga user=uztkmorlamgklq password=d3e72e14ea35339670a3beb428a9f863cf35464f3583e2fbd0830f7fb32d36e9");

$query = "SELECT id, title, difficulty, altdiff, description, area, ST_AsGeoJSON(the_geom) from tracks";
$result = $conn->selectQuery($query);

$i = 0;
$collection = new FeatureCollection();
while ($row = pg_fetch_row($result)) {
    $collection->addFeature(
        new TrackFeature(
            $row[0],
            $row[1],
            $row[2],
            $row[3],
            $row[4],
            $row[5],
            (json_decode($row[6])))
    );
    $i++;
}

echo json_encode($collection);

?>