<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".button_ordersku").click(function () {
            $('.aramex_error_message').remove();
            $('.aramex_success_message').remove();
            $('.green_table').remove();
            $('.order_sku_background').css('display', 'block');
            var data_order_sku_name = this.getAttribute("data-order-sku-name");
            $('input[name="SKUs[]"]').val(data_order_sku_name);
        });
        $(".aramex_order_sku_cancel").click(function () {
            $('.order_sku_background').css('display', 'none');
        });
        $("#button_ordersku").click(function () {
            var myform = $('#ordersku-form');
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
                    $('.green_table').remove();
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

    jQuery(document).ready(function ($) {
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper
        var fieldHTML = '<div><input type="text" name="SKUs[]" value=""/> <a href="javascript:void(0);" class="remove_button" title="Remove field">Remove field</a><br /><br /></div>'; //New input field html 
        var x = 1; //Initial field counter is 1
        $(addButton).click(function () { //Once add button is clicked
            if (x < maxField) { //Check maximum number of input fields
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); // Add field html
            }
        });
        $(wrapper).on('click', '.remove_button', function (e) { //Once remove button is clicked
            e.preventDefault();
            $(this).parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });
</script>  
<div class="order_sku_background" style="display:none;">
    <div class="order_sku">
        <div class="aramex_title">
            <b>WMS Order Sku</b>
        </div>
        <form method="post" style="font-size:12px;" id="ordersku-form">
            <form method="post"  id="orderin-form">
                <input type="hidden" name="ordersku_token" value="<?php echo generateFormToken('ordersku'); ?>">
                <div class="aramex_top_fields">
                    <FIELDSET>
                        <div class="aramex_left_form">
                            <div class="field  field_wrapper">
                                <label>SKUs<span class="red"></span></label><br />
                                <input type="text" name="SKUs[]"  value="" />
                                <a href="javascript:void(0);" class="add_button" title="Add field">Add field</a>
                                <br />
                                <br />
                            </div>
                        </div>
                    </FIELDSET>
                </div>
                <button id="button_ordersku" title="OrderSku" type="button" class="scalable " style=""><span><span><span>Submit order</span></span></span></button>
                <button id="button_cancel" title="Cancel" type="button" class="aramex_order_sku_cancel" style=""><span><span><span>Cancel</span></span></span></button>
            </form>
    </div>
</div>
