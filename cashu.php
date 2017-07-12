<?php



	hbm_create('CashU',array(
            'description'=>'CashU Module for HostBill by ArabHosters',
            'version'=>'1.0',
            'currencies'=>array('USD','EGP','SAR')
        ));
    



	hbm_add_config_option('Merchant ID');
	hbm_add_config_option('Return URL');	
	
	hbm_add_config_option('Keyword Encryption');
	  hbm_add_config_option('Test Mode',array(
		'type'=>'check',
		'default'=>'0',
		'description'=>'Tick if you wish to use test mode'
	    ));


    hbm_on_action('payment.displayform', function($details){

        $hostbill_details = hbm_get_hostbill_details();
        $cashu_url = 'https://www.cashu.com/cgi-bin/pcashu.cgi';
        $merchant_id=hbm_get_config_option('Merchant ID');
	$keyword_encryption=hbm_get_config_option('Keyword Encryption');
	$test_mode=hbm_get_config_option('Test Mode');
        //This will create url to callback route created below
        $callback_url = hbm_client_url('callback');
       
        
	$token = md5(strtolower($merchant_id).":".strtolower($details['invoice']['amount']).":".strtolower($details['invoice']		['currency']).":".strtolower($keyword_encryption));

        $form =    '<form action="'.$cashu_url .'" method="post" accept-charset="iso-8859-1" >
			<input type="hidden" value="'.$callback_url.'" name="notify_url">
			<input type="hidden" name="merchant_id" value="'.$merchant_id.'">
			<input type="hidden" name="token" value="'.$token.'">
			<input type="hidden" name="display_text" value="'.$details['invoice']['description'].'">
			<input type="hidden" name="currency" value="'.$details['invoice']['currency'].'">
			<input type="hidden" name="amount" value="'.$details['invoice']['amount'].'">
			<input type="hidden" name="language" value="ar">
			<input type="hidden" name="txt1" value="'.$details['invoice']['id'].'">
			<input type="hidden" name="txt2" value="'.$details['invoice']['description'].'">
			<input type="hidden" name="test_mode" value="'.$test_mode.'">
			<input type="submit" value="ادفع الآن">
			</form>';
      

     
        return $form;
    });


	hbm_client_route('callback',function($request) {
		//verify that request is valid and comes from gateway
		//log callback in gateway log:

		$merchant_id=hbm_get_config_option('Merchant ID');
		$return_url=hbm_get_config_option('Return URL');
		$keyword_encryption=hbm_get_config_option('Keyword Encryption');

		$verificationString = sha1(strtolower($merchant_id).":".$_POST['trn_id'].":".strtolower($keyword_encryption));

$token = md5(strtolower($merchant_id).":".strtolower($_POST['amount']).":".strtolower($_POST['currency']).":".strtolower($keyword_encryption));


		if($verificationString == $_POST['verificationString'] && $token == $_POST['token'])
		{

			hbm_log_callback($_POST,'Successfull');

			$fee = $_POST['amount'] - $_POST['netAmount'];
			hbm_add_transaction( $_POST['txt1'],$_POST['amount'],array(
                                'description' => $_POST['txt2'],
                                'fee' => $fee,
                                'transaction_id' => $_POST['trn_id']
                            ));

 echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$return_url.$_POST['txt1'].'"/>';  


		}

	});


