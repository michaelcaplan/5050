<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\Client;

// This Appwrite function will be executed every time your function is triggered
return function ($context) {

    try {
        $client = new Client();
        $client
            ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
            ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
            ->setKey($context->req->headers['x-appwrite-key']);

        $db = new Appwrite\Services\Databases($client);

        if ($context->req->path === '/stripe') {

            try {
                $context->log(json_encode($context->req->bodyJson));

                $display = $context->req->bodyJson['data']['object']['custom_fields'][2]['value'];
                if (empty($display) || str_contains($display, 'Yes')) {
                    $display = true;
                } else {
                    $display = false;
                }

                $db->createDocument(
                    '67acfa8f001b82489d37',
                    '67acfaaf000e9833c9e6',
                    Appwrite\ID::unique(),
                    [
                        'name' => $context->req->bodyJson['data']['object']['customer_details']['name'],
                        'total' => $context->req->bodyJson['data']['object']['amount_total'],
                        'team' => $context->req->bodyJson['data']['object']['custom_fields'][0]['dropdown']['value'],
                        'email' => $context->req->bodyJson['data']['object']['customer_details']['email'],
                        'display' => $display,
                        'business' => $context->req->bodyJson['data']['object']['custom_fields'][1]['text']['value'],
                        'created' =>  $context->req->bodyJson['data']['object']['created']
                    ]
                );
            } catch (Throwable $e) {
                $context->log($e->getMessage());
            }

            return $context->res->json([
                'msg' => 'Thanks Stripe, buddy, old pal.'
            ]);
        }
    } catch (Exception $e) {
        $context->log((string) $e);
    }

    return $context->res->json([
        'msg' => 'Nope'
    ]);
};
