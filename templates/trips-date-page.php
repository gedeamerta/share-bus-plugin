<!-- main-page.php -->
<?php
// include 'fetch-trips-data.php'; // Include the file with the function

// $response = fetchTripsData(); // Call the function to get the API response

// $data = json_decode($response, true); // Decode JSON response if needed
try {
$request = wp_remote_get( 'https://www.zohoapis.com/crm/v2/functions/sm_fetch_all_trips_date_data_by_amount_under_12/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5' );

if( is_wp_error( $request ) ) {
	return false; // Bail early
}

$body = wp_remote_retrieve_body( $request );

var_dump( $body );

echo "<br><br>";
// Decode the main JSON string
$data = json_decode($body, true); // Using `true` to get an associative array

$data = $data['details']['userMessage'][0];

$data = json_decode($data[0], true);


echo "hai";

if( !empty( $data ) ) {
  var_dump( $data );

}

} catch (Error $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";

  var_dump($e);
}
?>

 <div class="container">
    <h1>Hellow World</h1>
    <table>
    <thead>
      <tr>
        <th>Road Trip Route</th>
        <th>Start Date</th>
        <th>Trip Length</th>
        <th>End Date</th>
        <th>Early Bird</th>
        <th>Full Price</th>
        <th>Book Your Seat</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Perth to Melbourne</td>
        <td>04/11/24</td>
        <td>23 days</td>
        <td>26/11/2024</td>
        <td>$1440</td>
        <td>$1598</td>
        <td><a href="https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=Perth%20to%20Melbourne&date=04%2F11%2F2024" class="book-btn">Book Online</a></td>
      </tr>
      <tr>
        <td>South West Loop (Perth to Perth)</td>
        <td>13/11/24</td>
        <td>10 days</td>
        <td>22/11/2024</td>
        <td>$774</td>
        <td>$828</td>
        <td><a href="#" class="book-btn">Book Online</a></td>
      </tr>
      <tr>
        <td>Perth to Melbourne</td>
        <td>14/11/24</td>
        <td>23 days</td>
        <td>06/12/2024</td>
        <td>$1440</td>
        <td>$1598</td>
        <td><a href="#" class="book-btn">Book Online</a></td>
      </tr>
      <tr>
        <td>South West Loop (Perth to Perth)</td>
        <td>20/11/24</td>
        <td>10 days</td>
        <td>29/11/2024</td>
        <td>$774</td>
        <td>$828</td>
        <td><a href="#" class="book-btn">Book Online</a></td>
      </tr>
      <tr>
        <td>South West Loop (Perth to Perth)</td>
        <td>27/11/24</td>
        <td>10 days</td>
        <td>06/12/2024</td>
        <td>$774</td>
        <td>$828</td>
        <td><a href="#" class="book-btn">Book Online</a></td>
      </tr>
    </tbody>
  </table>
</div>