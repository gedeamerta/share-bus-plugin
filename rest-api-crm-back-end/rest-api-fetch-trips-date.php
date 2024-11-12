<?php

function fetchTripsData() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://www.zohoapis.com/crm/v2/functions/sm_fetch_all_trips_date_data_by_amount_under_12/actions/execute?auth_type=apikey&zapikey=1003.ea88241c32d52e7e44a3ccd2b9318ea4.a090fa0945e64dc79683a1abfe560bb5',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Cookie: _zcsr_tmp=9d441d2d-7f3b-4ad8-a29e-5636a46150c6; crmcsr=9d441d2d-7f3b-4ad8-a29e-5636a46150c6; group_name=usergroup2; zalb_1a99390653=3053fcfa2eedaefed51c1ef267ca1ed2'
      ),
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response === false) {
        return "Error: Unable to fetch data.";
    }

    return $response;
}

