#!/usr/bin/php
<?php
/*
 
  Simwood Fraud Alerting 

  This is a simple demonstration of how to use the Simwood realtime 
  calls in progress API to monitor, and alert, for suspect traffic. 
  It is not intended for production use.

  Please note this is an example only and will need adjusting to
  your own business logic.  The example given below is very simplistic
  and not in any way representative of Simwood's own monitoring patterns

  THIS CODE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTY 
  OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, WITHOUT 
  LIMITATION, WARRANTIES THAT THE CODE IS FREE OF DEFECTS, 
  MERCHANTABLE OR FIT FOR A PARTICULAR PURPOSE. 

*/

/* 

  Your Simwood API Details 

  These are in your welcome eMail, if you've not used our API 
  before or don't know your API details please raise a support
  ticket (https://support.simwood.com/) and we'll be happy to
  provide them.

  Note: your api details are NOT the same as your portal login.

 */

$api_user = 'YOUR API USER HERE';
$api_pass =  'YOUR API KEY HERE';
$accountcode = 'XXXXXX';

/* 

  Your Contact Details

  This script is designed to send alerts to you by eMail or SMS
  obviously you may want more complex rules, or to notify more 
  than one contact.  Again, this is an example only and is not 
  intended for production use. 

 */

$contact_email = 'me@example.com';
$contact_mobile = '447700900123';

/* 

  Make a simple "GET" call to the Simwood API, the calls in progress 
  information is returned as a JSON object which PHP then parses into
  an array.

  For full documentation on the Simwood API please see 
  https://mirror.simwood.com/pdfs/APIv3.pdf

 */

$json=file_get_contents("https://$api_user:$api_pass@api.simwood.com/v3/voice/$accountcode/inprogress/current");
$arrData = json_decode($json,true);

/*
  Easy wasn't it! I bet you wished you'd done it years ago.

  Now iterate through and apply your own business logic and rules. 
  The below are just examples, you will need to change them!
*/

$arrCountries = $arrData['countries'];
foreach($arrCountries as $isoCountryCode => $data)
{
    // For test/debug purposes, output the returned data. 
    echo "ISO Code: $isoCountryCode\n"; 
    print_r($data);

    // Perform different actions depending on the destination country    
    switch($isoCountryCode)
    {
        case "GB":
            //This is our core business, not worried so ignore
            break;
        case "LV": //Latvia
        case "LT": //Lithuania
        case "SO": //Somalia
        case "RS": //Serbia
        case "MC": //Monaco
            // These are hotspots so send me an email for any detected calls. 
            // NB: If this really is your policy why not set a channel/rate limit or block them altogether!?

            mail("me@example.com","Fraud notification","Calls detected in progress to known hotspot ($isoCountryCode)");

            // But if there's more than £1 call spend to any of these destinations, send me an SMS
            if($data['total']>1)
            {
                send_sms($contact_mobile,$data['callcount']." calls totalling ".$data['total']." in progress to $iso.");
            }
            break;
        default:
            // This is the rest of the world, I wouldn't expect more than £10 in progress to anywhere so mail me at £5, SMS me at £10.
            if ($data['total']>5)
            {
                mail($contact_email,"Fraud notification",$data['callcount']." calls totalling ".$data['total']." in progress to $iso.");
            }
            else if ($data['total']>10)
            {
                send_sms($contact_mobile,$data['callcount']." calls totalling ".$data['total']." in progress to $iso.");
            }
            break;
    }
}

function send_sms($to,$msg) {
	// Send SMS Notification

	global $accountcode, $api_user, $api_pass;

	$url = 'https://'.$api_user.':'.$api_pass.'@api.simwood.com/v3/messaging/'.$accountcode.'/sms';

	$arrPostData['to'] = $to;
	$arrPostData['from'] = 'CallAlert';
	$arrPostData['message'] = $msg;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrPostData));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	return $result;
}