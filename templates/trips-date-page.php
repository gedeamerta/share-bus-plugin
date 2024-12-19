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
  // Pagination settings
  $itemsPerPage = 5; // Number of cards per page
  $totalItems = count($data["data"]); // Total number of trips
  $totalPages = ceil($totalItems / $itemsPerPage); // Total number of pages
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page from URL
  $currentPage = max(1, min($totalPages, $currentPage)); // Ensure current page is valid
  $offset = ($currentPage - 1) * $itemsPerPage; // Calculate offset for the current page
?>
    <!-- Displaying the data in a table -->
    <div class="container-table">
      <table class="table-trips">
        <?php
        $currentMonth = ''; // Initialize current month to detect changes

        // Sort the data array by 'Trip_Start_Date' in ascending order
        usort($data["data"], function ($a, $b) {
          return strtotime($a["Trip_Start_Date"]) - strtotime($b["Trip_Start_Date"]);
        });

        foreach ($data["data"] as $trip) :
          // Check if all necessary fields are present
          $tripName = $trip["Related_Trip.Name_for_Form"] ?? $trip["Name"];
          $startDate = $trip["Trip_Start_Date"] ?? "N/A";

          $startDateForCalculationWeeks = DateTime::createFromFormat("Y-m-d", $trip["Trip_Start_Date"]) ?? "N/A";

          $todayDate = new DateTime();
          $interval = $todayDate->diff($startDateForCalculationWeeks);
          $totalDays = $interval->days;
          $totalWeeks = ceil($totalDays / 7);

          $length = $trip["Related_Trip.Trip_Days"] ?? "N/A";
          $countTrip = $trip["Trip_Registration_Count"];
          $endDate = $trip["Trip_End_Date"] ?? "N/A";
          $totalDrivers = $trip["Total_Drivers"] ?? "N/A";
          $earlyBird = "-";
          if ($trip["Related_Trip.Early_Bird_Price"] != null) {
            $earlyBird = "$" . $trip["Related_Trip.Early_Bird_Price"];
          }
          $fullPrice = "-";
          if ($trip["Related_Trip.Full_Price"] != null) {
            $fullPrice = "$" . $trip["Related_Trip.Full_Price"];
          }
          $tripDetailLink = $trip["Related_Trip.Page_Detail_URL"] ?? 'null';
          $startCity = $trip["Related_Trip.Start_City"] ?? "N/A";
          $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $startDate;
          $zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" . $tripName . "&date=" . $startDate;

          // Get the month and year from the start date
          $monthYear = date('F Y', strtotime($startDate));

          // Check if the month has changed
          if ($monthYear !== $currentMonth) :

            if ($currentMonth !== '') :
              echo '</tbody></table>';
            endif;

            $currentMonth = $monthYear;
        ?>
            <div class="current-month-name">
              <p style="font-weight: bold; font-size: 1.2em;"><?= $currentMonth ?></p>
            </div>
          <?php
            // Start a new table
            echo '<table class="table-trips">
            <thead>
                <tr>
                    <th>Road Trip Route</th>
                    <th>Start Date</th>
                    <th>Trip Length</th>
                    <th>End Date</th>
                    <th>Price</th>
                    <th>Notes</th>
                    <th>Book Your Seat</th>
                </tr>
            </thead>
            <tbody>';
          endif;
          ?>
          <tr>
            <td><a style="font-weight: bold" href="<?php echo esc_url($tripDetailLink); ?>"><?php echo esc_html($tripName); ?></a></td>
            <td><?php echo esc_html(date("d/m/Y", strtotime($startDate))); ?></td>
            <td><?php echo esc_html($length); ?> days</td>
            <td><?php echo esc_html(date("d/m/Y", strtotime($endDate))); ?></td>
            <?php if ($totalWeeks >= 6): ?>
              <td><?php echo esc_html($earlyBird); ?></td>
            <?php elseif ($totalWeeks < 6): ?>
              <td><?php echo esc_html($fullPrice); ?></td>
            <?php endif; ?>
            <td>
              <?php if ($countTrip == 10 && $tripDetailLink != 'null') : ?>
                <p style="color: #FFA500">2 Seats Left</p>
              <?php elseif ($countTrip == 11 && $tripDetailLink != 'null') : ?>
                <p style="color: #FFA500">1 Seat Left</p>
              <?php elseif ($countTrip == 12 && $tripDetailLink != 'null') : ?>
                <p style="color: red">Fully Booked</p>
              <?php elseif ($tripDetailLink == 'null') : ?>
                <p class="text-success-btn">More info coming soon</p>
              <?php endif; ?>
            </td>
            <!-- <td><?php echo esc_html($totalWeeks); ?></td> -->
            <td>
              <?php if ($tripDetailLink != 'null') : ?>
                <?php if ($totalDrivers < 2 && $countTrip >= 9 && $countTrip < 12) : ?>
                  <a href="<?php echo esc_url($zohoFormLinkDriver); ?>" class="book-btn">Book Now</a>
                <?php elseif (empty($totalDrivers) || $totalDrivers == 0 || $totalDrivers >= 1) : ?>
                  <?php if ($countTrip <= 11): ?>
                    <a href="<?php echo esc_url($zohoFormLink); ?>" class="book-btn">Book Now</a>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card-container" id="card-container">
      <!-- Cards will be populated here by JavaScript -->
    </div>

    <div class="pagination-controls">
      <button id="prev-page" class="pagination-btn" disabled>Previous</button>
      <span id="pagination-info">Page 1 of 1</span>
      <button id="next-page" class="pagination-btn">Next</button>
    </div>

    <script>

      // Function to format date as d/m/Y
      function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0'); // Get day and pad with zero if needed
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Get month (0-indexed) and pad with zero
        const year = date.getFullYear(); // Get full year
        return `${day}/${month}/${year}`; // Return formatted date
      }

      // Pass PHP data to JavaScript
      const tripsData = <?php echo json_encode($data["data"]); ?>; 
      const itemsPerPage = 5; // Number of cards per page
      let currentPage = 1;

      function displayCards(page) {
        const cardContainer = document.getElementById('card-container');
        cardContainer.innerHTML = ''; // Clear existing cards

        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedItems = tripsData.slice(start, end);

        paginatedItems.forEach(trip => {
          // Check if all necessary fields are present
          const tripName = trip["Related_Trip.Name_for_Form"] || trip["Name"];
          const startDate = trip["Trip_Start_Date"] || "N/A";
          const endDate = trip["Trip_End_Date"] || "N/A";
          const length = trip["Related_Trip.Trip_Days"] || "N/A";
          const tripDetailLink = trip["Related_Trip.Page_Detail_URL"] || 'null';
          const earlyBird = trip["Related_Trip.Early_Bird_Price"] ? "$" + trip["Related_Trip.Early_Bird_Price"] : "-";
          const fullPrice = trip["Related_Trip.Full_Price"] ? "$" + trip["Related_Trip.Full_Price"] : "-";
          const countTrip = trip["Trip_Registration_Count"] || 0;
          const totalDrivers = trip["Total_Drivers"] || 0;

          const zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" + tripName + "&date=" + startDate;
          const zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" + tripName + "&date=" + startDate;

          const card = document.createElement('div');
          card.className = 'card-trips';
          card.innerHTML = `
          <h3><a style="font-weight: bold" href="${tripDetailLink}">${tripName}</a></h3>
          <ul>
            <li>Start Date: ${formatDate(startDate)}</li>
            <li>End Date: ${formatDate(endDate)}</li>
            <li>Trip Length: ${length} days</li>
            <li>Price: ${countTrip >= 6 ? earlyBird : fullPrice}</li>
            <li>${countTrip === 12 ? '<p style="color: red">Fully Booked</p>' : ''}</li>
            ${countTrip === 10 && tripDetailLink !== 'null' ? '<li><p style="color: #FFA500">2 Seats Left</p></li>' : ''}
            ${countTrip === 11 && tripDetailLink !== 'null' ? '<li><p style="color: #FFA500">1 Seat Left</p></li>' : ''}
            ${countTrip === 12 && tripDetailLink !== 'null' ? '<li><p style="color: red">Fully Booked</p></li>' : ''}
            ${tripDetailLink === 'null' ? '<li><p class="text-success-btn">More info coming soon</p></li>' : ''}
          </ul>
       
          ${tripDetailLink !== 'null' ? `
  ${totalDrivers < 2 && countTrip >= 9 && countTrip < 12 ? 
    `<a href="${zohoFormLinkDriver}" class="book-btn">Book Now</a>` : 
    totalDrivers >= 0 && countTrip <= 11 ? 
    `<a href="${zohoFormLink}" class="book-btn">Book Now</a>` : ''}
` : ''}
          `;
          cardContainer.appendChild(card);
        });

        // Update pagination info
        document.getElementById('pagination-info').innerText = `Page ${currentPage} of ${Math.ceil(tripsData.length / itemsPerPage)}`;

        // Enable/disable pagination buttons
        document.getElementById('prev-page').disabled = currentPage === 1;
        document.getElementById('next-page').disabled = currentPage === Math.ceil(tripsData.length / itemsPerPage);
      }

      document.getElementById('prev-page').addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          displayCards(currentPage);
        }
      });

      document.getElementById('next-page').addEventListener('click', () => {
        if (currentPage < Math.ceil(tripsData.length / itemsPerPage)) {
          currentPage++;
          displayCards(currentPage);
        }
      });

      // Initial display
      displayCards(currentPage);
    </script>

      <!-- old card -->
    <!-- <div class="card-container">
      <?php $count = 0; ?>
      <?php foreach ($data["data"] as $trip) :

        // Check if all necessary fields are present
        $tripName = $trip["Related_Trip.Name_for_Form"] ?? $trip["Name"];
        $startDate = $trip["Trip_Start_Date"] ?? "N/A";

        $startDateForCalculationWeeks = DateTime::createFromFormat("Y-m-d", $trip["Trip_Start_Date"]) ?? "N/A";

        $todayDate = new DateTime();
        $interval = $todayDate->diff($startDateForCalculationWeeks);
        $totalDays = $interval->days;
        $totalWeeks = ceil($totalDays / 7);

        $length = $trip["Related_Trip.Trip_Days"] ?? "N/A";
        $countTrip = $trip["Trip_Registration_Count"];
        $endDate = $trip["Trip_End_Date"] ?? "N/A";
        $totalDrivers = $trip["Total_Drivers"] ?? "N/A";
        $earlyBird = "-";
        if ($trip["Related_Trip.Early_Bird_Price"] != null) {
          $earlyBird = "$" . $trip["Related_Trip.Early_Bird_Price"];
        }
        $fullPrice = "-";
        if ($trip["Related_Trip.Full_Price"] != null) {
          $fullPrice = "$" . $trip["Related_Trip.Full_Price"];
        }
        $tripDetailLink = $trip["Related_Trip.Page_Detail_URL"] ?? 'null';
        $startCity = $trip["Related_Trip.Start_City"] ?? "N/A";
        $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $startDate;
        $zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" . $tripName . "&date=" . $startDate;

        $count++;
      ?>
        <?php if ($count <= 5) : ?>
          <div class="card-trips">
            <h3><a style="font-weight: bold" href="<?php echo esc_url($tripDetailLink); ?>"><?php echo esc_html($tripName); ?></a></h3>
            <ul>
              <li>Start Date: <?php echo esc_html(date("d/m/Y", strtotime($startDate))); ?></li>
              <li>End Date: <?php echo esc_html(date("d/m/Y", strtotime($endDate))); ?></li>
              <li>Trip Length: <?php echo esc_html($length); ?> days</li>
              <?php if ($totalWeeks >= 6): ?>
                <li>Price: <?php echo esc_html($earlyBird); ?></li>
                <td><?php echo esc_html($earlyBird); ?></td>
              <?php elseif ($totalWeeks < 6): ?>
                <li>Price: <?php echo esc_html($fullPrice); ?></li>
              <?php endif; ?>
              <?php if ($countTrip == 10 && $tripDetailLink != 'null') : ?>
                <li>
                  <p style="color: #FFA500">2 Seats Left</p>
                </li>
              <?php elseif ($countTrip == 11 && $tripDetailLink != 'null') : ?>
                <li>
                  <p style="color: #FFA500">1 Seat Left</p>
                </li>
              <?php elseif ($countTrip == 12 && $tripDetailLink != 'null') : ?>
                <li>
                  <p style="color: red">Fully Booked</p>
                </li>
              <?php elseif ($tripDetailLink == 'null') : ?>
                <li>
                  <p class="text-success-btn">More info coming soon</p>
                </li>
              <?php endif; ?>
            </ul>
            <?php if ($tripDetailLink != 'null') : ?>
              <?php if ($totalDrivers < 2 && $countTrip >= 9 && $countTrip < 12) : ?>
                <a href="<?php echo esc_url($zohoFormLinkDriver); ?>" class="book-btn">Book Now</a>
              <?php elseif (empty($totalDrivers) || $totalDrivers == 0 || $totalDrivers >= 1) : ?>
                <?php if ($countTrip <= 11): ?>
                  <a href="<?php echo esc_url($zohoFormLink); ?>" class="book-btn">Book Now</a>
                <?php endif; ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div> -->
<?php
  } else {
    echo "<p>No trip data available.</p>";
  }
} catch (Exception $e) {
  echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>