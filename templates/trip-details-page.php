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

    $tripDays = $data["Trip_Days"];
    $earlyBird = $data["Early_Bird_Price"];
    $fullPrice = $data["Full_Price"];

    $tripDetailLink = $data["Page_Detail_URL"] ?? 'null';
    $startCity = $data["Start_City"] ?? "N/A";

    $tripName = $data["Name_for_Form"] ?? "N/A";

    ?>
    <section id="comp_trip_dates_1"
        class="compSection compSection_5 comp_trip_dates comp_trip_dates_1 py-sm textcolor__default  " data-animate="1"
        style=" z-index:1">
        <table>
            <thead>
                <tr>
                    <td>START DATE</td>
                    <td>END DATE</td>
                    <td>PRICE</td>
                    <td>NOTES</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data["Trip_Dates_List"] as $trip):
                    $startDate = $trip["Trip_Start_Date"] ?? "N/A";
                    $endDate = $trip["Trip_End_Date"] ?? "N/A";
                    $countTrip = $trip["Trip_Registration_Count"];

                    $zohoFormLink = "https://forms.zohopublic.com/admin1608/form/TESTFullFormRegistrationandPayment/formperma/ujzk8Yo2qYr13WNZpzz4PF6erUucysO21uTXuvTnYXY?trip=" . $tripName . "&date=" . $startDate;
                    $zohoFormLinkDriver = "https://forms.zohopublic.com/admin1608/form/TripRegistrationandPaymentDriver/formperma/-Fri6gn7uIQWcB6aCKXNdeAfJlPBX9r249ysVueUtTA?trip=" . $tripName . "&date=" . $startDate;
                    $totalDrivers = $trip["Total_Drivers"] ?? "N/A";


                    $startDateForCalculationWeeks = DateTime::createFromFormat("Y-m-d", $trip["Trip_Start_Date"]) ?? "N/A";

                    $todayDate = new DateTime();
                    $interval = $todayDate->diff($startDateForCalculationWeeks);
                    $totalDays = $interval->days;
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

                        <?php if ($totalWeeks >= 6): ?>
                            <td data-label="PRICE">$<?php echo esc_html($earlyBird); ?></td>
                        <?php elseif ($totalWeeks < 6): ?>
                            <td data-label="PRICE">$<?php echo esc_html($fullPrice); ?></td>
                        <?php endif; ?>

                        <td data-label="NOTES">
                            <?php if ($countTrip == 10 && $tripDetailLink != 'null'): ?>
                                <p style="color: #FFA500; margin: 0px;">2 Seats Left</p>
                            <?php elseif ($countTrip == 11 && $tripDetailLink != 'null'): ?>
                                <p style="color: #FFA500; margin: 0px;">1 Seat Left</p>
                            <?php elseif ($countTrip == 12 && $tripDetailLink != 'null'): ?>
                                <p style="color: red; margin: 0px;">Fully Booked</p>
                            <?php elseif ($tripDetailLink == 'null'): ?>
                                <p class="text-success-btn" style="margin: 0px;">More info coming soon</p>
                            <?php else: ?>
                                <p>-</p>
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
    </section>
    <?php
} catch (Exception $e) {
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}
?>