<?php
/***********************************************************
This File Sets Up Calls to Paypal by arranging url information.
***********************************************************/
class Paypal{

	function __construct(){

	}

	function DoDirectPayment($paymentInfo=array()){
		/**
		 * Get required parameters from the web form for the request
		 */
		$paymentType =urlencode('Sale');
		$firstName =urlencode($paymentInfo['Member']['first_name']);
		$lastName =urlencode($paymentInfo['Member']['last_name']);
		$creditCardType =urlencode($paymentInfo['CreditCard']['credit_type']);
		$creditCardNumber = urlencode($paymentInfo['CreditCard']['card_number']);
		$expDateMonth =urlencode($paymentInfo['CreditCard']['expiration_month']);
		$padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);
		$expDateYear =urlencode($paymentInfo['CreditCard']['expiration_year']);
		$cvv2Number = urlencode($paymentInfo['CreditCard']['cv_code']);
		$address1 = urlencode($paymentInfo['Member']['billing_address']);
		$address2 = urlencode($paymentInfo['Member']['billing_address2']);
		$country = urlencode($paymentInfo['Member']['billing_country']);
		$city = urlencode($paymentInfo['Member']['billing_city']);
		$state =urlencode($paymentInfo['Member']['billing_state']);
		$zip = urlencode($paymentInfo['Member']['billing_zip']);

		$description = urlencode($paymentInfo['Description']);

		$amount = urlencode($paymentInfo['Order']['theTotal']);
		$currencyCode="USD";
		$paymentType=urlencode('Sale');

		$ip=$_SERVER['REMOTE_ADDR'];

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		// $nvpstr="&PAYMENTACTION=Sale&IPADDRESS=$ip&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear."&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITYNAME=$city&STATEORPROVINCE=$state".
		// 		"&POSTALCODE=$zip&COUNTRY=$country&CURRENCYCODE=$currencyCode";

			$nvpstr="&PAYMENTACTION=Sale&IPADDRESS=$ip&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=" . $padDateMonth . $expDateYear . "&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITY=$city&STATE=$state" . "&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyCode&DESC=$description";

		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $resArray */
		$resArray=$this->hash_call("doDirectPayment",$nvpstr);

		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */

		return $resArray;
		//Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
	}

	function SetExpressCheckout($paymentInfo=array()){
		$amount = urlencode($paymentInfo['Order']['theTotal']);
		$paymentType=urlencode('Sale');
		$currencyCode=urlencode('USD');

		$returnURL =urlencode($paymentInfo['Order']['returnUrl']);
		$cancelURL =urlencode($paymentInfo['Order']['cancelUrl']);

		$nvpstr='&AMT='.$amount.'&PAYMENTACTION='.$paymentType.'&CURRENCYCODE='.$currencyCode.'&RETURNURL='.$returnURL.'&CANCELURL='.$cancelURL;
		$resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
		return $resArray;
	}

	function GetExpressCheckoutDetails($token){
		$nvpstr='&TOKEN='.$token;
		$resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
		return $resArray;
	}

	function DoExpressCheckoutPayment($paymentInfo=array()){
		$paymentType='Sale';
		$currencyCode='USD';
		$serverName = $_SERVER['SERVER_NAME'];
		$nvpstr='&TOKEN='.urlencode($paymentInfo['TOKEN']).'&PAYERID='.urlencode($paymentInfo['PAYERID']).'&PAYMENTACTION='.urlencode($paymentType).'&AMT='.urlencode($paymentInfo['ORDERTOTAL']).'&CURRENCYCODE='.urlencode($currencyCode).'&IPADDRESS='.urlencode($serverName);
		$resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
		return $resArray;
	}

	function CreateRecurringPayments($paymentInfo=array()){

        $firstName =urlencode($paymentInfo['Member']['first_name']);
        $lastName =urlencode($paymentInfo['Member']['last_name']);
        $email =urlencode($paymentInfo['Member']['email']);
        $creditCardType =urlencode($paymentInfo['CreditCard']['credit_type']);
        $creditCardNumber = urlencode($paymentInfo['CreditCard']['card_number']);
        $expDateMonth =urlencode($paymentInfo['CreditCard']['expiration_month']);
        $padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);
        $expDateYear =urlencode($paymentInfo['CreditCard']['expiration_year']);
        $cvv2Number = urlencode($paymentInfo['CreditCard']['cv_code']);
        $address1 = urlencode($paymentInfo['Member']['billing_address']);
        $address2 = urlencode($paymentInfo['Member']['billing_address2']);
        $country = urlencode($paymentInfo['Member']['billing_country']);
        $city = urlencode($paymentInfo['Member']['billing_city']);
        $state =urlencode($paymentInfo['Member']['billing_state']);
        $zip = urlencode($paymentInfo['Member']['billing_zip']);

        $description = urlencode($paymentInfo['Description']);
        $billingPeriod =urlencode($paymentInfo['Billing']['Period']);
        $billingFrequency =urlencode($paymentInfo['Billing']['Frequency']);
        $trialBillingPeriod =urlencode($paymentInfo['Trial']['Period']);
        $trialBillingFrequency =urlencode($paymentInfo['Trial']['Frequency']);
        $amt = urlencode($paymentInfo['Order']['theTotal']);
        $trialAmt = urlencode($paymentInfo['Order']['trialAmount']);
        $failedInitAmtAction = urlencode("CancelOnFailure");//urlencode("ContinueOnFailure");
        $autoBillAmt = urlencode("AddToNextBilling");
        $profileReference = urlencode("Anonymous");
        $cycles = urlencode($paymentInfo['Billing']['Cycles']);

        $currencyCode="USD";

        $startDate = urlencode(date('Y-m-d',time()+3600+24*30).'T00:00:00Z');

        $ip=$_SERVER['REMOTE_ADDR'];

        //set initial amount to recurring amount if not set
        //I think Paypal changed something and auto charges an init amount now (11/15/2011)
        /*
        if (!array_key_exists("init_amt",$paymentInfo['Order'])) {
        	$initAmt = $amt;
        }else {
        	$initAmt = $paymentInfo['Order']['init_amt'];
        }
        */
        $initAmt = "";


        /* Construct the request string that will be sent to PayPal.
           The variable $nvpstr contains all the variables and is a
           name value pair string with & as a delimiter */
        $nvpstr="&EMAIL=$email&DESC=$description&IPADDRESS=$ip&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear;
        $nvpstr.="&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITY=$city&STATE=$state&ZIP=$zip";
        $nvpstr.="&COUNTRYCODE=$country&CURRENCYCODE=$currencyCode&PROFILESTARTDATE=$startDate&BILLINGPERIOD=$billingPeriod&BILLINGFREQUENCY=$billingFrequency&AMT=$amt";
        $nvpstr.='&INITAMT='.$initAmt.'&AUTOBILLAMT='.$autoBillAmt.'&PROFILEREFERENCE='.$profileReference.'&FAILEDINITAMTACTION='.$failedInitAmtAction.'&DESC='.$description;
        $nvpstr.="&TRIALBILLINGPERIOD=$trialBillingPeriod&TRIALBILLINGFREQUENCY=$trialBillingFrequency&TRIALAMT=$trialAmt&TRIALTOTALBILLINGCYCLES=1&MAXFAILEDPAYMENTS=1&TOTALBILLINGCYCLES=$cycles";

        /* Make the API call to PayPal, using API signature.
           The API response is stored in an associative array called $resArray */
        $resArray=$this->hash_call("CreateRecurringPaymentsProfile",$nvpstr);

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */

//echo "<br/><br/>";print_r($resArray);exit();

        return $resArray;
        //Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
    }

    function GetRecurringPaymentsProfileDetails($profileId){

    	/* Construct the request string that will be sent to PayPal.
           The variable $nvpstr contains all the variables and is a
           name value pair string with & as a delimiter */
        $nvpstr="&PROFILEID=$profileId";

    	/* Make the API call to PayPal, using API signature.
           The API response is stored in an associative array called $resArray */
        $resArray=$this->hash_call("GetRecurringPaymentsProfileDetails",$nvpstr);

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */

    	return $resArray;
    }

    function UpdateRecurringPaymentsProfile($paymentInfo=array()){

    	$profileId =urlencode($paymentInfo['ProfileId']);
        if (array_key_exists("Member",$paymentInfo)) {
        	if (array_key_exists("first_name",$paymentInfo['Member'])) $firstName =urlencode($paymentInfo['Member']['first_name']);
        	if (array_key_exists("last_name",$paymentInfo['Member'])) $lastName =urlencode($paymentInfo['Member']['last_name']);
        	if (array_key_exists("email",$paymentInfo['Member'])) $email =urlencode($paymentInfo['Member']['email']);
        	if (array_key_exists("Member",$paymentInfo)) if (array_key_exists("billing_address",$paymentInfo['Member'])) $address1 = urlencode($paymentInfo['Member']['billing_address']);
	        if (array_key_exists("billing_address2",$paymentInfo['Member'])) $address2 = urlencode($paymentInfo['Member']['billing_address2']);
	        if (array_key_exists("billing_country",$paymentInfo['Member'])) $country = urlencode($paymentInfo['Member']['billing_country']);
	        if (array_key_exists("billing_city",$paymentInfo['Member'])) $city = urlencode($paymentInfo['Member']['billing_city']);
        	if (array_key_exists("billing_state",$paymentInfo['Member'])) $state =urlencode($paymentInfo['Member']['billing_state']);
        	if (array_key_exists("billing_zip",$paymentInfo['Member'])) $zip = urlencode($paymentInfo['Member']['billing_zip']);
        }
        if (array_key_exists("CreditCard",$paymentInfo)){
        	if (array_key_exists("credit_type",$paymentInfo['CreditCard'])) $creditCardType =urlencode($paymentInfo['CreditCard']['credit_type']);
        	if (array_key_exists("card_number",$paymentInfo['CreditCard'])) $creditCardNumber = urlencode($paymentInfo['CreditCard']['card_number']);
	        if (array_key_exists("expiration_month",$paymentInfo['CreditCard'])) $expDateMonth =urlencode(str_pad($paymentInfo['CreditCard']['expiration_month'], 2, '0', STR_PAD_LEFT));
	        if (array_key_exists("expiration_year",$paymentInfo['CreditCard'])) $expDateYear =urlencode($paymentInfo['CreditCard']['expiration_year']);
	        if (array_key_exists("cv_code",$paymentInfo['CreditCard'])) $cvv2Number = urlencode($paymentInfo['CreditCard']['cv_code']);
        }
        if (array_key_exists("Order",$paymentInfo)){
			if (array_key_exists("theTotal",$paymentInfo['Order'])) $amt = urlencode($paymentInfo['Order']['theTotal']);
			if (array_key_exists("trialAmount",$paymentInfo['Order'])) $trialAmt = urlencode($paymentInfo['Order']['trialAmount']);
        }

        $description = urlencode('Advertiser');
        $billingPeriod =urlencode('Month');
        $billingFrequency =urlencode('1');
        $trialBillingPeriod =urlencode('Month');
        $trialBillingFrequency =urlencode('1');
        $failedInitAmtAction = urlencode("ContinueOnFailure");
        $autoBillAmt = urlencode("AddToNextBilling");
        $profileReference = urlencode("Anonymous");

        $currencyCode="USD";

        $ip=$_SERVER['REMOTE_ADDR'];

        /* Construct the request string that will be sent to PayPal.
           The variable $nvpstr contains all the variables and is a
           name value pair string with & as a delimiter */
        $nvpstr="&PROFILEID=$profileId&EMAIL=$email&DESC=$description&IPADDRESS=$ip&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear;
        $nvpstr.="&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITY=$city&STATE=$state&ZIP=$zip";
        $nvpstr.="&COUNTRYCODE=$country&CURRENCYCODE=$currencyCode&PROFILESTARTDATE=$startDate&BILLINGPERIOD=$billingPeriod&BILLINGFREQUENCY=$billingFrequency&AMT=$amt";
        $nvpstr.='&AUTOBILLAMT='.$autoBillAmt.'&PROFILEREFERENCE='.$profileReference.'&FAILEDINITAMTACTION='.$failedInitAmtAction.'&DESC='.$description;
        $nvpstr.="&TRIALBILLINGPERIOD=$trialBillingPeriod&TRIALBILLINGFREQUENCY=$trialBillingFrequency&TRIALAMT=$trialAmt&TRIALTOTALBILLINGCYCLES=1&MAXFAILEDPAYMENTS=1";

        /* Make the API call to PayPal, using API signature.
           The API response is stored in an associative array called $resArray */
        $resArray=$this->hash_call("UpdateRecurringPaymentsProfile",$nvpstr);

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */

//echo "<br/><br/>";print_r($resArray);exit();

        return $resArray;
        //Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
    }

    function ManageRecurringPaymentsProfileStatus($paymentInfo=array()){

    	$profileId =urlencode($paymentInfo['ProfileId']);
        $action =urlencode($paymentInfo['Action']);
        $note = (array_key_exists("Note",$paymentInfo))?urlencode($paymentInfo['Note']):"";

        $ip=$_SERVER['REMOTE_ADDR'];

        /* Construct the request string that will be sent to PayPal.
           The variable $nvpstr contains all the variables and is a
           name value pair string with & as a delimiter */
        $nvpstr="&PROFILEID=$profileId&ACTION=$action&NOTE=$note";

        /* Make the API call to PayPal, using API signature.
           The API response is stored in an associative array called $resArray */
        $resArray=$this->hash_call("ManageRecurringPaymentsProfileStatus",$nvpstr);

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */

//echo "<br/><br/>";print_r($resArray);exit();

        return $resArray;
        //Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
    }

	function RefundTransaction($paymentInfo=array()){

		$transactionId =urlencode($paymentInfo['TransactionId']);
		$refundType =(array_key_exists("RefundType",$paymentInfo))?urlencode($paymentInfo['RefundType']):"Full";
		$amt = (array_key_exists("Amt",$paymentInfo))?urlencode($paymentInfo['Amt']):"";
		$note = (array_key_exists("Note",$paymentInfo))?urlencode($paymentInfo['Note']):"";

		$ip=$_SERVER['REMOTE_ADDR'];

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		$nvpstr="&TRANSACTIONID=$transactionId&REFUNDTYPE=$refundType&AMT=$amt&NOTE=$note";

		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $resArray */
		$resArray=$this->hash_call("RefundTransaction",$nvpstr);

		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */

//echo "<br/><br/>";print_r($resArray);exit();

		return $resArray;
		//Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
	}

	function DoReferenceTransaction($paymentInfo=array()){
		/**
		 * Get required parameters from the web form for the request
		 */
		$referenceId =urlencode($paymentInfo['transaction_id']);
		$firstName =urlencode($paymentInfo['Member']['first_name']);
		$lastName =urlencode($paymentInfo['Member']['last_name']);
		$description = urlencode($paymentInfo['Description']);
		$amount = urlencode($paymentInfo['Order']['theTotal']);
		$currencyCode="USD";
		$paymentType=urlencode('Sale');
		$ip=$_SERVER['REMOTE_ADDR'];

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		// $nvpstr="&PAYMENTACTION=Sale&IPADDRESS=$ip&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear."&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITYNAME=$city&STATEORPROVINCE=$state".
		// 		"&POSTALCODE=$zip&COUNTRY=$country&CURRENCYCODE=$currencyCode";

		$nvpstr="&PAYMENTACTION=$paymentType&IPADDRESS=$ip&REFERENCEID=$referenceId&AMT=$amount&FIRSTNAME=$firstName&LASTNAME=$lastName&CURRENCYCODE=$currencyCode&DESC=$description";

		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $resArray */
		$resArray=$this->hash_call("doReferenceTransaction",$nvpstr);

		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */

		return $resArray;
		//Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
	}

	function APIError($errorNo,$errorMsg,$resArray){
		$resArray['Error']['Number']=$errorNo;
		$resArray['Error']['Number']=$errorMsg;
		return $resArray;
	}

	function hash_call($methodName,$nvpStr)
	{
		require_once 'paypal_constants.php';

		$API_UserName=API_USERNAME;
		$API_Password=API_PASSWORD;
		$API_Signature=API_SIGNATURE;
		$API_Endpoint =API_ENDPOINT;
		$version=VERSION;

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	    //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php

		if(USE_PROXY)
			curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);

		//NVPRequest for submitting to server
		$nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($version)."&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature).$nvpStr;
//echo $nvpreq;
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		//convrting NVPResponse to an Associative Array
		$nvpResArray=$this->deformatNVP($response);
		$nvpReqArray=$this->deformatNVP($nvpreq);

		if (curl_errno($ch))
			$nvpResArray = $this->APIError(curl_errno($ch),curl_error($ch),$nvpResArray);
		else
			curl_close($ch);

		return $nvpResArray;
	}

	/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	  */

	function deformatNVP($nvpstr)
	{

		$intial=0;
	 	$nvpArray = array();


		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}
}
?>
