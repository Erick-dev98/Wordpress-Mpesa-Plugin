<?php

add_filter( 'woocommerce_gateway_description', 'emsoftwares_mpesa_description_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'emsoftwares_mpesa_description_fields_validation' );
add_action( 'woocommerce_checkout_update_order_meta', 'emsoftwares_checkout_update_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'emsoftwares_order_data_after_billing_address', 10, 1 );
add_action( 'woocommerce_order_item_meta_end', 'emsoftwares_order_item_meta_end', 10, 3 );

function emsoftwares_mpesa_description_fields( $description, $payment_id ) {

    if ( 'mpesa' !== $payment_id ) {
        return $description;
    }
    
    ob_start();

    echo '<div style="display: block; width:100%; height:auto;">';
    echo '<img src="' . plugins_url('../assets/icon2.png', __FILE__ ) . '"style="max-width: 100%;">';
    

    woocommerce_form_field(
        'payment_number',
        array(
            'type' => 'text',
            'label' =>__( 'Payment Phone Number', 'mpesa-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide', 'col-12' ),
            'required' => true,
            'placeholder' => __( 'e.g. 254712345678', 'mpesa-payments-woo' ),
        )
    );

    woocommerce_form_field(
        'paying_network',
        array(
            'type' => 'select',
            'label' => __( 'Payment Network', 'mpesa-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide', 'col-12' ),
            'required' => true,
            'options' => array(
                'none' => __( 'Select Phone Network', 'mpesa-payments-woo' ),
                'safaricom' => __( 'Safaricom', 'mpesa-payments-woo' ),
            ),
        )
    );

    echo '</div>';

    $description .= ob_get_clean();

    return $description;
}

function emsoftwares_mpesa_description_fields_validation() {
    if( 'mpesa' === $_POST['payment_method'] && ! isset( $_POST['payment_number'] )  || empty( $_POST['payment_number'] ) ) {
        wc_add_notice( 'Please enter a number that is to be billed', 'error' );
    }
}

function emsoftwares_checkout_update_order_meta( $order_id ) {
    if( isset( $_POST['payment_number'] ) || ! empty( $_POST['payment_number'] ) ) {
       update_post_meta( $order_id, 'payment_number', $_POST['payment_number'] );
    }
}

function emsoftwares_order_data_after_billing_address( $order ) {
    echo '<p><strong>' . __( 'Payment Phone Number:', 'mpesa-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'payment_number', true ) . '</p>';
}

function emsoftwares_order_item_meta_end( $item_id, $item, $order ) {
    echo '<p><strong>' . __( 'Payment Phone Number:', 'mpesa-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'payment_number', true ) . '</p>';
}