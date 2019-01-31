<?php
require_once 'GeoManager.php';

// This is the URL end point to get the routes (it seems there are less then 200 routes)
$url = "https://api.camptocamp.org/routes?a=14384&act=snowshoeing&pl=fr&limit=200";
$str_routes = file_get_contents($url);

// The data result is JSON decoded because we know it JSON encoded
$routes = json_decode($str_routes);

// For each route in the $routes->documents table we extract the ID to request the details of the route
foreach ($routes->documents as $doc){
    getRouteDetails($doc->document_id);
}

// Given an ID, we extract the useful information we want to transfer in our geodatabase
function getRouteDetails($id) {
  // This is the URL endpoint to get the details of the given route
  // e.g. https://api.camptocamp.org/routes/849904
  $url = "https://api.camptocamp.org/routes/" . $id;
  $str_route = file_get_contents($url);

  // The data result is JSON decoded because we know it JSON encoded
  $route = json_decode($str_route);

  // We prepare the database connection
  $conn = new Connection("host=ec2-23-21-244-254.compute-1.amazonaws.com port=5432 dbname=d6ibh7bqa237ga user=uztkmorlamgklq password=d3e72e14ea35339670a3beb428a9f863cf35464f3583e2fbd0830f7fb32d36e9");

  // We consider only routes having a detailed geometry (the path of snowshoeing)
  if (isset($route->geometry->geom_detail)) {

    // we extract some useful information
    $title = $route->locales[0]->title_prefix;
    $difficulty = $route->snowshoe_rating;
    $altdiff = $route->height_diff_down == null? 0:$route->height_diff_down;
    $description = $route->locales[0]->description;
    $area = $route->associations->waypoints[0]->areas[2]->locales[0]->title;
    $highest_point = $route->elevation_max;

    var_dump($title, $difficulty, $altdiff, $description, $area, $highest_point);

    // we extract especially the path of the route, but also there is always a representative point (may be it is the summit to reach)
    $geom_line = $route->geometry->geom_detail;
    $geom_point = $route->geometry->geom;

    // just display these information in the console
    echo "<h1>" . $title . "</h1>";
    echo "<h3> Difficulté : \n</h3>" . $difficulty;
    echo "<h3> Différence d'altitude : \n</h3>" . $altdiff;
    echo "<h3> Point le plus haut : \n</h3>" . $highest_point;
    echo "<h3> Description : \n</h3>" . $description;
    echo "<h3> Région/vallée : </h3>" . $area;
    echo "<h3> geom_point \n</h3>" . $geom_point;
    echo "<h3> geom_line \n</h3>" . $geom_line;

    // $title = addslashes($title);
    // $description = addslashes($description);

    // build and run table the insert query
    insertTrack($conn, $title, $difficulty, $altdiff, $description, $area, $geom_line);
    insertSummit($conn, $highest_point, $geom_point);

  } else {
    //echo "No geom details!" . "\n";
  }
}

// Given a ready to use DB connection and all information to insert, we prepare the query and run it
function insertTrack($conn, $title, $difficulty, $altdiff, $description, $area, $geom_line) {
  // we define the query template
  // (see https://secure.php.net/manual/fr/function.sprintf.php)
  $format = "INSERT INTO tracks VALUES (
      nextval('seq_track_id'),
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      ST_Transform(ST_Force3D(ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON('%s')), 3857)), 4326)
    )";

  // and we format the final string query (it is safe to pg_escape_string text content)
  $query = sprintf($format,
    pg_escape_string($title),
    pg_escape_string($difficulty),
    $altdiff,
    pg_escape_string($description),
    pg_escape_string($area),
    $geom_line
  );
  // just display it in the console
  echo "<p><b>" . $query . "</b></p>";

  // we run the query
  $conn->insertQuery($query);
}

// Given a ready to use DB connection and all information to insert, we prepare the query and run it
function insertSummit($conn, $highest_point, $geom_point) {
  // we define the query template
  // (see https://secure.php.net/manual/fr/function.sprintf.php)
  $format = "INSERT INTO summits VALUES (
      nextval('seq_summit_id'),
      '%s',
      ST_Transform(ST_Force3D(ST_SetSRID(ST_GeomFromGeoJSON('%s'), 3857)), 4326)
    )";

  // and we format the final string query (it is safe to pg_escape_string text content)
  $query = sprintf($format,
    $highest_point,
    $geom_point
  );
  // just display it in the console
  echo "<p><b>" . $query . "</b></p>";

  // we run the query
  $conn->insertQuery($query);
}

?>
