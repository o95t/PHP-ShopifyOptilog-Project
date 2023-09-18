<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".button_orderout").click(function () {
            $('.aramex_error_message').remove();
            $('.aramex_success_message').remove();
            $('.green_table').remove();
            $('.order_out_background').css('display', 'block');
            var data_order_out_name = this.getAttribute("data-order-out-name");
            var data_order_out_weight = this.getAttribute("data-order-out-weight");
            $('input[name="SKU"]').val(data_order_out_name);
            $('input[name="Name"]').val(data_order_out_name);
            $('input[name="Weight"]').val(data_order_out_weight);
        });
        $(".aramex_order_out_cancel").click(function () {
            $('.order_out_background').css('display', 'none');
        });
        $("#button_orderout").click(function () {

            var myform = $('#orderin-out');
            // Find disabled inputs, and remove the "disabled" attribute
            var disabled = myform.find(':input:disabled').removeAttr('disabled');
            // serialize the form
            var serialized = myform.serializeArray();
            // re-disabled the set of inputs that you previously enabled
            disabled.attr('disabled', 'disabled');
                    <?php  
            if(isset($_SERVER['HTTPS'])){
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            }
            else{
                $protocol = 'http';
            }
            $protocol = $protocol . "://". $_SERVER['HTTP_HOST']; 
         ?>
            var url = '<?php echo $protocol; ?>';
            $.ajax({
                url: url + "/catalog.php",
                type: "POST",
                dataType: 'json',
                data: serialized,
                beforeSend: function () {
                    $('#aramex_preloader').css('display', 'block');
                },
                success: function (data) {
                    $('#aramex_preloader').css('display', 'none');
                    $('.aramex_error_message').remove();
                    $('.aramex_success_message').remove();
                    if (data.error) {
                        $('.aramex_title').after('<p class=\'aramex_error_message\'>' + data.error + '</p>');
                    } else {
                        $('.aramex_title').after('<p class=\'aramex_success_message\'>' + data.success + '</p>');
                    }
                },
                error: function (response) {
                    $('#aramex_preloader').css('display', 'none');
                    $('.aramex_succes_message').remove();
                },
            });
        });
    });
</script>  

<div class="order_out_background" style="display:none;">
    <div class="order_out">
        <div class="aramex_title">
            <b>Result</b>
        </div>

        <form method="post"  id="orderin-out">
            <div class="test">
            <input type="hidden" name="orderout_token" value="<?php echo generateFormToken('orderout'); ?>">
            <input type="hidden" name="SKU"  value="" />
            <input type="hidden" name="Weight"  value="" />
            <input type="hidden" name="OriginCountry"  value="<?php echo (isset($originCountry))?$originCountry:''; ?>" />
            <input type="hidden" name="Name"  value="" />
        </div>
            <!--<button id="button_orderout" title="OrderIn" type="button" class="scalable " style=""><span><span><span>Submit order</span></span></span></button>-->
            <button id="button_cancel" title="Cancel" type="button" class="aramex_order_out_cancel" style=""><span><span><span>Close</span></span></span></button>
        </form>

    </div>

</div>
