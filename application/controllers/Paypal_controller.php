<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *
 * Example PayPal Controller
 * Power By Marcello Fiore
 * 
 */

class Paypal_controller extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->helper('url'); 

		// load library paypal With Configuration
		// CREATE ONE APP IN PAYPAL DASHBOARD => https://developer.paypal.com/ => LOGIN in your dashboard => MyApp and Credentials => REST API apps => Create Your App and then set Client ID and Secret
		$config = array(
			'sandbox' => true,
			'username' => "SET-YOUR-CLIENT-ID",
			'password' => "SET-YOUR-CLIENT-SECRET", // from Paypal daschboard => Create Rest Api APP
			'returnUrl' => site_url('paypal_controller/return'),
			'cancelUrl' => site_url('paypal_controller/cancel')
		);

		$this->load->library('paypal_lib_api', $config);
	}

	public function index() {
		echo 'START PAYPAL => <a href="'.site_url('paypal_controller/startPaypal').'">START BUTTON</a>';
	}


	public function startPaypal() {
		// COMPLETE DOCUMENTATION => https://developer.paypal.com/docs/subscriptions/integrate/integrate-steps/#

		// Request Access Token from Paypal
		$token_paypal_login = $this->paypal_lib_api->getToken()->access_token;

		// create Subscription Plan => configure your setting For Subscription Plan
		$json_data_subscription = '{
            "name": "Name Plan",
            "description": "Description Plan",
            "type": "INFINITE",
            "payment_definitions": [
              	{
					"name": "Regular payment definition",
					"type": "REGULAR",
					"frequency": "DAY",
					"frequency_interval": "7",
					"amount": {
						"value": "7.99",
						"currency": "USD"
					},
                "cycles": "0",
                "charge_models": [
                  {
                    "type": "SHIPPING",
                    "amount": {
                      "value": "0.00",
                      "currency": "USD"
                    }
                }
                ]
              },
              {
                "name": "Trial payment definition",
                "type": "TRIAL",
                "frequency": "DAY",
                "frequency_interval": "7",
                "amount": {
                  "value": "0.00",
                  "currency": "USD"
                },
                "cycles": "1",
                "charge_models": [
                  {
                    "type": "SHIPPING",
                    "amount": {
                      "value": "0.00",
                      "currency": "USD"
                    }
                  }
                ]
              }
            ],
            "merchant_preferences": {
              "auto_bill_amount": "YES",
              "initial_fail_amount_action": "CONTINUE",
              "max_fail_attempts": "1"
            }
		  }';
		$createSubscription = $this->paypal_lib_api->createSubscriptionPlan($json_data_subscription, $token_paypal_login);
		// result create Billing Plan
		echo "<br>Data result Request Create Billing Plan: <br>";
		print_r($createSubscription);

		$id_plan = $createSubscription->id;
		echo "<br><br>- ID: ".$id_plan;
		$url_activate_plan = $createSubscription->links[0]->href;
		echo "<br>- Link Activate Plan: ".$url_activate_plan."<br>";
		
		// Activate Billing Plan 
		$activatePlan = $this->paypal_lib_api->activeSubscriptionPlan($url_activate_plan, $token_paypal_login);
		echo "<br>- Status PLAN Activation: ";
		print_r($activatePlan);
		echo "<br>";
		
		// Create Agreegment Paypal With Start Date
		  
		date_default_timezone_set('UTC');
    $selectedTime = date(DateTime::ISO8601);
    $endTime = strtotime("+1 minutes", strtotime($selectedTime)); // in future
    $dateActive = date('c', $endTime);
    $start_date_sub = str_replace("+00:00", "Z", $dateActive); // create date with ISO format => documentation from PayPal

    // set Data Agreegment
    $data_json_agreegment = '{
        "name":"Subscription Marcello - Start Trial",
        "description":"Free Trial Subscription, renewal after 7 Days for $7,99 per week",
        "start_date":"'.$start_date_sub.'",
        "plan":{
            "id":"'.$id_plan.'"
          },
        "payer":{
            "payment_method":"paypal"
          }
			}';
		
		$createAgregment = $this->paypal_lib_api->createAgregment($data_json_agreegment, $token_paypal_login);
		echo "<br>- Status Create Agreegment:<br>";
		print_r($createAgregment); // INFO PLAN
		echo "<br>";

		$url_login_user = $createAgregment->links[0]->href;

		echo "<br><br>";
		echo "- NOW, REDIRECT THE USER TO => URL => ". $url_login_user . " for LOGIN in PAYPAL<br>";
		// redirect to => $url_login_user
		// then Execute Payment Plan in Return function
		// for example => echo ('<a href="'.$url_login_user.'">PAY WITH PAYPAL</a>');
		echo "<br>";
		echo ('<a href="'.$url_login_user.'">PAY WITH PAYPAL</a>');
		exit();
	}

	public function return() {
		$data = $this->input->get();
		echo "RETURN DATA FROM PAYPAL<br>";
		if($data['token']) {
			// return in get => token, and activate the plan!
			$result = $this->paypal_lib_api->activatePlan($data['token']); // activate the plan
			echo "<br> RULT TRANSATION: <br>";
			print_r($result); // check if result is OK
		}
	}

	public function cancel() {
		echo "Cancelled";
	}



}
