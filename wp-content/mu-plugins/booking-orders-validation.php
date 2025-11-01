<?php
/**
 * Plugin Name: Booking Orders Validation
 * Description: Validates JetFormBuilder booking submissions and stores sanitized meta for booking_orders posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'jet-form-builder/action/after-post-insert', function( $handler ) {
    if ( ! $handler || ! method_exists( $handler, 'get_inserted_post_id' ) ) {
        return;
    }

    $default_form_id = 457;
    $target_form_id  = apply_filters( 'booking_orders_validation_form_id', $default_form_id );

    if ( method_exists( $handler, 'get_form_id' ) ) {
        $form_id = (int) $handler->get_form_id();
        if ( $form_id !== (int) $target_form_id ) {
            return;
        }
    }

    $booking_post_id = (int) $handler->get_inserted_post_id();
    if ( ! $booking_post_id || 'booking_orders' !== get_post_type( $booking_post_id ) ) {
        return;
    }

    if ( is_user_logged_in() && ! current_user_can( 'edit_post', $booking_post_id ) ) {
        return;
    }

    $request = method_exists( $handler, 'get_request' ) ? (array) $handler->get_request() : array();

    $base_price_raw = isset( $request['base_price'] ) && is_scalar( $request['base_price'] ) ? trim( (string) $request['base_price'] ) : '';
    $base_price     = is_numeric( $base_price_raw ) ? (float) $base_price_raw : null;
    if ( null === $base_price || $base_price <= 0 ) {
        error_log( sprintf(
            'Booking post ID %d – base_price invalid (value: %s). Defaulted to 0.',
            $booking_post_id,
            $base_price_raw
        ) );
        $base_price = 0.0;
    }
    update_post_meta( $booking_post_id, 'base_price', $base_price );

    $distance_raw = isset( $request['distance_km'] ) && is_scalar( $request['distance_km'] ) ? trim( (string) $request['distance_km'] ) : '';
    $distance     = is_numeric( $distance_raw ) ? (float) $distance_raw : null;
    if ( null === $distance || $distance < 1 ) {
        error_log( sprintf(
            'Booking post ID %d – distance_km invalid (value: %s). Defaulted to 1.',
            $booking_post_id,
            $distance_raw
        ) );
        $distance = 1.0;
    }
    update_post_meta( $booking_post_id, 'distance_km', $distance );

    $floors_source_key = '';
    if ( isset( $request['floors'] ) && is_scalar( $request['floors'] ) ) {
        $floors_source_key = 'floors';
    } elseif ( isset( $request['num_floors'] ) && is_scalar( $request['num_floors'] ) ) {
        // Backward compatibility with earlier form field naming.
        $floors_source_key = 'num_floors';
    }

    $floors_raw = $floors_source_key ? trim( (string) $request[ $floors_source_key ] ) : '';
    $floors     = is_numeric( $floors_raw ) ? (int) $floors_raw : null;
    if ( null === $floors || $floors < 0 ) {
        error_log( sprintf(
            'Booking post ID %d – floors invalid (value: %s). Defaulted to 0.',
            $booking_post_id,
            $floors_raw
        ) );
        $floors = 0;
    }

    update_post_meta( $booking_post_id, 'floors', $floors );

    // Persist the legacy meta key to avoid breaking any downstream dependencies.
    update_post_meta( $booking_post_id, 'num_floors', $floors );

    if ( ! metadata_exists( 'post', $booking_post_id, '_booking_initial_submission_date' ) ) {
        update_post_meta( $booking_post_id, '_booking_initial_submission_date', current_time( 'timestamp' ) );
    }

    $service_id_raw = isset( $request['service_id'] ) && is_scalar( $request['service_id'] ) ? $request['service_id'] : '';
    update_post_meta( $booking_post_id, '_linked_service_id', absint( $service_id_raw ) );

    if ( isset( $request['appointment_date'] ) && is_scalar( $request['appointment_date'] ) ) {
        update_post_meta( $booking_post_id, 'appointment_date', sanitize_text_field( $request['appointment_date'] ) );
    }

    if ( isset( $request['appointment_time'] ) && is_scalar( $request['appointment_time'] ) ) {
        update_post_meta( $booking_post_id, 'appointment_time', sanitize_text_field( $request['appointment_time'] ) );
    }

    if ( isset( $request['van_size'] ) && is_scalar( $request['van_size'] ) ) {
        update_post_meta( $booking_post_id, 'van_size', sanitize_text_field( $request['van_size'] ) );
    }
}, 10, 1 );
