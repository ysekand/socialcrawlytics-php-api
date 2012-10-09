<?php

require_once 'sc\\api\\response.php';
require_once 'sc\\api\\transaction.php';
require_once 'sc\\api\\partial.php';
require_once 'sc\\api\\error.php';
require_once 'sc\\api\\request.php';

// Usually classes are kept one per file, but for simplicity and because some people may
// not use auto-loaders, we've also provided a merged copy with all our API classes in
// one file - but you're free to use the individual files also provided.
// require_once 'sc\\api\\merged\\api.php';

// Create our API object, through this method you can connect to multiple accounts
// within the same script without causing issues. Fisrt parameter is your API token
// the second one is your API key.

// NOTE: The demo account is limited on credits, refreshed periodically to prevent misuse
// if you do not change it to your own API details, it may return an out of credits error.
$api = new \SC\Api\Request('demo', 'demo');

// Since the API returns the number of credits remaining every time you perform an action
// that uses credits, a callback such as this can be used to easily see every time credits
// are used during your application.
$api->callback('account_credits', function($response) {
    echo "Looks like we only have " . $response->dataset . " credits left\r\n";
});

// Callbacks can be very useful to avoid having excessive loops within your main application,
// simply define it anywhere before you call the API and process each item as it's called.
$addresses = array();
$api->callback('reports_list', function($response) use (&$addresses) {
    foreach ($response->dataset as $report) {
        array_push($addresses, $report->base_scheme . '://' . $report->base_host . $report->base_uri);
    }
});

// Sample code to create a schedule with an example of what parameters can be set.
// Will schedule a report to be created every 3 hours, occuring 5 times.
// Note that this will cost 2 credits to perform, which will trigger the account_credits
// callback we setup above.
$reportCreate = $api->post_reports_create(array(
        'website'               => 'http://bbc.co.uk/',     // The starting point for our search, can be any valid URL.
        'websiteDepth'          => 0,                       // How many links deep shall our search go? Maximum 10.
        'websiteTraverse'       => 1,                       // What traverse type should we use? 1 = No traverse, 2 = Sub domain traverse.

        //'scheduleName'              => 'Hello world',       // If this is present, the system will create a schedule.
        //'scheduleCount'             => 5,                   // How many times do we want our schedule to run? Maximum 10.
        //'scheduleRepeatInterval'    => 3,                   // How many units of scheduleRepeatRotation?
        //'scheduleRepeatRotation'    => 1,                   // 1 = hour, 2 = day, 3 = week, 4 = month
        //'scheduleStartTimestamp'    => time(),              // What unix timestamp date should our first report run on?
                                                              // This schedule will do 5 occurances, every 3 hours.

        'notifyTwitter'         => false,                   // Should we post to our Twitter that our report finished?
        'notifyEmail'           => false,                   // Should we send an email to ourselves once the report finishes?
        'notifyEmailAddress'    => 'email@changeme'         // If so, what should that email address be?
    )
);

// Fetch a list of our top 10 reports by total shares, this will trigger the "reports_list"
// callback declared earlier.
$reportList = $api->get_reports_list(array(
        'records'           => 10,
        'orderColumn'       => 'total_shares',
        'orderDirection'    => 'desc',
    )
);

// So due to our "reports_list" callback, we now have $addresses filled with the address
// of each report, so we can easily manage our information.
var_dump($addresses);

// Of course, you're also free to skip the callbacks and loop through data
// using foreach or another similar method.
foreach ($reportList->transactions as $transaction) {
    if ($transaction->mark == '_eapi_reports_list') {
        foreach ($transaction->dataset as $report) {
            echo $report->base_scheme . '://' . $report->base_host . $report->base_uri . "\r\n";
        }
    }
}