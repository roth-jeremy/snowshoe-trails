<?php
error_reporting(1);


require_once 'GeoManager.php';
$conn = new Connection("host=ec2-23-21-244-254.compute-1.amazonaws.com port=5432 dbname=d6ibh7bqa237ga user=uztkmorlamgklq password=d3e72e14ea35339670a3beb428a9f863cf35464f3583e2fbd0830f7fb32d36e9");
 


$query = "SELECT id, hut_name, ST_AsGeoJSON(the_geom_pt) from huts";
$result = $conn->selectQuery($query);

$i = 0;
$collection = new FeatureCollection();
while ($row = pg_fetch_row($result)) {
    $collection->addFeature(
        new HutFeature(
            $row[0],
            $row[1],
            (json_decode($row[2])))
    );
    $i++;
}

echo json_encode($collection);

?>