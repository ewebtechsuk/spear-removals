<?php
/**
 * Purpose: Track initial publish date and validate packages on services CPT.
 */

add_action( 'save_post_services', 'spear_services_handle_publish_and_packages', 20, 3 );

function spear_services_handle_publish_and_packages( $post_id, $post, $update ) {
	// Abort early if doing autosave, revision, or incorrect post type.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'services' !== get_post_type( $post_id ) ) {
		return;
	}

	// Check the current user has permission to edit this post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Validate nonce when available from the standard edit screen submission.
	if ( ! empty( $_POST ) ) {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'update-post_' . $post_id ) ) {
			return;
		}
	}

	// Continue only if the post is being saved with publish status.
	if ( 'publish' !== $post->post_status ) {
		return;
	}

	// Record the initial publish timestamp if it hasn't been stored yet.
	$initial_publish = get_post_meta( $post_id, '_service_initial_publish_date', true );
	if ( empty( $initial_publish ) ) {
		update_post_meta( $post_id, '_service_initial_publish_date', current_time( 'timestamp' ) );
	}

	// Ensure JetEngine is available before accessing its meta API.
	if ( ! function_exists( 'jet_engine' ) ) {
		return;
	}

	$meta_boxes = jet_engine()->meta_boxes;
	if ( ! $meta_boxes ) {
		return;
	}

	// Fetch packages repeater data via JetEngine meta boxes API.
	$packages = $meta_boxes->get_meta( 'packages', $post_id );
	if ( empty( $packages ) || ! is_array( $packages ) ) {
		return;
	}

	$needs_update = false;

	// Validate each package price, ensuring it is numeric and positive.
	foreach ( $packages as $index => $package ) {
		$price_raw = isset( $package['price'] ) ? $package['price'] : '';
		$price     = is_numeric( $price_raw ) ? (float) $price_raw : 0;

		if ( $price <= 0 ) {
			error_log( sprintf( 'Service ID %d package index %d has invalid price (%s). Defaulting to £0.', $post_id, $index, is_scalar( $price_raw ) ? $price_raw : 'non-scalar value' ) );
			$price = 0;
		}

		if ( ! isset( $packages[ $index ]['price'] ) || (float) $packages[ $index ]['price'] !== $price ) {
			$packages[ $index ]['price'] = $price;
			$needs_update               = true;
		}
	}

	// Persist any corrected package data through JetEngine (fallback to update_post_meta).
	if ( $needs_update ) {
		if ( method_exists( $meta_boxes, 'update_meta' ) ) {
			$meta_boxes->update_meta( 'packages', $packages, $post_id );
		} else {
			update_post_meta( $post_id, 'packages', $packages );
		}
	}
}
