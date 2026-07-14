<?php
// Filter for regular order transactions
if ( ! function_exists( 'custom_prefix_taxjar_orders' ) ) {
    add_filter( 'taxjar_get_order_transaction_id', 'custom_prefix_taxjar_orders', 10, 2 );
    function custom_prefix_taxjar_orders( $transaction_id, $order ) {
        return 'FRI-' . $transaction_id;
    }
}

// Filter for refund transactions
if ( ! function_exists( 'custom_prefix_taxjar_refunds' ) ) {
    add_filter( 'taxjar_get_refund_transaction_id', 'custom_prefix_taxjar_refunds', 10, 2 );
    function custom_prefix_taxjar_refunds( $transaction_id, $order ) {
        return 'FRI-' . $transaction_id;
    }
}
?>