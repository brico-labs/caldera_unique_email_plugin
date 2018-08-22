<?php
/**
 * Plugin Name: Unique email Caldera Forms Field Validator
 */
add_filter('caldera_forms_get_form_processors', 'unique_email_cf_validator_processor');

/**
 * Add a custom processor for field validation
 *
 * @uses 'unique_email_cf_validator_processor'
 *
 * @param array $processors Processor configs
 *
 * @return array
 */
function unique_email_cf_validator_processor($processors){
    $processors['unique_email_cf_validator'] = array(
        'name' => __('Unique email validator', 'my-text-domain' ),
        'description' => 'Checks if one field is unique thoughout all the entries of the form',
        'pre_processor' => 'unique_email_validator',
        'template' => dirname(__FILE__) . '/config.php'

    );

    return $processors;
}

/**
 * Run field validation
 *
 * @param array $config Processor config
 * @param array $form Form config
 *
 * @return array|void Error array if needed, else void.
 */
function unique_email_validator( array $config, array $form ){

    //Processor data object
    $data = new Caldera_Forms_Processor_Get_Data( $config, $form, unique_email_cf_validator_fields() );

    //Value of field to be validated
    $value = $data->get_value( 'field-to-validate' );

    //get ID of field to put error on
    $fields = $data->get_fields();
    $field_id = $fields[ 'field-to-validate' ][ 'config_field' ];

    //if not valid, return an error
    if( false == unique_email_cf_validator_is_valid( $value, $form['ID'], $field_id, $form ) ){

        //Get label of field to use in error message above form
        $field = $form[ 'fields' ][ $field_id ];
        $label = $field[ 'label' ];

        //this is error data to send back
        return array(
            'type' => 'error',
            //this message will be shown above form
            'note' => sprintf( 'Este %s ya está registrado, si tienes problemas, ponte en contacto con nostros a través de info@oshwdem.org. ', $label),
            //Add error messages for any form field
            'fields' => array(
                //This error message will be shown below the field that we are validating
                $field_id => __( sprintf('Este %s ya está registrado', $label), 'text-domain' )
            )
        );
    }

    //If everything is good, don't return anything!

}


/**
 * Check if value is valid
 *
 * UPDATE THIS! Use your array of values, or query the database here.
 *
 * @return bool
 */
function unique_email_cf_validator_is_valid( $value , $form_id, $field_id, $form ){
    $data = Caldera_Forms_Admin::get_entries( $form_id, 1, 9999999 );

    foreach ( (array)$data['entries'] as $entry ) {
        $entry_id = $entry['_entry_id'];
        $entry_obj = new Caldera_Forms_Entry( $form, $entry_id );
        if (strtoupper($entry_obj->get_field( $field_id )->value) == strtoupper($value) )
            return false;
    }

    return true;
}

/**
 * Processor fields
 *
 * @return array
 */
function unique_email_cf_validator_fields(){
    return array(
        array(
            'id' => 'field-to-validate',
            'type' => 'text',
            'required' => true,
            'magic' => true,
            'label' => __( 'Magic tag for field to validate.', 'my-text-domain' )
        ),
    );
}