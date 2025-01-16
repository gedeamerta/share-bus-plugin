<style>
    /* test */
</style>

<?php
try {
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Prepare the request body
    $body = [
        'Website' => basename($actual_link), // Pass the current page URL
    ];

    $args = array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($body),
    );

    $request = wp_remote_post("https://www.zohoapis.com/crm/v2/functions/sm_fetch_detail_trip_dates_data/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5", $args);

    if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
        error_log(print_r($request, true));
    }

    $response = wp_remote_retrieve_body($request);

    // Check for errors
    if (is_wp_error($response)) {
        // var_dump('Error:', $response->get_error_message());
        return;
    }

    // Dump the response
    $data = json_decode(json_decode($response, true)["details"]["output"], true);

    // Check if the "output" field is empty
    if (empty($data)) {
        echo "<h3 style='padding: 40px 0px;'>Sorry, there are no trip details to display at the moment.</h3>";
        return;
    }

    $tripDays = $data["Trip_Days"];
    $earlyBird = $data["Early_Bird_Price"];
    $fullPrice = $data["Full_Price"];

    $tripDetailLink = $data["Page_Detail_URL"] ?? 'null';
    $startCity = $data["Start_City"] ?? "N/A";

    $tripName = $data["Name_for_Form"] ?? "N/A";

?>
    <section id="comp_trip_dates_1"
        class="compSection compSection_5 comp_trip_dates comp_trip_dates_1 py-sm textcolor__default  " data-animate="1"
        style=" z-index:1; width: 100%;">
        <table>
            <thead>
                <tr>
                    <th>START DATE</th>
                    <th>END DATE</th>
                    <th>PRICE</th>
                    <th>NOTES</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php

                // Get today's date in 'YYYY-MM-DD' format
                $today = date("Y-m-d");

                // Extract the 'Trip_Start_Date' column
                $dates = array_column($data["Trip_Dates_List"], "Trip_Start_Date");

                // Convert dates to a sortable format if needed (e.g., 'YYYY-MM-DD')
                $dates = array_map('strtotime', $dates);

                // Sort the dates array in ascending order
                array_multisort($dates, SORT_ASC, $data["Trip_Dates_List"]);

                // Filter out trips that start before today
                $data["Trip_Dates_List"] = array_filter($data["Trip_Dates_List"], function ($trip) use ($today) {
                    return strtotime($trip["Trip_Start_Date"]) >= strtotime($today);
                });

                // Reindex array to avoid gaps after filtering
                $data["Trip_Dates_List"] = array_values($data["Trip_Dates_List"]);

                foreach ($data["Trip_Dates_List"] as $trip):
                    $startDate = $trip["Trip_Start_Date"] ?? "N/A";
                    $endDate = $trip["Trip_End_Date"] ?? "N/A";
                    $countTrip = $trip["Trip_Registration_Count"];

                    $formattedStartDate = "N/A";
                    if ($startDate !== "N/A") {
                      // Convert the start date to DD/MM/YYYY format
                      $dateTime = new DateTime($startDate);
                      $formattedStartDate = $dateTime->format('d/m/Y');
                    } else {
                        $formattedStartDate = "N/A";
                    }

                    $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $formattedStartDate;
                    $zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" . $tripName . "&date=" . $formattedStartDate;
                    $totalDrivers = $trip["Total_Drivers"] ?? "N/A";


                    $startDateForCalculationWeeks = DateTime::createFromFormat("Y-m-d", $trip["Trip_Start_Date"]) ?? "N/A";

                    $todayDate = new DateTime();
                    $interval = $todayDate->diff($startDateForCalculationWeeks);
                    $totalDays = $interval->days + 1;
                    $totalWeeks = ceil($totalDays / 7);

                ?>
                    <tr>
                        <td data-label="START DATE"><?php
                                                    $formattedDate = date("d/m/Y", strtotime($startDate));
                                                    echo esc_html($formattedDate);
                                                    ?></td>

                        <td data-label="END DATE"><?php
                                                    $formattedDate = date("d/m/Y", strtotime($endDate));
                                                    echo esc_html($formattedDate);
                                                    ?></td>

                        <?php if ($totalDays >= 42): ?>
                            <td data-label="PRICE">$<?php echo esc_html($earlyBird); ?></td>
                        <?php elseif ($totalWeeks < 42): ?>
                            <td data-label="PRICE">$<?php echo esc_html($fullPrice); ?></td>
                        <?php endif; ?>

                        <td data-label="NOTES">
                            <?php if ($totalDays >= 56): ?>
                                <?php if ($countTrip < 10 && $tripDetailLink != '#'): ?>
                                    <p class="text-primary">EARLY BIRD PRICE</p>
                                <?php elseif ($countTrip == 10 && $tripDetailLink != '#'): ?>
                                    <p class="text-primary">EARLY BIRD PRICE <br> & <br> 2 SPOTS LEFT</p>
                                <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                                    <p class="text-primary">EARLY BIRD PRICE <br> & <br> 1 SPOT LEFT</p>
                                <?php else: ?>
                                    <p>-</p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($totalDays >= 42 && $totalDays < 56): ?>
                                <?php if ($countTrip < 10 && $tripDetailLink != '#'): ?>
                                    <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON</p>
                                <?php elseif ($countTrip == 10 && $tripDetailLink != '#'): ?>
                                    <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 2 SPOTS LEFT</p>
                                <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                                    <p class="text-danger-btn">EARLY BIRD PRICE ENDS SOON <br> & <br> 1 SPOT LEFT</p>
                                <?php else: ?>
                                    <p>-</p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($countTrip == 10 && $tripDetailLink != '#'): ?>
                                <p class="text-danger-btn">2 SPOTS LEFT</p>
                            <?php elseif ($countTrip == 11 && $tripDetailLink != '#'): ?>
                                <p class="text-danger-btn">1 SPOT LEFT</p>
                            <?php elseif ($countTrip == 12 && $tripDetailLink != '#'): ?>
                                <p class="text-danger-btn">FULLY BOOKED</p>
                            <?php elseif ($tripDetailLink == '#'): ?>
                                <p class="text-success-btn">MORE INFO COMING SOON</p>
                            <?php endif; ?>

                        </td>

                        <!-- <td data-label="NOTES">
                        <span class="">
                            12 slots left </span> 
                        </td> -->

                        <td data-label="BOOK NOW">
                            <?php if ($tripDetailLink != 'null'): ?>
                                <?php if ($totalDrivers < 2 && $countTrip >= 9): ?>
                                    <a href="<?php echo esc_url($zohoFormLinkDrivers); ?>" class=""
                                        style="font-family: 'ITCAvantGardeStd-Bold';">BOOK NOW</a>
                                <?php elseif (empty($totalDrivers) || $totalDrivers == 0 || $totalDrivers >= 1): ?>
                                    <?php if ($countTrip <= 11): ?>
                                        <a href="<?php echo esc_url($zohoFormLink); ?>" class=""
                                            style="font-family: 'ITCAvantGardeStd-Bold';">BOOK NOW</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                        </td>
                        <!--                         
                        <td data-label="BOOK NOW">
                            <a
                                href="https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=Broome%20to%20Perth&amp;date=22%2F10%2F2024">BOOK
                                NOW</a>
                        </td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <button id="expandButton" class="expand-btn" data-expanded="false">MORE DATES</button>
    </section>


    <script>
        const expandButton = document.getElementById('expandButton');
        const hiddenItems = document.querySelectorAll('.comp_trip_dates table tbody tr:nth-child(n+6)');

        // Function to handle item visibility based on screen width
        function handleResize() {
            if (window.innerWidth >= 1024) {
                // On desktop, show all items and hide the button
                hiddenItems.forEach(item => item.style.display = 'table');
                expandButton.style.display = 'none';
            } else {
                // On mobile, show only the first 5 items and display the button
                const isExpanded = expandButton.getAttribute('data-expanded') === 'true';
                hiddenItems.forEach(item => item.style.display = isExpanded ? 'table' : 'none');
                expandButton.style.display = 'block';
            }
        }

        expandButton.addEventListener('click', function() {
            const isExpanded = this.getAttribute('data-expanded') === 'true';

            if (isExpanded) {
                hiddenItems.forEach(item => item.style.display = 'none');
                this.textContent = 'MORE DATES';
                this.setAttribute('data-expanded', 'false');
            } else {
                hiddenItems.forEach(item => item.style.display = 'table');
                this.textContent = 'SHOW LESS';
                this.setAttribute('data-expanded', 'true');
            }
        });


        // Attach the resize event listener
        window.addEventListener('resize', handleResize);

        // Run on initial load to ensure correct state
        handleResize();
    </script>
<?php
} catch (Exception $e) {
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>