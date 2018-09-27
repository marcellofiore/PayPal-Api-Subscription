<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *
 * PAYPAL LIBRARY - CREATED BY Marcello Fiore
 * Version: 0.1 - 2018-09-27
 * 
 */

class Paypal_lib_api {

    protected $CI;

    protected $sandbox; // true => test mode - false => live mode
     // DATA APP PAYPAL SANDBOX
    protected $username; // "ATI1ZvytdqD-pr_8TGAfVkvyNJUadc0VR-HwCVYxjmodOI7UBObVy2Rwg_zB6CAPOnhGVcvKukR2V3iy"; // micropayments-facilitator@tuunes.co => SandBox Receive
    protected $password; // "EHKqv_WyjDZcEc-0cKdaU_SHMV65Nfn-iL2i154ecCh_LqAgmxnU5AKXTcPBcXZILavfJgcwiAoTghYN"; // Sunshine!

    // URL SANDBOX
    protected $urlSandBoxAuth = "https://api.sandbox.paypal.com/v1/oauth2/token";
    protected $urlSandBoxCretaePlan = "https://api.sandbox.paypal.com/v1/payments/billing-plans/";
    protected $urlSandBoxCreateAgregment = "https://api.sandbox.paypal.com/v1/payments/billing-agreements/";
    // URL LIVE API
    protected $urlAuth = "https://api.paypal.com/v1/oauth2/token";
    protected $urlCretaePlan = "https://api.paypal.com/v1/payments/billing-plans/";
    protected $urlCreateAgreegment = "https://api.paypal.com/v1/payments/billing-agreements/";
    // VARIABLE FROM CLASS
    protected $returnUrl;
    protected $cancelUrl;
    
    public function __construct($params) {
        $this->CI =& get_instance();
        // load library
        $this->CI->load->helper('url');
        $this->CI->load->library('session');

        //set Sandbox or Live
        $this->sandbox = $params['sandbox'];
        // set Username And Password
        $this->username = $params['username'];
        $this->password = $params['password'];
        // set RETURN URL AND CANCEL URL
        $this->returnUrl = $params['returnUrl'];
        $this->cancelUrl = $params['cancelUrl'];
    }

    // get AutToken PayPal API
    public function getToken() {
        $curl = curl_init();
        if($this->sandbox == true) {
            $url = $this->urlSandBoxAuth;
        } else {
            $url = $this->urlAuth;
        }

        $data_curl = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
            ),
            CURLOPT_USERPWD => $this->username . ":" . $this->password
        );
        curl_setopt_array($curl, $data_curl);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Token API Paypal Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    // create Subscription
    public function createSubscriptionPlan($data_plan, $token) {
        $data_plan = json_decode($data_plan, true);
        // add field return and cancel
        $data_plan["merchant_preferences"]["return_url"] = $this->returnUrl;
        $data_plan["merchant_preferences"]["cancel_url"] = $this->cancelUrl;
        $data_plan = json_encode($data_plan);

        if($this->sandbox == true) {
            $url = $this->urlSandBoxCretaePlan;
        } else {
            $url = $this->urlCretaePlan;
        }
        $curl = curl_init();
        $data_curl = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_plan,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$token,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            )
        );

        curl_setopt_array($curl, $data_curl);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    public function activeSubscriptionPlan($url, $token) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_POSTFIELDS => "[{\n\"op\":\"replace\",\n\"path\":\"/\",\n \"value\":\n {\n\"state\":\"ACTIVE\"\n}\n}]",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$token,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpcode == "200") {
            return "Success Activation";
        }
        /*
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
        */
    }

    public function createAgregment($post_field, $token) {
        if($this->sandbox == true) {
            $url = $this->urlSandBoxCreateAgregment;
        } else {
            $url = $this->urlCreateAgreegment;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_field,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$token,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    // modificare indirizzo da SandBox a Live
    public function activatePlan($token) {
        $curl = curl_init();

        if($this->sandbox == true) {
            $url_activate_sandbox = "https://api.sandbox.paypal.com/v1/payments/billing-agreements/".$token."/agreement-execute";
        } else {
            $url_activate_sandbox = "https://api.paypal.com/v1/payments/billing-agreements/".$token."/agreement-execute";
        }
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url_activate_sandbox,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$this->getToken()->access_token,
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
          } else {
            return json_decode($response);
          }
    }



    /*** SUBSCRIPTION NON FREE ***/
    // create Subscription NON FREE
    public function createSubscriptionPlanNoFree() {
        $curl = curl_init();

        // set Setup FEE => PAGAMENTO INIZIALE visto che non cè il free trial

        $data_plan = '{
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
              }
            ],
            "merchant_preferences": {
                "setup_fee": {
                    "value": "7.99",
                    "currency": "USD"
                  },
              "return_url": "'.$this->returnUrl.'",
              "cancel_url": "'.$this->cancelUrl.'",
              "auto_bill_amount": "YES",
              "initial_fail_amount_action": "CONTINUE",
              "max_fail_attempts": "1"
            }
          }';

        if($this->sandbox == true) {
            $url = $this->urlSandBoxCretaePlan;
        } else {
            $url = $this->urlCretaePlan;
        }

        $data_curl = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_plan,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getToken()->access_token,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            )
        );

        curl_setopt_array($curl, $data_curl);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    public function createAgregmentNoFree($id) {
        date_default_timezone_set('UTC');
        //echo date("c"); // => iso strtotime() => string to time

        $selectedTime = date(DateTime::ISO8601);
        $endTime = strtotime("+7 day", strtotime($selectedTime));
        $dateActive = date('c', $endTime);
        $start_date_sub = str_replace("+00:00", "Z", $dateActive);

        $curl = curl_init();
        $post_field = '{
            "name":"Subscription Tuunes - No Trial",
            "description":"Subscription renewal after 7 Days for $7,99 per week, cancellable anytime.",
            "start_date":"'.$start_date_sub.'",
            "plan":{
                    "id":"'.$id.'"
                },
            "payer":{
                    "payment_method":"paypal"
                }
        }';

        if($this->sandbox == true) {
            $url = $this->urlSandBoxCreateAgregment;
        } else {
            $url = $this->urlCreateAgreegment;
        }

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_field,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$this->getToken()->access_token,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    /**** FINE SETTING NON FREE ****/

}