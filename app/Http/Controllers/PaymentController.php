<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Itsmurumba\Mpesa\Mpesa;

class PaymentController extends Controller
{
    /**
     * Initiate STK push (Lipa Na Mpesa Online)
     */
    public function initiateSTKPush(Request $request)
    {
        // Validate the request data if necessary
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'phone_number' => 'required|string',
        ]);

        return response()->json(['message' => 'Payment initiated successfully']);


        // //parse the result body

        // //check whether the record exists 'perform validation'

        // //if it exists mark as paid

        // $result= MpesaRequest::where('MerchantRequestID',$MerchantRequestID)
        //                         ->where('CheckoutRequestID',$CheckoutRequestID)
        //                         ->get();

        //     if(!$result->isEmpty()){
        //         //mark result column 'status' as paid
        //     }




        // Extract validated data
        $amount = $validatedData['amount'];
        $phoneNumber = $validatedData['phone_number'];

        // Example of initiating STK push (Lipa Na Mpesa Online)
        $accountReference = 'CompanyXLTD'; // Replace with your account reference
        $transactionDescription = 'Payment of X'; // Replace with your transaction description

        try {
        //     // Instantiate Mpesa class
            $mpesa = new Mpesa();

        //     // Call the expressPayment method
            $response = $mpesa->expressPayment($amount, $phoneNumber, $accountReference, $transactionDescription);


        //     // Log the success response
            Log::info('Mpesa STK Push Initiated: ' . json_encode($response));

            return response()->json(['message' => 'Payment initiated successfully', 'response' => $response]);
        } catch (\Exception $e) {
        //     // Log the error
            Log::error('Mpesa STK Push Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to initiate payment. Please try again later.'], 500);
        }
    }

    //authenticate to daraja
    public function authDaraja(){
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic Z2tIZE9xMDB1QURXTUFaVFBpb3RkWmh5ZUVNc0RsSnlTWldwclNCQ0t4WFNsVFpROjY1UGRuRkpBV1hEMUFYUWRoajl1QWpXaUd3dXdFRWUzWkNDNkVySFRueW1Md2ExU0lYQkJUdHdPWEhZaGF6a3Y=']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
         echo $response;
        return $response;
    }


    //send stk push to sim card

    public function pushStk(Request $request){

        $response=$this->authDaraja();
        $decodedJson = json_decode($response);
        $accessToken=$decodedJson->access_token;

        $businessShortCode=174379;
        $passkey=env("LIPA_NA_MPESA_PASSKEY");
        $timestamp=Carbon::now('Africa/Nairobi')->format('YmdHms');
        $password=base64_encode($businessShortCode.$passkey.$timestamp);


        $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '."$accessToken",
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);

        $dataFields= [
            "BusinessShortCode" => $businessShortCode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount"=> 1,
            "PartyA" => 254708374149,
            "PartyB"=> 174379,
            "PhoneNumber"=> 254114126783,
            "CallBackURL"=> "https://mydomain.com/path",
            "AccountReference"=> "CompanyXLTD",
            "TransactionDesc"=> "Payment of X" 
        ];

        $encodedData=json_encode($dataFields);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$encodedData );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response     = curl_exec($ch);
        curl_close($ch);

        //TODO::Install Ngrok and create a callback route and paste the route in the above key "CallBackUrl"

        //upon receiving a response

        //step 1: json_decode($response)

        //step 2: extract CheckoutRequestID and MerchantRequestID

        //step three: store the above two + amount + status 'NOT PAID'

        return response()->json([
            "message" =>$response]);
    }

    public function callback(){
        //step 1: check if CheckoutRequestID and MerchantRequestID exist in table

        //step 2: change status to paid 

    }



}
