
<div>
    
    <table class="form-table">
        <tbody>
            <?php echo do_settings_fields('almefy', AlmefyConstants::$SECTION_SETTINGS); ?>
        </tbody>
    </table>
    
</div>


<!-- reinstall -->
<!-- <div class="almefy-3-col-s" style="background: transparent; margin-top: 3rem;">
    <div style="display: flex; align-items: center;">
        <a class="almefy-button almefy-button-red" href="<?php echo esc_attr(admin_url('admin.php?page=almefy-wizard')) ?>"><?php _e("Reinstall", 'almefy-me') ?></a>
    </div>
    <div></div>
    <div class="almefy-info"><?php _e("If you want to restore the default settings just restart the setup and you will be guided through the steps.", 'almefy-me') ?></div>
</div> -->