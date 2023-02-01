<style>
.woocommerce_payermax_icon_wrapper .flex {
    display: flex;
    align-items: center;
    gap: 10px;
}

.woocommerce_payermax_icon img {
    max-height: 75px;
    border: 1px solid #ccc;
    background: #fff;
}
</style>

<?php
echo '<h2>' . esc_html($this->get_method_title());
wc_back_link(__('Return to payments', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=checkout'));
echo '</h2>';
echo wp_kses_post(wpautop($this->get_method_description()));
?>

<table class="form-table">

    <!-- upload payermax icon -->
    <tr valign="top" class="woocommerce_payermax_icon_wrapper">
        <th scope="row" class="titledesc">
            <label>
                <?php echo WC_PayerMax::trans('icon'); ?>
            </label>
        </th>
        <td class="forminp">
            <div class="flex">
<?php
$name         = $this->id;
$image_id     = get_option($name, 0);
$upload_title = WC_PayerMax::trans('upload_icon');
$remove_title = WC_PayerMax::trans('remove');

if ($image = wp_get_attachment_image_url($image_id, 'full')):
?>
                <a href="#" class="woocommerce_payermax_icon" data-title="<?php echo $upload_title; ?>">
                    <img src="<?php echo esc_url($image) ?>" />
                </a>
                <a href="#" class="remove_woocommerce_payermax_icon">
                    <?php echo $remove_title; ?>
                </a>
                <input type="hidden" name="<?php echo $name; ?>" value="<?php echo absint($image_id) ?>">
<?php else: ?>
                    <a href="#" class="button woocommerce_payermax_icon" data-title="<?php echo $upload_title; ?>">
                        <?php echo $upload_title; ?>
                    </a>
                    <a href="#" class="remove_woocommerce_payermax_icon" style="display:none"><?php echo $remove_title; ?></a>
                    <input type="hidden" name="<?php echo $name; ?>" value="" />
<?php endif;?>
            </div>

        </td>
    </tr>

<?php echo $this->generate_settings_html($this->get_form_fields(), false); ?>

</table>


<script>
jQuery(function($) {
    var wooIcon = jQuery('.woocommerce_payermax_icon');

    wooIcon.on('click', function(event) {
        event.preventDefault();

        const button = $(this);
        const imageId = button.next().next().val();

        const customUploader = wp.media({
            title: button.data('title'),
            library: {
                type: 'image'
            },
            multiple: false
        });

        customUploader.on('select', function() {
            const attachment = customUploader.state().get('selection').first().toJSON();

            // if (attachment.width > 512 || attachment.height > 256) {
            //     alert('Image too large for ICON');
            //     return;
            // }

            // add image instead of "Upload Image"
            button.removeClass('button').html('<img src="' + attachment.url + '">');
            // show "Remove image" link
            button.next().show();
            // Populate the hidden field with image ID
            button.next().next().val(attachment.id);
        });

        // already selected images
        customUploader.on('open', function() {
            if (imageId) {
                const selection = customUploader.state().get('selection')
                attachment = wp.media.attachment(imageId);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            }
        });

        customUploader.open();

    });
    // on remove button click
    $('body').on('click', '.remove_woocommerce_payermax_icon', function(event) {
        event.preventDefault();
        const button = $(this);
        // emptying the hidden field
        button.next().val('');
        // replace the image with text
        button.hide().prev().addClass('button').html(wooIcon.data('title'));
    });
});
</script>
