Social Crawlytics PHP API Handler
========================

**Requires**

*  PHP 5.3 or higher
*  cURL and ability to connect over port 443 (SSL)

This set of PHP classes is designed to make it as easy as possible to get full functionality out of Social Crawlytics API.
The example.php file contains some examples of what can be done, and can be run right away with no configuration using the demo account.
Please note that due to the nature of a demo account, it has a low limit of credits which is reset every 10 minutes.

An SSL certificate is provided with this set of PHP scripts as to validate our SSL certificate, cURL needs to know the root
certificate. This certificate is named trust.crt and is for AddTrust External CA Root.



Getting started
====

It's recommended that you read the introduction to Social Crawlytics API available at https://socialcrawlytics.com/docs/api and
the interactive API reference https://socialcrawlytics.com/docs/api/interactive to get a better understanding of the API, you
will also need the token and key from your Social Crawlytics account at https://socialcrawlytics.com/account/myapi

    $api = new \SC\Api\Request('demo', 'demo');

Will create a new API instance, ready to make requests to the API server.

In the API class, we make use of callbacks via anonymous (Closure) functions to provide a better method of dealing with
our API output, considering each API request may yield more than one transaction block (compare the callbacks with the code at the bottom of example.php).

    $api->callback('account_credits', function($response) {
        echo "Looks like we only have " . $response->dataset . " credits left\r\n";
    });

This callback will trigger every time our API responds with an updated credit amount, of course there's a lot of different possibilities available
here, such as having this callback check to see if your credits are below a certain amount, and alert somebody if they are.

Making requests from the API is also kept pretty simple, and the available resources found within the interactive API documentation linked earlier
convert like for like over to PHP commands. Example for getting a list of reports:

    $reportList = $api->get_reports_list(array(
            'records'           => 10,
            'orderColumn'       => 'total_shares',
            'orderDirection'    => 'desc',
        )
    );

This code essentially translates to GET reports/list.json?records=10&orderColumn=total_shares&orderDirection=desc and that applies across all
of the available API commands.

Of course our API is new, we do intend on extending the feature set and including more examples. If you have any feedback we'd leave to hear
it at info@socialcrawlytics.com