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

    usort($data['data'], function ($a, $b) {
      // Extract date part from the "Name" field (last 10 characters, assuming format like "02/12/2024")
      $dateA = DateTime::createFromFormat('d/m/Y', substr($a['Name'], -10));
      $dateB = DateTime::createFromFormat('d/m/Y', substr($b['Name'], -10));
  
      // Compare the dates
      return $dateA <=> $dateB;
    });
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
            $tripNameFiltered = strtok($tripName,"0123456789");
            $tripLink = $trip["Trip Link"] ?? "N/A";
            $startDate = $trip["Trip_Start_Date"] ?? "N/A";
            $length = $trip["Length"] ?? "N/A";
            $countTrip = $trip["Trip_Registration_Count"] ?? "NA";
            $endDate = $trip["Trip_End_Date"] ?? "N/A";
            $earlyBird = $trip["Early Bird"] ?? "N/A";
            $fullPrice = $trip["Full Price"] ?? "N/A";
            $bookingLink = $trip["Booking Link"] ?? "#";

            // Get the month and year from the start date
            $monthYear = date('F Y', strtotime($startDate));

            // Check if the month has changed
            if ($monthYear !== $currentMonth) {
              $currentMonth = $monthYear;
              echo "<tr><td colspan='7' style='font-weight: bold; font-size: 1.2em;'>$currentMonth</td></tr>";
            }
            ?>
            <tr>
              <td><a href="<?php echo $tripLink; ?>"><?php echo esc_html($tripNameFiltered); ?></a></td>
              <td><?php echo esc_html($startDate); ?></td>
              <td><?php echo esc_html($length); ?></td>
              <td><?php echo esc_html($endDate); ?></td>
              <td><?php echo esc_html($earlyBird); ?></td>
              <td><?php echo esc_html($fullPrice); ?></td>
              <?php if ($countTrip < 9 ) : ?>
                <td><a href="<?php echo esc_url($bookingLink); ?>" class="book-btn">Book Now</a></td>
              <?php elseif ($countTrip < 12 ) : ?>
                <td><a href="<?php echo esc_url($bookingLink); ?>" class="book-btn" style="color: #ececec">Book Now (Almost Full) </a></td>
              <?php else : ?>
                <td><p class="full-book-btn">Full Booked</p></td>
              <?php endif; ?>
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