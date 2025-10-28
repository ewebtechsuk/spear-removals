<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/** @var HMWP_Models_Presets $presetsModel */
$presetsModel = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Presets' );
?>
<div id="hmwp_wrap" class="d-flex flex-row p-0 my-3">
    <div class="hmwp_row d-flex flex-row p-0 m-0">
        <div class="hmwp_col flex-grow-1 px-2 py-0 mr-2 mb-3">

            <div class="card col-sm-12 p-0 m-0">
                <h3 class="card-title hmwp_header p-2 m-0"><?php echo esc_html__( 'Preset Security', 'hide-my-wp' ); ?>
                    <a href="<?php echo esc_url( HMWP_Classes_Tools::getOption('hmwp_plugin_website') . '/kb/preset-security-options/' ) ?>" target="_blank" class="d-inline-block float-right mr-2" style="color: white"><i class="dashicons dashicons-editor-help"></i></a>
                </h3>
                <div class="card-body">
                    <div class="text-black-50 mb-2"><?php echo esc_html__( "Select a preset security settings we've tested on most websites.", 'hide-my-wp' ); ?></div>
                    <form method="POST">
                        <?php wp_nonce_field( 'hmwp_preset', 'hmwp_nonce' ); ?>
                        <input type="hidden" name="action" value="hmwp_preset"/>
                        <div class="col-sm-9 p-0 input-group mb-1">
                            <select name="hmwp_preset_settings" class="selectpicker form-control" onchange="jQuery('.detail').hide(); jQuery('#detail_preset' + this.value).show();">
                                <option value=""><?php echo esc_html__( "Select Preset", 'hide-my-wp' ) ?></option>
                                <?php foreach ( $presetsModel->getPresetsSelect() as $index => $presetSelect ) { ?>
                                    <option value="<?php echo esc_attr( $index ) ?>"><?php echo esc_html( $presetSelect ) ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php foreach ( $presetsModel->getPresetsSelect() as $index => $presetSelect ) { ?>
                            <div id="detail_preset<?php echo esc_attr( $index ) ?>" class="detail" style="display: none">
                                <h6 class="my-3">
                                    <?php echo esc_html__( 'Paths & Options', 'hide-my-wp' ) ?>:
                                </h6>
                                <?php
                                $presetsModel->setCurrentPreset( $index );
                                $presets = $presetsModel->getPresetData();
                                ?>

                                <table class="table table-striped col-6">
                                    <?php foreach ( $presets as $name => $preset ) { ?>
                                        <tr>
                                            <td><?php echo esc_html( $preset['title'] ) ?></td>
                                            <td><strong><?php echo $presetsModel->getPresetValue( $name ); ?></strong></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        <?php } ?>

                        <input type="submit" class="btn rounded-0 btn-default" name="hmwp_preset" onclick="return confirm('By loading a preset, you will lose the previously saved settings. Do you want to continue?');" value="<?php echo esc_attr__( 'Load Preset', 'hide-my-wp' ) ?>"/>
                    </form>
                </div>
            </div>
            <div class="card col-sm-12 p-0 m-0 mt-3">
                <h3 class="card-title hmwp_header p-2 m-0">
                    <?php echo esc_html__( 'Backup/Restore Settings', 'hide-my-wp' ); ?>
                </h3>
                <div class="card-body">
                    <div class="text-black-50 mb-2"><?php echo esc_html__( 'Click Backup and the download will start automatically. You can use the Backup for all your websites.', 'hide-my-wp' ); ?></div>

                    <div class="hmwp_settings_backup">
                        <form action="" method="POST">
							<?php wp_nonce_field( 'hmwp_backup', 'hmwp_nonce' ); ?>
                            <input type="hidden" name="action" value="hmwp_backup"/>
                            <button type="submit" class="btn rounded-0 btn-default noload" name="hmwp_backup"><?php echo esc_html__( 'Backup Settings', 'hide-my-wp' ) ?></button>
                            <button type="button" class="btn rounded-0 btn-light hmwp_restore hmwp_modal" onclick="jQuery('#hmwp_settings_restore').modal('show')" name="hmwp_restore"><?php echo esc_html__( 'Restore Settings', 'hide-my-wp' ) ?></button>
                        </form>
                    </div>

                    <!-- Modal -->
                    <div id="hmwp_settings_restore" class="modal hmwp_settings_restore" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo esc_html__( 'Restore Settings', 'hide-my-wp' ) ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div><?php echo esc_html__( 'Upload the file with the saved plugin settings', 'hide-my-wp' ) ?></div>
                                    <form action="" method="POST" enctype="multipart/form-data">
										<?php wp_nonce_field( 'hmwp_restore', 'hmwp_nonce' ); ?>
                                        <input type="hidden" name="action" value="hmwp_restore"/>
                                        <div class="py-2">
                                            <input type="file" name="hmwp_options" id="favicon"/>
                                        </div>

                                        <input type="submit" style="margin-top: 10px;" class="btn rounded-0 btn-default" name="hmwp_restore" value="<?php echo esc_attr__( 'Restore Backup', 'hide-my-wp' ) ?>"/>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="card col-sm-12 p-0 m-0 mt-3">
                <h3 class="card-title hmwp_header p-2 m-0"><?php echo esc_html__( 'Reset Settings', 'hide-my-wp' ); ?></h3>
                <div class="card-body">
                    <div class="hmwp_settings_rollback">
                        <div class="text-black-50 mb-2"><?php echo esc_html__( 'Rollback all the plugin settings to initial values.', 'hide-my-wp' ); ?></div>
                        <form method="POST">
							<?php wp_nonce_field( 'hmwp_rollback', 'hmwp_nonce' ); ?>
                            <input type="hidden" name="action" value="hmwp_rollback"/>
                            <input type="submit" class="btn rounded-0 btn-default" name="hmwp_rollback" onclick="return confirm('Are you sure you want to reset the settings to their initial values?');" value="<?php echo esc_attr__( 'Reset Settings', 'hide-my-wp' ) ?>"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="hmwp_col hmwp_col_side px-2 py-0 mr-2">
            <div class="card col-sm-12 m-0 p-0 rounded-0">
                <div class="card-body f-gray-dark text-left">
                    <h3 class="panel-title"><?php echo esc_html__( 'Backup Settings', 'hide-my-wp' ); ?></h3>
                    <div class="text-info mt-3"><?php echo sprintf( esc_html__( "It's important to %s save your settings every time you change them %s. You can use the backup to configure other websites you own.", 'hide-my-wp' ), '<strong>', '</strong>' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
