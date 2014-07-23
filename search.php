<?php  

$center_lat = ( isset( $_GET["lat"] ) ? $_GET["lat"] : 0 );
$center_lng = ( isset( $_GET["lng"] ) ? $_GET["lng"] : 0 ); 
$radius     = ( isset( $_GET["radius"] ) ? $_GET["radius"] : 10 ); 

$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

if( !( $connection = mysql_connect( 'localhost' , 'root' , '' ) ) )
  die( "MySQL Error - Failed to connect to Server : " . mysql_error() );

if( !mysql_select_db('maps', $connection) );
  die( "MySQL Error - Failed to connect to Database : " . mysql_error() );

// algorithm that searches for closest locations 
$query = sprintf( "SELECT address, name, lat, lng, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM markers HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
  mysql_real_escape_string( $center_lat ),
  mysql_real_escape_string( $center_lng ),
  mysql_real_escape_string( $center_lat ),
  mysql_real_escape_string( $radius ) );
$result = mysql_query( $query );

if( !$result )
  die( "MySQL Error - Invalid query: " . mysql_error() . ' "'.$query.'"' );

if( !headers_sent() )
  header( "Content-type: text/xml" );

if( mysql_num_rows( $result ) ){
  while ($row = @mysql_fetch_assoc($result)){
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("name", $row['name']);
    $newnode->setAttribute("address", $row['address']);
    $newnode->setAttribute("lat", $row['lat']);
    $newnode->setAttribute("lng", $row['lng']);
    $newnode->setAttribute("distance", $row['distance']);
  }
}else
  die( 'MySQL Error - No Records Returned "'.$query.'"' );

echo $dom->saveXML();
?>