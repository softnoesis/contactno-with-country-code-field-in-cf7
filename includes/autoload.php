<?php 
/*
check contact form 7 plugin is active.
*/
if(class_exists('WPCF7')){
	require SN_CPF_PATH. 'includes/settings.php';
	require SN_CPF_PATH. 'includes/country-text.php';
	require SN_CPF_PATH. 'includes/phone-text.php';
	require SN_CPF_PATH. 'includes/include-js-css.php';
}