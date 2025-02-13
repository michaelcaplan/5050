<?php

require_once(__DIR__ . '/../vendor/autoload.php');

//use Appwrite\Client;

// This Appwrite function will be executed every time your function is triggered
return function ($context) {
    // You can use the Appwrite SDK to interact with other services
    // For this example, we're using the Users service
//    $client = new Client();
//    $client
//        ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
//        ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
//        ->setKey($context->req->headers['x-appwrite-key']);

    $context->log('Total users: Hi!');


    if ($context->req->path === '/stripe') {

        $context->log(json_encode($context->req->bodyJson));

        return $context->res->json('Thanks Stripe, buddy, old pal.');
    }

    return $context->res->json('Nope');
};
