<?php
$fetchAllTripsDate = "https://www.zohoapis.com/crm/v2/functions/sm_fetch_all_trips_date_data_by_amount_under_12/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5"; // REST API URL
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true); // Decode JSON response
    print_r($data); // Display the data
} else {
    echo "Error: Unable to fetch data.";
}
?>
