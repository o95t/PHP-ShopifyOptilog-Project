<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".account_settings").click(function () {
            $('.aramex_error_message').remove();
            $('.aramex_success_message').remove();
            $('.green_table').remove();
            $('.order_settings_background').css('display', 'block');
        });
        $(".aramex_order_out_cancel").click(function () {
            $('.order_settings_background').css('display', 'none');
        });
        $("#button_settings").click(function () {
            var myform = $('#settings');
            // serialize the form
            var serialized = myform.serializeArray();
            <?php
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $protocol = $protocol . "://" . $_SERVER['HTTP_HOST'];
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
                    if (data !== "Saved") {
                        $('.aramex_title').after('<p class=\'aramex_error_message\'>' + data + '</p>');
                    } else {
                        $('.aramex_title').after('<p class=\'aramex_success_message\'>' + data + '</p>');
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
<div class="order_settings_background" style="display:none;">
    <div class="order_out order_out_settings">

        <div class="aramex_title">
            <b>Account settings</b>
        </div>
        <div style="clear:both"></div>

        <form method="post" id="settings">
            <div class="order_out_left">
                <input type="hidden" name="settings_token" value="<?php echo generateFormToken('settings'); ?>">
                <div class="field">
                    <label>Account Entity<span class="red"></span></label><br/>
                    <input class="form-control" name="AccountEntity"
                           value="<?php echo ($settings['AccountEntity']) ? $settings['AccountEntity'] : ""; ?>"/>
                </div>
                <div class="field">
                    <label>Account Number<span class="red"></span></label><br/>
                    <input class="form-control" name="AccountNumber"
                           value="<?php echo ($settings['AccountNumber']) ? $settings['AccountNumber'] : ""; ?>"/>
                </div>
                <div class="field">
                    <label>Account Pin<span class="red"></span></label><br/>
                    <input class="form-control" name="AccountPin"
                           value="<?php echo ($settings['AccountPin']) ? $settings['AccountPin'] : ""; ?>"/>
                </div>
                <div class="field">
                    <label>Site Code<span class="red"></span></label><br/>
                    <input class="form-control" name="SiteCode"
                           value="<?php echo ($settings['SiteCode']) ? $settings['SiteCode'] : ""; ?>"/>
                </div>

                <div class="field">
                    <label>Shopify App Key<span class="red"></span></label><br/>
                    <input class="form-control" name="Key"
                           value="<?php echo ($settings['Key1']) ? $settings['Key1'] : ""; ?>"/>
                </div>
                <div class="field">
                    <label>Shopify App
                        Password<span class="red"></span></label><br/>
                    <input class="form-control" name="Password"
                           value="<?php echo ($settings['Password1']) ? $settings['Password1'] : ""; ?>"/>
                </div>
                <div class="field">
                    <label>Time Zone<span class="red"></span></label><br/>
                    <select name="TimeZone" class="form-control">
                        <?php
                        $timezones = array(
                            'Pacific/Midway' => "(GMT-11:00) Midway Island",
                            'US/Samoa' => "(GMT-11:00) Samoa",
                            'US/Hawaii' => "(GMT-10:00) Hawaii",
                            'US/Alaska' => "(GMT-09:00) Alaska",
                            'US/Pacific' => "(GMT-08:00) Pacific Time (US &amp; Canada)",
                            'America/Tijuana' => "(GMT-08:00) Tijuana",
                            'US/Arizona' => "(GMT-07:00) Arizona",
                            'US/Mountain' => "(GMT-07:00) Mountain Time (US &amp; Canada)",
                            'America/Chihuahua' => "(GMT-07:00) Chihuahua",
                            'America/Mazatlan' => "(GMT-07:00) Mazatlan",
                            'America/Mexico_City' => "(GMT-06:00) Mexico City",
                            'America/Monterrey' => "(GMT-06:00) Monterrey",
                            'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
                            'US/Central' => "(GMT-06:00) Central Time (US &amp; Canada)",
                            'US/Eastern' => "(GMT-05:00) Eastern Time (US &amp; Canada)",
                            'US/East-Indiana' => "(GMT-05:00) Indiana (East)",
                            'America/Bogota' => "(GMT-05:00) Bogota",
                            'America/Lima' => "(GMT-05:00) Lima",
                            'America/Caracas' => "(GMT-04:30) Caracas",
                            'Canada/Atlantic' => "(GMT-04:00) Atlantic Time (Canada)",
                            'America/La_Paz' => "(GMT-04:00) La Paz",
                            'America/Santiago' => "(GMT-04:00) Santiago",
                            'Canada/Newfoundland' => "(GMT-03:30) Newfoundland",
                            'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
                            'Greenland' => "(GMT-03:00) Greenland",
                            'Atlantic/Stanley' => "(GMT-02:00) Stanley",
                            'Atlantic/Azores' => "(GMT-01:00) Azores",
                            'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
                            'Africa/Casablanca' => "(GMT) Casablanca",
                            'Europe/Dublin' => "(GMT) Dublin",
                            'Europe/Lisbon' => "(GMT) Lisbon",
                            'Europe/London' => "(GMT) London",
                            'Africa/Monrovia' => "(GMT) Monrovia",
                            'Europe/Amsterdam' => "(GMT+01:00) Amsterdam",
                            'Europe/Belgrade' => "(GMT+01:00) Belgrade",
                            'Europe/Berlin' => "(GMT+01:00) Berlin",
                            'Europe/Bratislava' => "(GMT+01:00) Bratislava",
                            'Europe/Brussels' => "(GMT+01:00) Brussels",
                            'Europe/Budapest' => "(GMT+01:00) Budapest",
                            'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
                            'Europe/Ljubljana' => "(GMT+01:00) Ljubljana",
                            'Europe/Madrid' => "(GMT+01:00) Madrid",
                            'Europe/Paris' => "(GMT+01:00) Paris",
                            'Europe/Prague' => "(GMT+01:00) Prague",
                            'Europe/Rome' => "(GMT+01:00) Rome",
                            'Europe/Sarajevo' => "(GMT+01:00) Sarajevo",
                            'Europe/Skopje' => "(GMT+01:00) Skopje",
                            'Europe/Stockholm' => "(GMT+01:00) Stockholm",
                            'Europe/Vienna' => "(GMT+01:00) Vienna",
                            'Europe/Warsaw' => "(GMT+01:00) Warsaw",
                            'Europe/Zagreb' => "(GMT+01:00) Zagreb",
                            'Europe/Athens' => "(GMT+02:00) Athens",
                            'Europe/Bucharest' => "(GMT+02:00) Bucharest",
                            'Africa/Cairo' => "(GMT+02:00) Cairo",
                            'Africa/Harare' => "(GMT+02:00) Harare",
                            'Europe/Helsinki' => "(GMT+02:00) Helsinki",
                            'Europe/Istanbul' => "(GMT+02:00) Istanbul",
                            'Asia/Jerusalem' => "(GMT+02:00) Jerusalem",
                            'Europe/Kiev' => "(GMT+02:00) Kyiv",
                            'Europe/Minsk' => "(GMT+02:00) Minsk",
                            'Europe/Riga' => "(GMT+02:00) Riga",
                            'Europe/Sofia' => "(GMT+02:00) Sofia",
                            'Europe/Tallinn' => "(GMT+02:00) Tallinn",
                            'Europe/Vilnius' => "(GMT+02:00) Vilnius",
                            'Asia/Baghdad' => "(GMT+03:00) Baghdad",
                            'Asia/Kuwait' => "(GMT+03:00) Kuwait",
                            'Africa/Nairobi' => "(GMT+03:00) Nairobi",
                            'Asia/Riyadh' => "(GMT+03:00) Riyadh",
                            'Europe/Moscow' => "(GMT+03:00) Moscow",
                            'Asia/Tehran' => "(GMT+03:30) Tehran",
                            'Asia/Baku' => "(GMT+04:00) Baku",
                            'Europe/Volgograd' => "(GMT+04:00) Volgograd",
                            'Asia/Muscat' => "(GMT+04:00) Muscat",
                            'Asia/Tbilisi' => "(GMT+04:00) Tbilisi",
                            'Asia/Yerevan' => "(GMT+04:00) Yerevan",
                            'Asia/Kabul' => "(GMT+04:30) Kabul",
                            'Asia/Karachi' => "(GMT+05:00) Karachi",
                            'Asia/Tashkent' => "(GMT+05:00) Tashkent",
                            'Asia/Kolkata' => "(GMT+05:30) Kolkata",
                            'Asia/Kathmandu' => "(GMT+05:45) Kathmandu",
                            'Asia/Yekaterinburg' => "(GMT+06:00) Ekaterinburg",
                            'Asia/Almaty' => "(GMT+06:00) Almaty",
                            'Asia/Dhaka' => "(GMT+06:00) Dhaka",
                            'Asia/Novosibirsk' => "(GMT+07:00) Novosibirsk",
                            'Asia/Bangkok' => "(GMT+07:00) Bangkok",
                            'Asia/Jakarta' => "(GMT+07:00) Jakarta",
                            'Asia/Krasnoyarsk' => "(GMT+08:00) Krasnoyarsk",
                            'Asia/Chongqing' => "(GMT+08:00) Chongqing",
                            'Asia/Hong_Kong' => "(GMT+08:00) Hong Kong",
                            'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
                            'Australia/Perth' => "(GMT+08:00) Perth",
                            'Asia/Singapore' => "(GMT+08:00) Singapore",
                            'Asia/Taipei' => "(GMT+08:00) Taipei",
                            'Asia/Ulaanbaatar' => "(GMT+08:00) Ulaan Bataar",
                            'Asia/Urumqi' => "(GMT+08:00) Urumqi",
                            'Asia/Irkutsk' => "(GMT+09:00) Irkutsk",
                            'Asia/Seoul' => "(GMT+09:00) Seoul",
                            'Asia/Tokyo' => "(GMT+09:00) Tokyo",
                            'Australia/Adelaide' => "(GMT+09:30) Adelaide",
                            'Australia/Darwin' => "(GMT+09:30) Darwin",
                            'Asia/Yakutsk' => "(GMT+10:00) Yakutsk",
                            'Australia/Brisbane' => "(GMT+10:00) Brisbane",
                            'Australia/Canberra' => "(GMT+10:00) Canberra",
                            'Pacific/Guam' => "(GMT+10:00) Guam",
                            'Australia/Hobart' => "(GMT+10:00) Hobart",
                            'Australia/Melbourne' => "(GMT+10:00) Melbourne",
                            'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
                            'Australia/Sydney' => "(GMT+10:00) Sydney",
                            'Asia/Vladivostok' => "(GMT+11:00) Vladivostok",
                            'Asia/Magadan' => "(GMT+12:00) Magadan",
                            'Pacific/Auckland' => "(GMT+12:00) Auckland",
                            'Pacific/Fiji' => "(GMT+12:00) Fiji",
                        );
                        foreach ($timezones as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>"
                                <?php
                                if ($key === $settings['TimeZone']) {
                                    echo ' selected="selected"';
                                }
                                ?>
                            ><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field">
                    <label>Auto Mode<span class="red"></span></label><br/>
                    <input name="Auto" type="checkbox" <?php if ($settings['Auto'] == "on") echo "checked"; ?>>
                </div>
                <div class="field">
                    <label>Time interval for "Auto mode"<span class="red"></span></label><br/>
                    <select name="Interval" class="form-control">
                        <?php
                        $intervals = array(
                            '1' => "5 minutes",
                            '2' => "15 minutes",
                            '3' => "30 minutes",
                            '4' => "1 hour",
                            '5' => "2 hours",
                            '6' => "4 hours",
                        );
                        foreach ($intervals as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>"
                                <?php
                                if ($key == $settings['Intervall']) {
                                    echo ' selected="selected"';
                                }
                                ?>
                            ><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field">
                    <label>Test Mode<span class="red"></span></label><br/>
                    <input name="TestMode" type="checkbox" <?php if ($settings['TestMode'] == "on") echo "checked"; ?>>
                </div>
                <div class="field">
                    <label>2 hours delay<span class="red"></span></label><br/>
                    <input name="Delay" type="checkbox" <?php if ($settings['Delay'] == "on") echo "checked"; ?>>
                </div>
                <div class="field">
                    <label>Stock level update<span class="red"></span></label><br/>
                    <input name="Stock" type="checkbox" <?php if ($settings['Stock'] == "on") echo "checked"; ?>>
                </div>
            </div>
            <div class="order_out_right">
                <label>Shipping services<span class="red"></span></label><br/>
                <?php
                $services = unserialize($settings['Services']);

                $i = 0;
                $service = array();
                $price = array();
                if ($services !== false) {
                    foreach ($services as $key => $value) {
                        $service[$i] = $key;
                        $price[$i] = $value;
                        $i = $i + 1;
                    }
                }
                ?>
                <div class="field">
                    <p>1.</p>
                    <p><input placeholder="Service" class="form-control" name="Service1"
                              value="<?php echo (isset($service[0])) ? $service[0] : ""; ?>"/></p>
                    <p><input placeholder="Price" class="form-control" name="Price1"
                              value="<?php echo (isset($price[0])) ? $price[0] : ""; ?>"/></p>
                </div>
                <div class="field">
                    <p>2.</p>
                    <p><input placeholder="Service" class="form-control" name="Service2"
                              value="<?php echo (isset($service[1])) ? $service[1] : ""; ?>"/></p>
                    <p><input placeholder="Price" class="form-control" name="Price2"
                              value="<?php echo (isset($price[1])) ? $price[1] : ""; ?>"/></p>
                </div>
                <div class="field">
                    <p>3.</p>
                    <p><input placeholder="Service" class="form-control" name="Service3"
                              value="<?php echo (isset($service[2])) ? $service[2] : ""; ?>"/></p>
                    <p><input placeholder="Price" class="form-control" name="Price3"
                              value="<?php echo (isset($price[2])) ? $price[2] : ""; ?>"/></p>
                </div>
                <div class="field">
                    <p>4.</p>
                    <p><input placeholder="Service" class="form-control" name="Service4"
                              value="<?php echo (isset($service[3])) ? $service[3] : ""; ?>"/></p>
                    <p><input placeholder="Price" class="form-control" name="Price4"
                              value="<?php echo (isset($price[3])) ? $price[3] : ""; ?>"/></p>
                </div>
                <div class="field">
                    <p>5.</p>
                    <p><input placeholder="Service" class="form-control" name="Service5"
                              value="<?php echo (isset($service[4])) ? $service[4] : ""; ?>"/></p>
                    <p><input placeholder="Price" class="form-control" name="Price5"
                              value="<?php echo (isset($price[4])) ? $price[4] : ""; ?>"/></p>
                </div>
                
            </div>
            <div style="clear:both"></div>
            <div class="field">
                <button id="button_settings" title="OrderIn" type="button" class="scalable btn btn-primary " style="">
                    <span><span><span>Save</span></span></span></button>
                <button id="button_cancel" title="Cancel" type="button" class="aramex_order_out_cancel btn btn-primary "
                        style="margin-top:0px;"><span>Close</span></button>
            </div>
    </div>

    </form>

</div>
