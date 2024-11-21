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
          $currentMonth = ''; // Initialize current month to detect changes

          // Sort the data array by 'Trip_Start_Date' in ascending order
          usort($data["data"], function ($a, $b) {
            return strtotime($a["Trip_Start_Date"]) - strtotime($b["Trip_Start_Date"]);
          });

          foreach ($data["data"] as $trip) :
            // Check if all necessary fields are present
            $tripName = $trip["Related_Trip.Name_for_Form"] ?? "N/A";
            $startDate = $trip["Trip_Start_Date"] ?? "N/A";
            $length = $trip["Related_Trip.Trip_Days"] ?? "N/A";
            $countTrip = $trip["Trip_Registration_Count"];
            $endDate = $trip["Trip_End_Date"] ?? "N/A";
            $earlyBird = $trip["Related_Trip.Early_Bird_Price"] ?? "N/A";
            $fullPrice = $trip["Related_Trip.Full_Price"] ?? "N/A";
            $tripDetailLink = $trip["Related_Trip.Page_Detail_URL"] ?? 'null';
            $startCity = $trip["Related_Trip.Start_City"] ?? "N/A";
            $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $startDate;

            // var_dump($zohoFormLink);

            // Get the month and year from the start date
            $monthYear = date('F Y', strtotime($startDate));

            // Check if the month has changed
            if ($monthYear !== $currentMonth) :
              $currentMonth = $monthYear;
              echo "<tr><td colspan='7' style='font-weight: bold; font-size: 1.2em;'>$currentMonth</td></tr>";
            endif;
          ?>
            <tr>
              <td><a href="<?php echo esc_url($tripDetailLink); ?>"><?php echo esc_html($tripName); ?></a></td>
              <td><?php echo esc_html($startDate); ?></td>
              <td><?php echo esc_html($length); ?> days</td>
              <td><?php echo esc_html($endDate); ?></td>
              <td>$<?php echo esc_html($earlyBird); ?></td>
              <td>$<?php echo esc_html($fullPrice); ?></td>

              <td>
                <?php if ($countTrip < 10 && $tripDetailLink != 'null') : ?>
                  <a href="<?php echo esc_url($zohoFormLink); ?>" class="book-btn">Book Now</a>
                <?php elseif ($countTrip == 10 && $tripDetailLink != 'null') : ?>
                  <a href="<?php echo esc_url($zohoFormLink); ?>" class="warning-btn" style="color: #ececec">2 Seats Left</a>
                <?php elseif ($countTrip == 11 && $tripDetailLink != 'null') : ?>
                  <a href="<?php echo esc_url($zohoFormLink); ?>" class="warning-btn" style="color: #ececec">1 Seat Left</a>
                <?php elseif ($countTrip == 12 && $tripDetailLink != 'null') : ?>
                  <p class="full-book-btn">Fully Booked</p>
                <?php elseif (empty($countTrip) || $tripDetailLink != "null") : ?>
                  <p class="success-btn">More info coming soon</p>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
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