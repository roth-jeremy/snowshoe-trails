<?php

class Connection {

    private $CONFIG;
    private $CONN;

    function __construct($cfg) {
        $this->CONFIG = $cfg;
        return $this->CONN = pg_connect($this->CONFIG) or die('connection failed');
    }

    function selectQuery($query) {
        $result = pg_query($this->CONN, $query);
        if (!$result) throw new ErrorException($query);
        return $result;
    }

    function insertQuery($query) {
        pg_query($this->CONN, "BEGIN WORK");
        $result = pg_query($this->CONN, $query);
        if (!$result) {
            echo "\n" .  "RESULT ERROR:" . pg_last_error($this->CONN) . "\n";
            pg_query($this->CONN, "ROLLBACK");
        } else {
            pg_query($this->CONN, "COMMIT");
        }
    }
}

/*
  Below we define a FeatureCollection constructor helper
  (useful to build a GeoJSON flow to transfert to an OpenLayers web application)
  We may also prefer to use the PostgreSQL json_build_object function
  (see 2.2 in https://www.geomatick.com/2017/03/02/postgis-import-et-creation-des-fichiers-geojson-a-la-volee/)
*/

class TrackFeature
{
  var $type;
  var $id;
  var $title;
  var $difficulty;
  var $altDiff;
  var $description;
  var $area;
  var $geometry;

  function __construct($id, $title, $difficulty, $altDiff, $description, $area, $geometry) {
    $this->type = "Feature";
    $this->id = $id;
    $this->title = $title;
    $this->difficulty = $difficulty;
    $this->altDiff = $altDiff;
    $this->description = $description;
    $this->area = $area;
    $this->geometry = $geometry;
  }
}

class SummitFeature
{
  var $type;
  var $id;
  var $properties;
  var $highest_point;
  var $geometry;

  function __construct($id, $highest_point, $geometry){
    $this->type = "Feature";
    $this->id = $id;
    $this->properties = array("Highest Point" => $highest_point);
    $this->geometry = $geometry;
  }
}

class HutFeature
{
  var $type;
  var $id;
  var $properties;
  var $hutName;
  var $geometry;

  function __construct($id, $hutName, $geometry){
    $this->type = "Feature";
    $this->id = $id;
    $this->properties = array("Hut name" => $hutName);
    $this->geometry = $geometry;
  }
}

class FeatureCollection
{
  var $type;
  var $features;

  function __construct()  {
    $this->type = "FeatureCollection";
    $this->features = array();
  }

  function addFeature($feature) {
    array_push($this->features, $feature);
  }
}
?>
