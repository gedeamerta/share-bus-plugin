<?php
try {
    // Fetch data from Zoho API
    $request = wp_remote_get('https://www.zohoapis.com/crm/v2/functions/sm_fetch_all_trips_date_data_by_amount_under_12/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5');

    // Check for errors in the request
    if (is_wp_error($request)) {
        echo "<p>Error fetching data from Zoho API.</p>";
        return;
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($request);
    $response = json_decode($body, true);

    // Check if response contains the expected structure
    if (isset($response['details']['userMessage'][0])) {
        $data = json_decode($response['details']['userMessage'][0], true);
    } else {
        echo "<p>No data found in API response.</p>";
        return;
    }

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