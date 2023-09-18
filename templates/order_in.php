<div id="aramex_preloader" style="display:none;" ></div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".button_orderin").click(function () {
            $('.order_in_background').css('display', 'block');
            var data_order_in_name = this.getAttribute("data-order-in-name");
            var data_order_in_price = this.getAttribute("data-order-in-price");
            var data_order_in_weight = this.getAttribute("data-order-in-weight");

            $('input[name="SKU"]').val(data_order_in_name);
            $('input[name="Weight"]').val(data_order_in_weight);
            $('input[name="SellingPrice"]').val(data_order_in_price);
        });
        $(".aramex_order_in_cancel").click(function () {
            $('.order_in_background').css('display', 'none');
        });
       
        $("#button_orderin").click(function () {
            $('.aramex_error_message').remove();
            $('.aramex_success_message').remove();
            $('.green_table').remove();
            var myform = $('#orderin-form');
            // Find disabled inputs, and remove the "disabled" attribute
            var disabled = myform.find(':input:disabled').removeAttr('disabled');
            // serialize the form
            var serialized = myform.serializeArray();
            // re-disabled the set of inputs that you previously enabled
            disabled.attr('disabled', 'disabled');
            serialized.push({name: 'form_key', value: window.FORM_KEY});
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

<div class="order_in_background" style="display:none;">
    <div class="order_in">
        <div class="aramex_title ">
            <b>WMS Order In</b>
        </div>
        <form method="post"  id="orderin-form">

            <input type="hidden" name="orderin_token" value="<?php echo generateFormToken('orderin'); ?>">
            <div class="aramex_top_fields">
                <FIELDSET>
                    <div class="aramex_left_form_in">
                        <div class="field">
                            <label>GLReference<span class="red"></span></label><br />
                            <input type="text" name="GLReference"  value="" />
                        </div>

                        <div class="field">
                            <label>Handling Type<span class="red"></span></label><br />
                            <input type="text" name="HandlingType"  value="" />
                        </div>
                        <div class="field">
                            <label>Invoice Number<span class="red"></span></label><br />
                            <input type="text" name="InvoiceNumber"  value="" />
                        </div>
                        <div class="field">
                            <label>Supplier<span class="red"></span></label><br />
                            <input type="text" name="Supplier"  value="" />
                        </div>
                        <div class="field">
                            <label>SupplierAttention<span class="red"></span></label><br />
                            <input type="text" name="SupplierAttention"  value="" />
                        </div>
                    </div>
                    <div class="aramex_left_form_in">
                        <div class="field">
                            <label>Supplier City<span class="red"></span></label><br />
                            <input type="text" name="SupplierCity"  value="" />
                        </div>

                        <div class="field">
                            <label>Supplier Country<span class="red"></span></label><br />
                            <input type="text" name="SupplierCountry"  value="" />
                        </div>

                        <div class="field">
                            <label>SupplierRef<span class="red"></span></label><br />
                            <input type="text" name="SupplierRef"  value="" />
                        </div>
                        <div class="field">
                            <label>Supplier State<span class="red"></span></label><br />
                            <input type="text" name="SupplierState"  value="" />
                        </div>
                        <div class="field">
                            <label>Supplier ZIP Code<span class="red"></span></label><br />
                            <input type="text" name="SupplierZIPCode"  value="" />
                        </div>
                    </div>

                </FIELDSET>
            </div>
            <input type="hidden" name="SKU"  value="" />
            <input type="hidden" name="Weight"  value="" />
            <input type="hidden" name="SellingPrice"  value="" />
            <button id="button_orderin" title="OrderIn" type="button" class="scalable "  style=""><span><span><span>Submit order</span></span></span></button>
            <button id="button_cancel" title="Cancel" type="button" class="aramex_order_in_cancel" style=""><span><span><span>Cancel</span></span></span></button>
        </form>
    </div>

</div>
