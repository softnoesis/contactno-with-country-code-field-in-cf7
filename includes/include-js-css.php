<?php

/* Include all js and css files for active theme */
function sn_cpf_embedCssJs() {

    wp_enqueue_style( 'sncpf-intlTelInput-style', SN_CPF_URL . 'assets/css/intlTelInput.min.css' );
	wp_enqueue_style( 'sncpf-countryFlag-style', SN_CPF_URL . 'assets/css/countrySelect.min.css' );
	wp_enqueue_script( 'sncpf-intlTelInput-script', SN_CPF_URL . 'assets/js/intlTelInput.min.js', array( 'jquery' ), false, true );
	wp_enqueue_script( 'sncpf-countryFlag-script', SN_CPF_URL . 'assets/js/countrySelect.min.js', array( 'jquery' ), false, true );

	wp_localize_script( 'sncpf-countryFlag-script', 'sncpf', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
	) );
	
	$sn_cpf_settings_options = get_option( 'sn_cpf_options' );
	$IPaddress  =   $_SERVER['REMOTE_ADDR'];
	
	if(isset( $sn_cpf_settings_options['defaultCountry'] ) && $sn_cpf_settings_options['defaultCountry'] !=''){
		$defaultCountry = 'defaultCountry: "'.strtolower( $sn_cpf_settings_options['defaultCountry'] ).'",';
		
	} else {
		$defaultCountry = '';
		
	}
	if(isset( $sn_cpf_settings_options['onlyCountries'] ) && $sn_cpf_settings_options['onlyCountries'] !=''){
		$onlyCountries = 'onlyCountries: '.json_encode(explode(',',$sn_cpf_settings_options['onlyCountries'])).',';
	}else{
		$onlyCountries = '';
	}
	if(isset( $sn_cpf_settings_options['preferredCountries'] ) && $sn_cpf_settings_options['preferredCountries'] !=''){
		$preferredCountries = 'preferredCountries: '.json_encode(explode(',',$sn_cpf_settings_options['preferredCountries'])).',';
	}else{
		$preferredCountries = '';
	}
	if(isset( $sn_cpf_settings_options['excludeCountries'] ) && $sn_cpf_settings_options['excludeCountries'] !=''){
		$excludeCountries = 'excludeCountries: '.json_encode(explode(',',$sn_cpf_settings_options['excludeCountries'])).',';
	}else{
		$excludeCountries = '';
	}
	
	// phone field settings

	if(isset( $sn_cpf_settings_options['phone_defaultCountry'] ) && $sn_cpf_settings_options['phone_defaultCountry'] !=''){
		$phone_defaultCountry = 'initialCountry: "'.strtolower( $sn_cpf_settings_options['phone_defaultCountry'] ).'",';
	} else {
		$phone_defaultCountry = '';
		
	}
	if(isset( $sn_cpf_settings_options['phone_onlyCountries'] ) && $sn_cpf_settings_options['phone_onlyCountries'] !=''){
		$phone_onlyCountries = 'onlyCountries: '.json_encode(explode(',',$sn_cpf_settings_options['phone_onlyCountries'])).',';
	}else{
		$phone_onlyCountries = '';
	}
	if(isset( $sn_cpf_settings_options['phone_preferredCountries'] ) && $sn_cpf_settings_options['phone_preferredCountries'] !=''){
		$phone_preferredCountries = 'preferredCountries: '.json_encode(explode(',',$sn_cpf_settings_options['phone_preferredCountries'])).',';
	}else{
		$phone_preferredCountries = '';
	}
	if(isset( $sn_cpf_settings_options['phone_excludeCountries'] ) && $sn_cpf_settings_options['phone_excludeCountries'] !=''){
		$phone_excludeCountries = 'excludeCountries: '.json_encode(explode(',',$sn_cpf_settings_options['phone_excludeCountries'])).',';
	}else{
		$phone_excludeCountries = '';
	}
	
	if(isset($sn_cpf_settings_options['phone_nationalMode']) && $sn_cpf_settings_options['phone_nationalMode'] == 1){
		$phone_nationalMode = 'true';
	}else {
		$phone_nationalMode = 'false';
	}
	
	$custom_inline_js = '';
	
	if(isset($phone_defaultCountry) && $phone_defaultCountry == ''){
		$custom_inline_js .= '';
	}

	if( ( isset( $sn_cpf_settings_options['country_auto_select'] ) && $sn_cpf_settings_options['country_auto_select'] == 1 ) || ( isset( $sn_cpf_settings_options['phone_auto_select'] ) && $sn_cpf_settings_options['phone_auto_select'] == 1 ) ){
		$custom_inline_js .= '
		(function($) {
			$(function() {

				function render_country_flags(){

					$(".wpcf7-countrytext").countrySelect({
						'.$defaultCountry.''.$onlyCountries.''.$preferredCountries.''.$excludeCountries.'
					});
					$(".wpcf7-phonetext").intlTelInput({
						autoHideDialCode: false,
						autoPlaceholder: "off",
						nationalMode: '.$phone_nationalMode.',
						separateDialCode: false,
						hiddenInput: "full_number",
						'.$phone_defaultCountry.''.$phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
					});
	
					$(".wpcf7-phonetext").each(function () {
						var hiddenInput = $(this).attr(\'name\');
						//console.log(hiddenInput);
						$("input[name="+hiddenInput+"-country-code]").val($(this).val());
					});
					
					$(".wpcf7-phonetext").on("countrychange", function() {
						// do something with iti.getSelectedCountryData()
						//console.log(this.value);
						var hiddenInput = $(this).attr("name");
						$("input[name="+hiddenInput+"-country-code]").val(this.value);
						
					});';
	
					if(! isset($sn_cpf_settings_options['phone_nationalMode']) || isset($sn_cpf_settings_options['phone_nationalMode']) && $sn_cpf_settings_options['phone_nationalMode'] != 1){
	
						$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
							var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
							if(dial_code == "")
							var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
							var value   = $(this).val();
							console.log(dial_code, value);
							$(this).val(dial_code + value.substring(dial_code.length));
						 });';
	
					}
	
					$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
						var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
						if(country_name == "")
						var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
						
						var value   = $(this).val();
						//console.log(country_name, value);
						$(this).val(country_name + value.substring(country_name.length));
					});
				}

				var ip_address = "";

				jQuery.ajax({
					//url: "https://ipwho.is/",
					url: "https://reallyfreegeoip.org/json/",
					success: function(response){
						
						//console.log(response);
						//var location = JSON.parse(response);
						console.log(response.country_code);
						if( response.country_code !== undefined){
							//console.log("here");
							$(".wpcf7-countrytext").countrySelect({';
							
							$custom_inline_js .= isset( $sn_cpf_settings_options['country_auto_select'] ) 
							&& $sn_cpf_settings_options['country_auto_select'] == 1 
							? 'defaultCountry: response.country_code.toLowerCase(),' : '';
							
							$custom_inline_js .= $onlyCountries.''.$preferredCountries.''.$excludeCountries.'
							});
							$(".wpcf7-phonetext").intlTelInput({
								autoHideDialCode: false,
								autoPlaceholder: "off",
								nationalMode: '.$phone_nationalMode.',
								separateDialCode: false,
								hiddenInput: "full_number",';
							$custom_inline_js .= isset( $sn_cpf_settings_options['phone_auto_select'] ) 
							&& $sn_cpf_settings_options['phone_auto_select'] == 1 ?
								'initialCountry: response.country_code.toLowerCase(),' : '';
							$custom_inline_js .= $phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
							});
							
							$(".wpcf7-phonetext").each(function () {
								var hiddenInput = $(this).attr(\'name\');
								//console.log(hiddenInput);
								$("input[name="+hiddenInput+"-country-code]").val($(this).val());
							});
							
							$(".wpcf7-phonetext").on("countrychange", function() {
								// do something with iti.getSelectedCountryData()
								//console.log(this.value);
								var hiddenInput = $(this).attr("name");
								$("input[name="+hiddenInput+"-country-code]").val(this.value);
								
							});';

							if(! isset($sn_cpf_settings_options['phone_nationalMode']) || isset($sn_cpf_settings_options['phone_nationalMode']) && $sn_cpf_settings_options['phone_nationalMode'] != 1){

								$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
									var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
									if(dial_code == "")
									var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
									var value   = $(this).val();
									console.log(dial_code, value);
									$(this).val(dial_code + value.substring(dial_code.length));
								});';

							}
			
							$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
								var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
								if(country_name == "")
								var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
								
								var value   = $(this).val();
								//console.log(country_name, value);
								$(this).val(country_name + value.substring(country_name.length));
							});

						} else {

							render_country_flags();

						}

					},
					error: function(){
						render_country_flags();
					}
				});
			});
		})(jQuery);';

	}else{ 

		$custom_inline_js .= '
		(function($) {
			$(function() {
				$(".wpcf7-countrytext").countrySelect({
					'.$defaultCountry.''.$onlyCountries.''.$preferredCountries.''.$excludeCountries.'
				});
				$(".wpcf7-phonetext").intlTelInput({
					autoHideDialCode: false,
					autoPlaceholder: "off",
					nationalMode: '.$phone_nationalMode.',
					separateDialCode: false,
					hiddenInput: "full_number",
					'.$phone_defaultCountry.''.$phone_onlyCountries.''.$phone_preferredCountries.''.$phone_excludeCountries.'	
				});

				$(".wpcf7-phonetext").each(function () {
					var hiddenInput = $(this).attr(\'name\');
					//console.log(hiddenInput);
					$("input[name="+hiddenInput+"-country-code]").val($(this).val());
				});
				
				$(".wpcf7-phonetext").on("countrychange", function() {
					// do something with iti.getSelectedCountryData()
					//console.log(this.value);
					var hiddenInput = $(this).attr("name");
					$("input[name="+hiddenInput+"-country-code]").val(this.value);
					
				});';

				if(! isset($sn_cpf_settings_options['phone_nationalMode']) || isset($sn_cpf_settings_options['phone_nationalMode']) && $sn_cpf_settings_options['phone_nationalMode'] != 1){

					$custom_inline_js .= '$(".wpcf7-phonetext").on("keyup", function() {
						var dial_code = $(this).siblings(".flag-container").find(".country-list li.active span.dial-code").text();
						if(dial_code == "")
						var dial_code = $(this).siblings(".flag-container").find(".country-list li.highlight span.dial-code").text();
						var value   = $(this).val();
						console.log(dial_code, value);
						$(this).val(dial_code + value.substring(dial_code.length));
					 });';

				}

				$custom_inline_js .= '$(".wpcf7-countrytext").on("keyup", function() {
					var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.active span.country-name").text();
					if(country_name == "")
					var country_name = $(this).siblings(".flag-dropdown").find(".country-list li.highlight span.country-name").text();
					
					var value   = $(this).val();
					//console.log(country_name, value);
					$(this).val(country_name + value.substring(country_name.length));
				});
				
			});
		})(jQuery);';
	
	}
	
	
	wp_add_inline_script('sncpf-countryFlag-script',$custom_inline_js );
    
}

add_action( 'wp_enqueue_scripts', 'sn_cpf_embedCssJs' );


add_action('wp_ajax_nopriv_auto_country_detection', 'sn_cpf_autoCountryDetection');
add_action('wp_ajax_auto_country_detection', 'sn_cpf_autoCountryDetection' );

function sn_cpf_autoCountryDetection(){

	$sn_cpf_settings_options = get_option( 'sn_cpf_options' );

	//$api_key = isset($sn_cpf_settings_options['ip_api_key']) && $sn_cpf_settings_options['ip_api_key'] != '' ? $sn_cpf_settings_options['ip_api_key'] : '3abce2be42d640a8a98e82806e32cd4f';
	//$api_key = '3abce2be42d640a8a98e82806e32cd4f';
	//$api_url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$api_key.'&fields=country_code2,country_name';

	$ip_address = $_REQUEST['ip'];
	if($ip_address != ''){
		$api_url = 'https://ipwho.is/'.$ip_address;
		$response = wp_safe_remote_get(
			$api_url,
			array(
				'timeout' => 3,
			)
		);
		//print_r($response);
		$response = wp_remote_retrieve_body( $response );

		
		if ( is_wp_error( $response ) ) {
				
			return false; //$error_message = $response->get_error_message();

		} else {
			
			$parse_json = json_decode($response, true);
			//print_r($parse_json);
			echo json_encode($parse_json);
			//$api_data = json_decode( $response['body'], true );
		}

		
	} else {
		return false;
	}

	wp_die();
	

}