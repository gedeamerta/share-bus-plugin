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

  // Check if 'data' key exists and is an array
  if (isset($data["data"]) && is_array($data["data"])) {
    ?>

    <!-- Displaying the data in a table -->
    <div class="container">
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
          <?php
          foreach ($data["data"] as $trip) {
            // Check if all necessary fields are present
            $tripName = $trip["Name"] ?? "N/A";
            $tripLink = $trip["Trip Link"] ?? "N/A";
            $startDate = $trip["Trip_Start_Date"] ?? "N/A";
            $length = $trip["Length"] ?? "N/A";
            $endDate = $trip["Trip_End_Date"] ?? "N/A";
            $earlyBird = $trip["Early Bird"] ?? "N/A";
            $fullPrice = $trip["Full Price"] ?? "N/A";
            $bookingLink = $trip["Booking Link"] ?? "#";
            ?>
            <tr>
              <td><a href="<?php echo esc_url($tripLink); ?>" class="book-btn"><?php echo esc_url($tripName); ?></a></td>
              <td><?php echo esc_html($startDate); ?></td>
              <td><?php echo esc_html($length); ?></td>
              <td><?php echo esc_html($endDate); ?></td>
              <td><?php echo esc_html($earlyBird); ?></td>
              <td><?php echo esc_html($fullPrice); ?></td>
              <td><a href="<?php echo esc_url($bookingLink); ?>" class="book-btn">Book Online</a></td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    </div>

    <?php
  } else {
    echo "<p>No trip data available.</p>";
  }
} catch (Exception $e) {
  echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>