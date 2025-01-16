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
      $dateA = DateTime::createFromFormat('d/m/Y', datetime: $a["Trip_Start_Date"]);
      $dateB = DateTime::createFromFormat('d/m/Y', $b["Trip_Start_Date"]);

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
    $itemsPerPage = 25; // Number of cards per page
    $totalItems = count($data["data"]); // Total number of trips
    $totalPages = ceil($totalItems / $itemsPerPage); // Total number of pages
    $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Current page from URL
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

        foreach ($data["data"] as $trip):
          // Check if all necessary fields are present
          $tripName = $trip["Related_Trip.Name_for_Form"] ?? $trip["Name"];
          $startDate = $trip["Trip_Start_Date"] ?? "N/A";

          $formattedStartDate = "N/A";
          if ($startDate !== "N/A") {
            // Convert the start date to DD/MM/YYYY format
            $dateTime = new DateTime($startDate);
            $formattedStartDate = $dateTime->format('d/m/Y');
          } else {
              $formattedStartDate = "N/A";
          }
          
          $startDateForCalculationWeeks = DateTime::createFromFormat("Y-m-d", $trip["Trip_Start_Date"]);

          $todayDate = new DateTime();

          $interval = $todayDate->diff($startDateForCalculationWeeks);
          $totalDays = $interval->days + 1;
          // echo "<script>console.log('debugphp');</script>";
          // echo "<script>console.log(" . json_encode($trip["Trip_Start_Date"]) . ");</script>";
          // echo "<script>console.log(" . json_encode($startDateForCalculationWeeks) . ");</script>";
          // echo "<script>console.log(" . json_encode($todayDate) . ");</script>";
          // echo "<script>console.log(" . json_encode($interval) . ");</script>";
          // echo "<script>console.log(" . json_encode($totalDays) . ");</script>";
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
          $tripDetailLink = $trip["Related_Trip.Page_Detail_URL"] ?? '#';
          $startCity = $trip["Related_Trip.Start_City"] ?? "N/A";
          $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $formattedStartDate;
          $zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" . $tripName . "&date=" . $formattedStartDate;

          // Get the month and year from the start date
          $monthYear = date('F Y', strtotime($startDate));

          // Check if the month has changed
          if ($monthYear !== $currentMonth):

            if ($currentMonth !== ''):
              echo '</tbody></table>';
            endif;

            $currentMonth = $monthYear;
            ?>
            <div class="current-month-name">
              <h4 style="font-weight: bold;padding-top: 20px;text-transform: uppercase; font-family: var(--font2);">
                <?= $currentMonth ?></>
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
            <tbody style="padding-top: 10px">';
          endif;
          ?>
          <tr class="tr-dekstop">
            <td><a style="text-transform: uppercase;"
                href="<?php echo esc_url($tripDetailLink); ?>"><?php echo esc_html($tripName); ?></a></td>
            <td><?php echo esc_html(date("d/m/Y", strtotime($startDate))); ?></td>
            <td><?php echo esc_html($length); ?> days</td>
            <td><?php echo esc_html(date("d/m/Y", strtotime($endDate))); ?></td>
            <?php if ($totalDays >= 42): ?>
              <td><?php echo esc_html($earlyBird); ?></td>
            <?php elseif ($totalDays < 42): ?>
              <td><?php echo esc_html($fullPrice); ?></td>
            <?php else: ?>
              <td>-</td>
            <?php endif; ?>
            <td>
              <?php if ($totalDays >= 56): ?>
                <?php if ($countTrip < 10 && $tripDetailLink != '#'): ?>
                  <p class="text-primary">EARLY BIRD PRICE</p>
                <?php elseif ($countTrip == 10 && $tripDetailLink != '#'): ?>
                  <p class="text-primary">EARLY BIRD PRICE <br> & <br> 2 SPOTS LEFT</p>
                <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                  <p class="text-primary">EARLY BIRD PRICE <br> & <br> 1 SPOT LEFT</p>
                <?php endif; ?>
              <?php endif; ?>

              <?php if ($totalDays >= 42 && $totalDays < 56): ?>
                <?php if ($countTrip < 10 && $tripDetailLink != '#'): ?>
                  <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON</p>
                <?php elseif ($countTrip == 10 && $tripDetailLink != '#'): ?>
                  <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 2 SPOTS LEFT</p>
                <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                  <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 1 SPOT LEFT</p>
                <?php endif; ?>
              <?php endif; ?>

              <?php if ($countTrip == 10 && $tripDetailLink != '#'): ?>
                <p class="text-danger-btn">2 SPOTS LEFT</p>
              <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                <p class="text-danger-btn">1 SPOT LEFT</p>
              <?php elseif ($countTrip == 12 && $tripDetailLink != '#'): ?>
                <p class="text-danger-btn">FULLY BOOKED</p>
              <?php  elseif ($tripDetailLink == '#'): ?>
                <p class="text-success-btn">MORE INFO COMING SOON</p>
              <?php endif; ?>
            </td>

            <!-- <td><?php echo esc_html($totalWeeks); ?></td> -->
            <td>
              <?php if ($tripDetailLink != '#'): ?>
                <?php if ($totalDrivers < 2 && $countTrip >= 9 && $countTrip < 12): ?>
                  <a style="" href="<?php echo esc_url($zohoFormLinkDriver); ?>" class="book-btn">BOOK NOW</a>
                <?php elseif (empty($totalDrivers) || $totalDrivers == 0 || $totalDrivers >= 1): ?>
                  <?php if ($countTrip <= 11): ?>
                    <a style="" href="<?php echo esc_url($zohoFormLink); ?>" class="book-btn">BOOK NOW</a>
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
      <span id="pagination-info" style="margin: 12px">Page 1 of 1</span>
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
      const itemsPerPage = 35; // Number of cards per page
      let currentPage = 1;

      function displayCards(page) {
        const cardContainer = document.getElementById('card-container');
        cardContainer.innerHTML = ''; // Clear existing cards

        let currentMonth = ''

        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedItems = tripsData.slice(start, end);

        paginatedItems.forEach((trip, index) => {
          const isEven = ((index % 2) != 0);
          // Check if all necessary fields are present
          const tripName = trip["Related_Trip.Name_for_Form"] || trip["Name"];
          const startDate = trip["Trip_Start_Date"] || "N/A";
          // Define today's date
          const todayDate = new Date();
          let formattedStartDate = "N/A";
          if (startDate !== "N/A") {
              const startDateObj = new Date(startDate);
              formattedStartDate = startDateObj.toLocaleDateString("en-GB"); // en-GB formats the date as DD/MM/YYYY
          }
          const startDateForCalculationWeeks = new Date(startDate);
          // Calculate the difference in time (milliseconds)
          const timeDifference = Math.abs(todayDate - startDateForCalculationWeeks);
          // Convert time difference to days
          const totalDays = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
          // Calculate total weeks
          const totalWeeks = Math.ceil(totalDays / 7);

          console.log("totalWeeks");
          console.log(totalDays);
          console.log(totalWeeks);
          

          const endDate = trip["Trip_End_Date"] || "N/A";
          const length = trip["Related_Trip.Trip_Days"] || "N/A";
          const tripDetailLink = trip["Related_Trip.Page_Detail_URL"] || 'null';
          const earlyBird = trip["Related_Trip.Early_Bird_Price"] ? "$" + trip["Related_Trip.Early_Bird_Price"] : "-";
          const fullPrice = trip["Related_Trip.Full_Price"] ? "$" + trip["Related_Trip.Full_Price"] : "-";
          const countTrip = trip["Trip_Registration_Count"] || 0;
          const totalDrivers = trip["Total_Drivers"] || 0;

          const zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" + tripName + "&date=" + formattedStartDate;
          const zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" + tripName + "&date=" + formattedStartDate;

          const date = new Date(startDate);

          // Get Month and Year from Start Date
          let monthYear = date.toLocaleDateString("en-GB", {
            month: "long",
            year: "numeric"
          });;

          // Add month and year only once
          if (monthYear !== currentMonth) {
            currentMonth = monthYear;
            const monthAndYear = document.createElement("div");
            monthAndYear.className = "month-and-year-container";
            monthAndYear.innerHTML = `
                  <h4 style="font-weight: bold; padding-top: 20px; text-transform: uppercase; margin: 10px 0; font-family: var(--font2);">${monthYear}</h4>
                `;
            cardContainer.appendChild(monthAndYear);
          }

          // Create table row for trip details
          const tableRow = document.createElement("div");
          tableRow.className = "trip-row";
          tableRow.innerHTML = `
              <table id="tr-trip-date-list-${index}" style="width: 100%; margin-top: 0px; ${isEven ? "background-color: var(--brandColor4);" : "background-color: white;"}">
                <tr>
                  <td style="width: 65%; font-weight: bold; text-transform: uppercase; padding-left: 10px; padding-top: 10px;"><a style="color: #16a7fb" href="https://${tripDetailLink}" target='_blank'">${tripName}</a></td>
                  <td id="trip-start-date-${index}" style="width: 30%; padding: 10px; color: #000; font-family: 'ITCAvantGardeStd-Bold'; padding-bottom: 0px;">${startDate !== "N/A" ? formatDate(startDate) : "N/A"}</td>
                  <td style="width: 5%; text-align: center; padding-right: 10px;">
                    <span id="show-more-icon-${index}" class="show-more-icon" style="color: var(--brandColor2); cursor: pointer; font-size: 28px">+</span>
                    <span id="show-less-icon-${index}" class="show-less-icon" style="color: var(--brandColor2); display: none; cursor: pointer; font-size: 32px">-</span>
                  </td>
                </tr>
              </table>
              <div id="trip-details-${index}" class="card-trips" style="display: none; margin-top: -5px; border-top: 1px solid #ddd; padding-top: 8px; border-bottom-left-radius: 10px;border-bottom-right-radius: 10px; ${isEven ? "background-color: var(--brandColor4);" : "background-color: white; padding-left: 10px; padding-right: 10px;}"}">
              <ul>
                  <li><span>START DATE </span><span>${formatDate(startDate)}</span></li>
                  <li><span>END DATE </span><span>${formatDate(endDate)}</span></li>
                  <li><span>TRIP LENGTH </span><span>${length} days</span></li>
                  <li><span>PRICE </span><span>${totalDays >= 42 ? earlyBird : fullPrice}</span></li>
                  <li><span>NOTES</span>
                    ${totalDays >= 56 && countTrip < 10 && tripDetailLink !== 'null' 
                    ? '<p class="text-primary">EARLY BIRD PRICE</p>' 
                    : ''}
                  ${totalDays >= 56 && countTrip === 10 && tripDetailLink !== 'null' 
                    ? '<p class="text-primary">EARLY BIRD PRICE <br> & <br> 2 SPOTS LEFT</p>' 
                    : ''}
                  ${totalDays >= 56 && countTrip === 11 && tripDetailLink !== 'null' 
                    ? '<p class="text-primary">EARLY BIRD PRICE <br> & <br> 1 SPOT LEFT</p>' 
                    : ''}
                  
                  ${totalDays >= 42 && totalDays < 56 && countTrip < 10 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON</p>' 
                    : ''}
                  ${totalDays >= 42 && totalDays < 56 && countTrip === 10 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 2 SPOTS LEFT</p>' 
                    : ''}
                  ${totalDays >= 42 && totalDays < 56 && countTrip === 11 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 1 SPOT LEFT</p>' 
                    : ''}

                  ${countTrip === 10 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">2 SPOTS LEFT</p>' 
                    : ''}
                  ${countTrip === 11 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">1 SPOT LEFT</p>' 
                    : ''}
                  ${countTrip === 12 && tripDetailLink !== 'null' 
                    ? '<p class="text-danger-btn">FULLY BOOKED</p>' 
                    : ''}
                  ${tripDetailLink === 'null' 
                    ? '<p class="text-success-btn">MORE INFO COMING SOON</p>' 
                    : ''}
                  ${countTrip === 10 && tripDetailLink !== 'null' ? '<p style="color: #FFA500">2 SPOTS LEFT</p></li>' : ''}
                  ${countTrip === 11 && tripDetailLink !== 'null' ? '<p style="color: #FFA500">1 SPOT LEFT</p></li>' : ''}
                  ${countTrip === 12 && tripDetailLink !== 'null' ? '<p style="color: red">FULLY BOOKED</p></li>' : ''}
                  ${ (tripDetailLink === 'null' && !( totalWeeks >= 6 || totalWeeks === 5 || countTrip === 10 ||  countTrip === 11 || countTrip === 12)) ? '<p class="text-success-btn">MORE INFO COMING SOON</p></li>' : ''}
                </ul>
                ${tripDetailLink !== 'null' ? `
                ${totalDrivers < 2 && countTrip >= 9 && countTrip < 12 ?
                `<a href="${zohoFormLinkDriver}" class="book-btn-mobile">BOOK NOW</a>` :
                totalDrivers >= 0 && countTrip <= 11 ?
                  `<a href="${zohoFormLink}" class="book-btn-mobile">BOOK NOW</a>` : ''}
              ` : ''}
              </div>
            `;

          cardContainer.appendChild(tableRow);

          // Add event listeners for Show More / Show Less
          const showMoreIcon = document.getElementById(`show-more-icon-${index}`);
          const showLessIcon = document.getElementById(`show-less-icon-${index}`);
          const tripDetails = document.getElementById(`trip-details-${index}`);
          const tripStartDate = document.getElementById(`trip-start-date-${index}`);
          const trTripDateList = document.getElementById(`tr-trip-date-list-${index}`);

          showMoreIcon.addEventListener("click", () => {
            tripDetails.style.display = "block";
            showMoreIcon.style.display = "none";
            showLessIcon.style.display = "inline";
            tripStartDate.style.display = "none";
            trTripDateList.style.backgroundColor = isEven ? "background-color: var(--brandColor4);" : "background-color: white;";
            trTripDateList.style.borderTop = "1px solid var(--brandColor3)";
            trTripDateList.style.borderRight = "1px solid var(--brandColor3)";
            trTripDateList.style.borderLeft = "1px solid var(--brandColor3)";
            trTripDateList.style.borderBottom = "0px solid #fff";
            trTripDateList.style.borderCollapse = "separate";
            trTripDateList.style.borderTopLeftRadius = "10px";
            trTripDateList.style.borderTopRightRadius = "10px";
          });

          showLessIcon.addEventListener("click", () => {
            tripDetails.style.display = "none";
            showMoreIcon.style.display = "inline";
            showLessIcon.style.display = "none";
            tripStartDate.style.display = "block";
            trTripDateList.style.backgroundColor = isEven ? "background-color: var(--brandColor4);" : "background-color: white;";
            trTripDateList.style.borderRadius = "0px";
            trTripDateList.style.borderTop = "0px solid #fff";
            trTripDateList.style.borderRight = "0px solid #fff";
            trTripDateList.style.borderLeft = "0px solid #fff";
            trTripDateList.style.borderCollapse = "separate";
            trTripDateList.style.borderTopLeftRadius = "0px";
            trTripDateList.style.borderTopRightRadius = "0px";
          });

        });

        // Helper function to format date
        function formatDate(dateString) {
          const date = new Date(dateString);
          if (isNaN(date.getTime())) return "N/A";

          const day = String(date.getDate()).padStart(2, "0");
          const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are zero-based
          const year = date.getFullYear();

          return `${day}/${month}/${year}`;
        }

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

    <?php
  } else {
    echo "<p>No trip data available.</p>";
  }
} catch (Exception $e) {
  echo "<p>Erro from Smartmates Plugin: " . esc_html($e->getMessage()) . "</p>";
}
?>