<?php
/**
** A base module for [phonetext] and [phonetext*]
**/

/* form_tag handler */

add_action( 'wpcf7_init', 'sncpf_add_form_tag_phonetext' );

function sncpf_add_form_tag_phonetext() {
	wpcf7_add_form_tag(
		array( 'phonetext', 'phonetext*'),
		'sncpf_phonetext_form_tag_handler', array( 'name-attr' => true ) );
}

function sncpf_phonetext_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

	if ( in_array( $tag->basetype, array( 'phonetext' , 'phonetext*') ) ) {
		$class .= ' wpcf7-validates-as-' . $tag->basetype;
	}

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if ( $atts['maxlength'] && $atts['minlength']
	&& $atts['maxlength'] < $atts['minlength'] ) {
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$atts['autocomplete'] = $tag->get_option( 'autocomplete',
		'[-0-9a-zA-Z]+', true );

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}
	
	if ( $tag->has_option( 'numberonly') ) {
		$atts['data-numberonly'] = 'true';
	}
	
	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid'] = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	} else {
		$atts['aria-invalid'] = 'false';
	}

	//$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;
	$atts['type'] = 'text';
	

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	$atts_country_code = array();
	$atts_country_code['type'] = 'hidden';
	$atts_country_code['name'] = $tag->name . '-country-code';
	$atts_country_code['class'] = 'wpcf7-phonetext-country-code';

	$atts_country_code = wpcf7_format_atts( $atts_country_code );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s /><input %3$s />%4$s</span>',
		sanitize_html_class( $tag->name ), $atts,  $atts_country_code, $validation_error);

	return $html;
}


/* Validation filter */

add_filter( 'wpcf7_validate_phonetext', 'sncpf_phonetext_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_phonetext*', 'sncpf_phonetext_validation_filter', 10, 2 );

function sncpf_phonetext_validation_filter( $result, $tag ) {
	$type = $tag->type;
	$name = $tag->name;
	$extension = $_POST[$name.'-country-code'];

	$value = isset( $_POST[$name] ) ? (string) wp_unslash($_POST[$name]) : '';
    $value = str_replace($extension , '', str_replace(" ", "" , $value));
    $str_array = str_split($value);

	if ( ( $tag->is_required() && '' == $value ) || ($value == $extension)) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}
	elseif ( $tag->has_option( 'numberonly') ) {
		if ( '' != $value && ( ! is_numeric($value) ) ) {
			$result->invalidate( $tag, __('Phone number must be numbers only', 'sn-pcf') );
		}
		else {

			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();
	
			if ( $maxlength and $minlength and $maxlength < $minlength ) {
				$maxlength = $minlength = null;
			}
	
			$code_units = wpcf7_count_code_units( stripslashes( $value ) );
	
			if ( false !== $code_units ) {
				if ( $maxlength and $maxlength < $code_units ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
				} elseif ( $minlength and $code_units < $minlength ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
				}
			}
		}
	}
	else {

		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();

		if ( $maxlength and $minlength and $maxlength < $minlength ) {
			$maxlength = $minlength = null;
		}

		$code_units = wpcf7_count_code_units( stripslashes( $value ) );

		if ( false !== $code_units ) {
			if ( $maxlength and $maxlength < $code_units ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
			} elseif ( $minlength and $code_units < $minlength ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
			}
		}
	}
	
	
	return $result;
}


/* Tag generator */

add_action( 'wpcf7_admin_init', 'sncpf_add_tag_generator_phonetext', 20 );

function sncpf_add_tag_generator_phonetext() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'phonetext', __( 'phone number', 'sn-pcf' ),
		'sncpf_tag_generator_phonetext' );
}

function sncpf_tag_generator_phonetext( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'phonetext';

	$description = __( "Generate a form-tag for a phone number with flags icon text input field.", 'sn-pcf' );

	//$desc_link = wpcf7_link( __( 'https://contactform7.com/text-fields/', 'sn-pcf' ), __( 'Text Fields', 'sn-pcf' ) );
	$desc_link = '';
?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field type', 'sn-pcf' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'sn-pcf' ) ); ?></legend>
		<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'sn-pcf' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'sn-pcf' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'sn-pcf' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
	<label><input type="checkbox" name="placeholder" class="option" /> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'sn-pcf' ) ); ?></label></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'sn-pcf' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'sn-pcf' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>
	
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field Validation', 'sn-pcf' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field Validation', 'sn-pcf' ) ); ?></legend>
		<label><input type="checkbox" name="numberonly" class="option"/> <?php echo esc_html( __( 'Number Only', 'sn-pcf' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'sn-pcf' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'sn-pcf' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
}