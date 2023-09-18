<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#checkAll').click(function () {
            $('input:checkbox').prop('checked', this.checked);
        });

        $(".mass_send_so").click(function () {
            $('.aramex_success_message').css('display', 'none');
            var selected = [];
            $('.data-row input:checked').each(function () {
                selected.push((this).getAttribute('data-order-check'));
            });
            if (selected.length > 0) {
                $('#aramex_preloader').css('display', 'block');
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
                    data: {selectedOrders: selected, bulk: "bulk"},
                    success: function ajaxViewsSection(data) {
                        if (typeof data.orders !== 'undefined') {
                            $('.account_so').each(function () {
                                var i;
                                for (i = 0; i < data.orders.length; ++i) {
                                    if (this.getAttribute('data-order-id_order') == data.orders[i]) {
                                        $(this).prev().removeClass("label-success").toggleClass("label-default").html("Processed");
                                    }
                                }
                            });
                        }
                        $('.aramex_error_message').css('display', 'none');
                        $('#aramex_preloader').css('display', 'none');
                        $('.aramex_success_message').css('display', 'none');
                        $('.order_out_background').css('display', 'block');
                        $('.order_out').css('display', 'block');
                        $(".order_out .test").html(data.message);
                    }
                });
            } else {
                alert("Select orders!");
            }
        });
        $(".synchronize").click(function () {
            $('.aramex_success_message').css('display', 'none');
            var selected = [];
            $('.data-row input:checked').each(function () {
                selected.push((this).getAttribute('data-order-check'));
            });
            $('#aramex_preloader').css('display', 'block');
            var url = '<?php echo $protocol; ?>';

            $.ajax({
                url: url + "/catalog.php",
                type: "POST",
                dataType: 'json',
                data: {selectedOrders: selected, synchronize: "synchronize"},
                success: function ajaxViewsSection(data) {
                    console.log(data);
                    if (typeof data.id !== 'undefined') {
                        $('.account_so').each(function () {
                            var i;
                            for (i = 0; i < data.id.length; ++i) {
                                if (this.getAttribute('data-order-id_order') == data.id[i]) {
                                    $(this).prev().html(data.status[i]);
                                    if (typeof data.awb !== 'undefined') {
                                        $(this).prev().parent().next().html(data.awb[i]);
                                    }
                                }
                            }
                        });
                    }
                    $('.aramex_error_message').css('display', 'none');
                    $('#aramex_preloader').css('display', 'none');
                    $('.aramex_success_message').css('display', 'none');
                    $('.order_out_background').css('display', 'block');
                    $('.order_out').css('display', 'block');
                    $(".order_out .test").html(data.message);
                }
            });

        });

        $(".account_so").click(function () {

            var data_order_so_name = this.getAttribute("data-order-so-name");
            var data_order_so_price = this.getAttribute("data-order-so-price");
            var data_order_so_weight = this.getAttribute("data-order-so-weight");

            var data_order_OrderNumber = this.getAttribute("data-order-OrderNumber");
            var data_order_ConsigneeName = this.getAttribute("data-order-ConsigneeName");
            var data_order_ConsigneeCity = this.getAttribute("data-order-ConsigneeCity");
            var data_order_ConsigneeAttention = this.getAttribute("data-order-ConsigneeAttention");
            var data_order_ConsigneeZipCode = this.getAttribute("data-order-ConsigneeZipCode");
            var data_order_ConsigneeCountryCode = this.getAttribute("data-order-ConsigneeCountryCode");
            var data_order_ConsigneeAddress = this.getAttribute("data-order-ConsigneeAddress");
            var data_order_Carrier = this.getAttribute("data-order-Carrier");
            var data_order_ClearanceAgent = this.getAttribute("data-order-ClearanceAgent");
            var data_order_SKU = this.getAttribute("data-order-SKU");
            var data_order_Quantity = this.getAttribute("data-order-Quantity");
            var data_order_Currency = this.getAttribute("data-order-Currency");
            var data_order_UnitInvoicePrice = this.getAttribute("data-order-UnitInvoicePrice");
            var data_order_TotalInvoicePrice = this.getAttribute("data-order-TotalInvoicePrice");
            var data_order_Comments = this.getAttribute("data-order-Comments");
            var data_order_IdOrder = this.getAttribute("data-order-id_order");
            var data_order_Phone = this.getAttribute("data-order-phone");
            var data_order_Company = this.getAttribute("data-order-company");

            $('input[name="SKU"]').val(data_order_so_name);
            $('input[name="Weight"]').val(data_order_so_weight);
            $('input[name="SellingPrice"]').val(data_order_so_price);
            $('input[name="OrderNumber"]').val(data_order_OrderNumber);
            $('input[name="ConsigneeName"]').val(data_order_ConsigneeName);
            $('input[name="ConsigneeCity"]').val(data_order_ConsigneeCity);
            $('input[name="ConsigneeAttention"]').val(data_order_ConsigneeAttention);
            $('input[name="ConsigneeZipCode"]').val(data_order_ConsigneeZipCode);
            $('input[name="ConsigneeCountryCode"]').val(data_order_ConsigneeCountryCode);
            $('input[name="ConsigneeAddress"]').val(data_order_ConsigneeAddress);
            $('input[name="Carrier"]').val(data_order_Carrier);
            $('input[name="ClearanceAgent"]').val(data_order_ClearanceAgent);
            $('input[name="SKU"]').val(data_order_SKU);
            $('input[name="Quantity"]').val(data_order_Quantity);
            $('input[name="Currency"]').val(data_order_Currency);
            $('input[name="UnitInvoicePrice"]').val(data_order_UnitInvoicePrice);
            $('input[name="TotalInvoicePrice"]').val(data_order_TotalInvoicePrice);
            $('input[name="Comments"]').val(data_order_Comments);
            $('input[name="Id_order"]').val(data_order_IdOrder);
            $('input[name="Phone"]').val(data_order_Phone);
            $('input[name="ConsigneeCompany"]').val(data_order_Company);


            $('.aramex_error_message').remove();
            $('.aramex_success_message').remove();
            $('.green_table').remove();
            $('.order_so_background').css('display', 'block');
        });
        $(".aramex_order_so_cancel").click(function () {
            $('.order_so_background').css('display', 'none');
        });
        $("#button_so").click(function (data_order_IdOrder) {
            var myform = $('#so');
            // serialize the form
            var serialized = myform.serializeArray();
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
                       $('.account_so').each(function () {
                            if (this.getAttribute('data-order-id_order') == data.data_order_IdOrder) {
                                $(this).prev().removeClass("label-success").toggleClass("label-default").html("Blocked");
                                $(this).prev().attr("data-original-title", data.error );
                                $(this).prev().attr("data-toggle", "tooltip");
                            }
                        });
                        
                        $('.aramex_title').after('<p class=\'aramex_error_message\'>' + data.error + '</p>');
                    } else {

                        $('.account_so').each(function () {
                            if (this.getAttribute('data-order-id_order') == data.data_order_IdOrder) {
                                $(this).prev().removeClass("label-success").toggleClass("label-default").html("Processed");
                            }
                        });
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

<div class="order_so_background" style="display:none;">
    <div class="order_out order_so">
        <div class="aramex_title">
            <b>Create Order</b>
        </div>
        <form method="post"  id="so">
            <?php

            function generateFormToken($form) {
                // generate a token from an unique value
                $token = md5(uniqid(microtime(), true));
                // Write the generated token to the session variable to check it against the hidden field when the form is sent
                $_SESSION[$form . '_token'] = $token;
                return $token;
            }
            ?>
            <input type="hidden" name="so_token" value="<?php echo generateFormToken('so'); ?>">
            <input type="hidden" name="SKU"  value="" />
            <input type="hidden" name="Weight"  value="" />
            <input type="hidden" name="SellingPrice"  value="" />
            <input type="hidden" name="Id_order"  value="" />


            <FIELDSET>
                <div class="aramex_left_form_in aramex_left_form_so">
                    <div class="field">
                        <label>Order Number<span class="red"></span></label><br />
                        <input type="text" name="OrderNumber"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee Name<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeName"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee City<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeCity"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee Attention<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeAttention"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee ZipCode<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeZipCode"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee CountryCode<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeCountryCode"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee Phone<span class="red"></span></label><br />
                        <input type="text" name="Phone"  value="" />
                    </div>
                    <div class="field">
                        <label>Consignee Address<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeAddress"  value="" />
                    </div>
                    <div class="field">
                        <label>Carrier<span class="red"></span></label><br />
                        <input type="text" name="Carrier"  value="Aramex" />
                    </div>
                </div>
                <div class="aramex_left_form_in aramex_right_form_so">
                    <div class="field">
                        <label>Consignee Company<span class="red"></span></label><br />
                        <input type="text" name="ConsigneeCompany"  value="" />
                    </div>
                    <div class="field">
                        <label>Clearance Agent<span class="red"></span></label><br />
                        <input type="text" name="ClearanceAgent"  value="" />
                    </div>
                    <div class="field">
                        <label>SKU<span class="red"></span></label><br />
                        <input type="text" name="SKU"  value="" />
                    </div>
                    <div class="field">
                        <label>Quantity<span class="red"></span></label><br />
                        <input type="text" name="Quantity"  value="" />
                    </div>
                    <div class="field">
                        <label>Currency<span class="red"></span></label><br />
                        <input type="text" name="Currency"  value="" />
                    </div>
                    <div class="field">
                        <label>Unit Invoice Price<span class="red"></span></label><br />
                        <input type="text" name="UnitInvoicePrice"  value="" />
                    </div>
                    <div class="field">
                        <label>Total Invoice Price<span class="red"></span></label><br />
                        <input type="text" name="TotalInvoicePrice"  value="" />
                    </div>
                    <div class="field">
                        <label>Comments<span class="red"></span></label><br />
                        <textarea name="Comments" ></textarea>
                    </div>
                </div>
            </FIELDSET>
            <div class="field">
                <button id="button_so" title="Save Order" type="button" class="scalable btn btn-primary " style=""><span><span><span>Create order</span></span></span></button>
                <button id="button_cancel" title="Cancel" type="button" class="aramex_order_so_cancel btn btn-primary " style="margin-top:0px;"><span>Close</span></button>
            </div>
        </form>
    </div>
</div>
