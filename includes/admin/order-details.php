<p class="form-field form-field-wide">
    <button id="check_payment_status" type="button" class="button button-primary" data-action="check_payment_status" data-order_id="<?php echo $order->get_id(); ?>">
        <?php echo WC_PayerMax::trans('check_status'); ?>
    </button>
</p>
<script>
    jQuery(document).ready(function() {

        jQuery('#check_payment_status').on('click', function(e) {
            e.preventDefault();
            const target = e.target;
            const dataset = target.dataset;

            var request = jQuery.ajax({
                url: ajaxurl,
                method: "POST",
                data: dataset,
                dataType: "json",
                beforeSend: (xhr) => {
                    target.classList.add('button-disabled');
                }
            });

            request.done((response) => {
                target.classList.remove('button-disabled');
                window.location.reload();
            });

            request.fail((xhr, textStatus) => {
                target.classList.remove('button-disabled');
            });
        });
    });
</script>
