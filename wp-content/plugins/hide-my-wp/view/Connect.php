<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
} ?>
<div id="hmwp_wrap" class="d-flex flex-row p-0 my-3">
    <div class="hmwp_row d-flex flex-row p-0 m-0">
		<?php do_action( 'hmwp_notices' ); ?>
        <div class="hmwp_col flex-grow-1 px-2 py-0 mr-2 mb-3" >
            <form method="POST">
				<?php wp_nonce_field( 'hmwp_connect', 'hmwp_nonce' ) ?>
                <input type="hidden" name="action" value="hmwp_connect"/>

				<?php do_action( 'hmwp_form_notices' ); ?>

                <div id="connect" class="col-sm-12 p-0 m-0">
                    <div class="card col-sm-12 p-0 m-0">
                        <h3 class="card-title hmwp_header p-2 m-0"><?php echo esc_html__( 'Activate Your Plugin', 'hide-my-wp' ); ?></h3>
                        <div class="card-body">

                            <div class="col-sm-12 row border-bottom border-light py-3 mx-0 my-3">
                                <div class="col-sm-4 p-1 font-weight-bold">
                                    <?php echo esc_html__( 'Licence Token', 'hide-my-wp' ); ?>:
                                    <div class="small text-black-50"><?php echo sprintf( esc_html__( 'Enter the 32 chars token from Order/Licence on %s', 'hide-my-wp' ), '<a href="' . esc_url(_HMWP_ACCOUNT_SITE_) . '/user/orders" target="_blank" style="font-weight: bold">' . esc_url(_HMWP_ACCOUNT_SITE_) . '</a>' ); ?></div>
                                </div>
                                <div class="col-sm-8 p-0 input-group ">
                                    <input type="text" class="form-control" name="hmwp_token" value=""/>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-sm-12 my-3 p-0">
                        <button type="submit" class="btn rounded-0 btn-success btn-lg px-5 save"><?php echo esc_html__( 'Activate', 'hide-my-wp' ); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="hmwp_col hmwp_col_side px-2 py-0 mr-2">
            <div class="card col-sm-12 m-0 p-0 rounded-0">
                <div class="card-body f-gray-dark text-left border-bottom">
                    <h3 class="card-title"><?php echo esc_html__( 'Activation Help', 'hide-my-wp' ); ?></h3>
                    <div class="text-info my-3">
						<?php echo sprintf( esc_html__( "Once you bought the plugin, you will receive the %s credentials for your account by email.", 'hide-my-wp' ), esc_html(HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' )) ); ?>
                    </div>
                    <div class="text-info my-3">
						<?php echo sprintf( esc_html__( "Please visit %s to check your purchase and to get the license token.", 'hide-my-wp' ), '<a href="' . esc_url(_HMWP_ACCOUNT_SITE_) . '" target="_blank" style="font-weight: bold">' . esc_url(_HMWP_ACCOUNT_SITE_) . '</a>' ); ?>
                    </div>
                    <div class="text-info my-3">
						<?php echo sprintf( esc_html__( 'By activating, you agree to our %s Terms of Use %s and %sPrivacy Policy%s', 'hide-my-wp' ), '<a href="' . esc_url(HMWP_Classes_Tools::getOption( 'hmwp_plugin_website' )) . '/terms-of-use/" target="_blank" style="font-weight: bold">', '</a>', '<a href="' . esc_url(HMWP_Classes_Tools::getOption( 'hmwp_plugin_website' )) . '/privacy-policy/" target="_blank" style="font-weight: bold">', '</a>' ); ?>
                    </div>

                    <div class="text-info my-3">
						<?php echo sprintf( esc_html__( "%sNOTE:%s If you didn't receive the credentials, please access %s.", 'hide-my-wp' ), '<strong>', '</strong>', '<a href="' . esc_url(_HMWP_ACCOUNT_SITE_) . '/lostpass" target="_blank" style="font-weight: bold">https://wpplugins.tips/lostpass</a>' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
