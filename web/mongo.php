<?php
ini_set("display_errors", 1);
try {
// connect to Compose assuming your MONGOHQ_URL environment
// variable contains the connection string
//$connection_url = getenv("MONGOHQ_URL");
$connection_url = "ds055792.mongolab.com:55792/heroku_0vr2zs59";
$dbuser = "odi-elearning";
$dbpass = "connectme";
 // create the mongo connection object
$m = new MongoClient("mongodb://".$connection_url,array("username" => $dbuser, "password" => $dbpass));

// extract the DB name from the connection path
$url = parse_url($connection_url);
$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);

// use the database we connected to
$db = $m->selectDB($db_name);

echo "<h2>Collections</h2>";
echo "<ul>";

// print out list of collections
$cursor = $db->listCollections();
$collection_name = "";
foreach( $cursor as $doc ) {
  echo "<li>" .  $doc->getName() . "</li>";
  $collection_name = $doc->getName();
}
echo "</ul>";

// print out last collection
if ( $collection_name != "" ) {
  $collection = $db->selectCollection($collection_name);
  echo "<h2>Documents in ${collection_name}</h2>";

  // only print out the first 5 docs
  $cursor = $collection->find();
  $cursor->limit(5);
  echo $cursor->count() . ' document(s) found. <br/>';
  foreach( $cursor as $doc ) {
    echo "<pre>";
    var_dump($doc);
    echo "</pre>";
  }
}
// disconnect from server
$m->close();
 } catch ( MongoConnectionException $e ) {
die('Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
 } catch ( MongoException $e ) {
die('Mongo Error: ' . $e->getMessage());
 } catch ( Exception $e ) {
die('Error: ' . $e->getMessage());
 }
 ?>
