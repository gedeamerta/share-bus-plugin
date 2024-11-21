<?php
try {
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Prepare the request body
    $body = [
        'Website' => basename($actual_link), // Pass the current page URL
    ];

    $args = array(
        'method'      => 'POST',
        'headers'     => array(
            'Content-Type'  => 'application/json',
        ),
        'body'        => json_encode($body),
    );

    $request = wp_remote_post( "https://www.zohoapis.com/crm/v2/functions/sm_fetch_detail_trip_dates_data/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5", $args );

    if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
        error_log( print_r( $request, true ) );
    }

    $response = wp_remote_retrieve_body( $request );

    // Check for errors
    if (is_wp_error($response)) {
        // var_dump('Error:', $response->get_error_message());
        return;
    }

    // Dump the response
    $data = json_decode(json_decode($response, true)["details"]["output"], true);

    $tripDays = $data["Trip_Days"];
    $earlyBird = $data["Early_Bird_Price"];
    $fullPrice = $data["Full_Price"];

?>

    <div class="container">
        <h3>Trip Detail</h3>
        <p>Trip Length: <?php echo esc_html($tripDays); ?> days</p>
        <p>Prices:</p>
        <ul>
            <li>Early Bird: $<?php echo esc_html($earlyBird); ?> (6+ weeks before Start Date)</li>
            <li>Full Fee: $<?php echo esc_html($fullPrice); ?></li>
        </ul>

        <p>Start Dates:</p>
        <div class="date-lists">
            <ul>
                <?php 
                    foreach ($data["Trip_Dates_List"] as $trip) :
                ?>
                    <li><a class="book-btn-date" href="<?php echo "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=". $data["Name_for_Form"] . "&date=" . $trip["Trip_Start_Date"]; ?>">Book Now - <?php echo esc_html($trip["Trip_Start_Date"]); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>


    </div>

<?php
} catch (Exception $e) {
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>