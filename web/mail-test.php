<?php

$mandrill_key = getenv("MANDRILL_KEY");

try {
    $mandrill = new Mandrill($mandrill_key);
    $message = array(
        'html' => '<p>Example HTML content</p>',
        'subject' => 'example subject',
        'from_email' => 'training@theodi.org',
        'from_name' => 'ODI eLearning',
        'to' => array(
            array(
                'email' => 'davetaz@theodi.org',
                'type' => 'to'
            )
        ),
        'headers' => array('Reply-To' => 'training@theodi.org'),
        'important' => false,
        'track_opens' => null,
        'track_clicks' => null,
        'auto_text' => null,
        'auto_html' => null,
        'inline_css' => null,
        'url_strip_qs' => null,
        'preserve_recipients' => null,
        'view_content_link' => null,
        'tracking_domain' => null,
        'signing_domain' => null,
        'return_path_domain' => null
    );
    $async = false;
    $ip_pool = 'Main Pool';
    $send_at = '2000-01-01 00:00:00';
    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
    print_r($result);
    /*
    Array
    (
        [0] => Array
            (
                [email] => recipient.email@example.com
                [status] => sent
                [reject_reason] => hard-bounce
                [_id] => abc123abc123abc123abc123abc123
            )
    
    )
    */
} catch(Mandrill_Error $e) {
    // Mandrill errors are thrown as exceptions
    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
    throw $e;
}

?>

?>
