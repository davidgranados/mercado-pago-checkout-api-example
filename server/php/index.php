<?php

require __DIR__  . '/vendor/autoload.php';

//REPLACE WITH YOUR ACCESS TOKEN AVAILABLE IN: https://www.mercadopago.com/developers/panel
MercadoPago\SDK::setAccessToken("TEST-4041512422646958-092720-0aa808720824b6063a07e97e636d4acb-831564684");

$path = $_SERVER['REQUEST_URI'];

switch($path){
    case '':
    case '/':
        require __DIR__ . '/client/index.html';
        break;
    case '/process_payment':
        // Takes raw data from the request
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $data = json_decode($json);
        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = (float)$data->transactionAmount;
        $payment->token = $data->token;
        $payment->description = $data->description;
        $payment->installments = (int)$data->installments;
        $payment->payment_method_id = $data->paymentMethodId;
        $payment->issuer_id = (int)$data->issuer;

        $payer = new MercadoPago\Payer();
        $payer->email = $data->payer->email;
        $payer->identification = array(
            "type" => $data->payer->identification->type,
            "number" => $data->payer->identification->number
        );
        $payment->payer = $payer;

        $payment->save();

        if ($payment->status === 'approved') {
            // guardo en base de datos los datos de la publicacion
        }

        $response = array(
            'status' => $payment->status,
            'message' => $payment->status_detail,
            'id' => $payment->id
        );
        echo json_encode($response);
        break;

    //Serve static resources
    default:
        $file = __DIR__ . '/client' . $path;
        $extension = end(explode('.', $path));
        $content = 'text/html';
        switch($extension){
            case 'js': $content = 'application/javascript'; break;
            case 'css': $content = 'text/css'; break;
            case 'png': $content = 'image/png'; break;
        }
        header('Content-Type: '. $content);
        readfile($file);
        break;
}
