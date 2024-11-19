<?php
try {
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Prepare the request body
    $body = [
        'Website' => $actual_link, // Pass the current page URL
    ];

    // Make the POST request using wp_remote_post
    $response = wp_remote_post("https://www.zohoapis.com/crm/v2/functions/sm_fetch_detail_trip_dates_data/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5", [
        'body' => json_encode($body),
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);

    // Check for errors
    if (is_wp_error($response)) {
        var_dump('Error:', $response->get_error_message());
        return;
    }

    // Dump the response
    var_dump('Response:', wp_remote_retrieve_body($response));

?>

    <div class="container">
        <h3>Trip Detail</h3>
        <p>Trip Length: 30 days</p>
        <p>Prices:</p>
        <ul>
            <li>Early Bird: $1440 (6+ weeks before Start Date)</li>
            <li>Full Fee: $1598</li>
        </ul>

        <p>Start Dates:</p>
        <div class="date-lists">
            <ul>
                <li><button class="book-btn-date">Book Now - 04/11/2024</button></li>
                <li><button class="book-btn-date">Book Now - 14/11/2024</button></li>
                <li><button class="book-btn-date">Book Now - 02/12/2024</button></li>
                <li><button class="book-btn-date">Book Now - 30/01/2025</button></li>
            </ul>
        </div>


    </div>

<?php
} catch (Exception $e) {
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>