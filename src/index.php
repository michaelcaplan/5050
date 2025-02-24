<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\Client;
use Stripe\Stripe;

function getCustomField($fields, $key) {
    foreach ($fields as $field) {
        if ($field['key'] == $key) {
            return $field;
        }
    }

    return null;
}

// This Appwrite function will be executed every time your function is triggered
return function ($context) {

    try {

        Stripe::setApiKey(getenv('STRIPE_SECRET'));
        $event = \Stripe\Webhook::constructEvent(
            $context->req->bodyText,
            $context->req->headers['stripe-signature'],
            getenv('STRIPE_WEBHOOK_SECRET')
        );

        $context->log('Event: ' . $event->type);

        if ($event->type !== 'checkout.session.completed') {
            $context->res->json([
                'msg' => 'Nope'
            ]);
        }


        $client = new Client();
        $client
            ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
            ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
            ->setKey($context->req->headers['x-appwrite-key']);

        $db = new Appwrite\Services\Databases($client);

        if ($context->req->path === '/stripe') {

            $context->log(json_encode($context->req->bodyJson));

            $dontDisplayField = getCustomField($context->req->bodyJson['data']['object']['custom_fields'], 'dontdisplaymynamepubliclyonthefundraiser');

            if (empty($dontDisplayField) || str_contains($dontDisplayField['dropdown']['value'], 'Yes')) {
                $display = true;
            } else {
                $display = false;
            }

            $businessField = getCustomField($context->req->bodyJson['data']['object']['custom_fields'], 'businessname');
            $teamField = getCustomField($context->req->bodyJson['data']['object']['custom_fields'], 'team');

            $db->createDocument(
                '67acfa8f001b82489d37',
                '67acfaaf000e9833c9e6',
                Appwrite\ID::unique(),
                [
                    'name' => $context->req->bodyJson['data']['object']['customer_details']['name'],
                    'total' => $context->req->bodyJson['data']['object']['amount_total'],
                    'team' => (!empty($teamField) ? $teamField['dropdown']['value'] : null),
                    'email' => $context->req->bodyJson['data']['object']['customer_details']['email'],
                    'display' => $display,
                    'business' => (!empty($businessField) ? $businessField['text']['value'] : null),
                    'created' =>  date('c', $context->req->bodyJson['data']['object']['created'])
                ]
            );

            return $context->res->json([
                'msg' => 'Thanks Stripe, buddy, old pal.'
            ]);
        }
    } catch (Throwable $e) {
        $context->log((string) $e);
        throw $e;
    }

    return $context->res->json([
        'msg' => 'Nope'
    ]);
};
