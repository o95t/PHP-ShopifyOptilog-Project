<!DOCTYPE html>
<html>
<head>
    <title>Catalog</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="https://code.jquery.com/jquery-migrate-1.2.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <script type="text/javascript" src="js/custom.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
</head>
<body><br/>
<script>
    jQuery(document).ready(function ($) {
        $("#datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0",
            dateFormat: 'yy-mm-dd'
        });
        $("#datepicker1").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0",
            dateFormat: 'yy-mm-dd'
        });
        
    });
    $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
<div class="container">
    <div class="row">
        <div class="dropdown col-sm-12">
            <div class="dropdown pull-left">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="true">
                    Status
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=fresh"; ?>">Fresh</a></li>
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=processed"; ?>">Processed</a></li>
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=new"; ?>">New</a></li>
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=allocated"; ?>">Allocated</a></li>                      
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=part_allocated"; ?>">Part Allocated</a></li> 
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=picked"; ?>">Picked</a></li> 
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=part_picked"; ?>">Part picked</a></li> 
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=cancelled"; ?>">Cancelled</a></li>                    
                    <li><a href="<?php echo $_SERVER['PHP_SELF'] . "?status=issued"; ?>">Issued</a></li> 
                    <li role="separator" class="divider"></li>
                    <li><a href="/catalog.php">All</a></li>
                </ul>
            </div>
            <form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get' name='form_search'>
                <div class="input-group pull-left" style="width:300px; margin-left:20px;">
                            <span class="input-group-btn ">
                                <button class="btn btn-default" type="submit">Go!</button>
                            </span>
                    <input name="search" type="text" class="form-control" placeholder="Search for order">
                </div>
            </form>
            <form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get' name='form_filter'>
                <div class="dropdown pull-right" style="padding-bottom: 30px;">
                    <input type="text" name="time1" id="datepicker" class="form-control "
                           style="width:130px; float:left;" placeholder="Date from" aria-describedby="basic-addon1">
                    <input type="text" name="time2" id="datepicker1" class="form-control"
                           style="width:130px; margin-left:20px; margin-right: 20px; float:left;" placeholder="Date to"
                           aria-describedby="basic-addon1">
                    <button class="btn btn-default" type="submit">Go!</button>
                </div>
            </form>
        </div>
        <div class="col-sm-12 ">
            <div class="dropdown pull-right">
                <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Settings
                    <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a class="account_settings" style="cursor:pointer;">Account settings</a></li>
                    <li><a href="/catalog.php?logout">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="panel panel-default">
            <!-- Default panel contents -->
            <div class="panel-heading" style="overflow:hidden;">Shopify Orders
                <button type="button" class="btn  btn-primary pull-right mass_send_so">Bulk Create Order</button>
                <button type="button" class="btn  btn-primary pull-right synchronize" style="margin-right:20px;">
                    Synchronize
                </button>
            </div>
            <div style="clear:both;"></div>
            <!-- Table -->
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><input id="checkAll" type="checkbox"/></th>
                    <!--<th>#</th>-->
                    <th>Order Number</th>
                    <th>Status</th>
                    <th> #AWB</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($savedOrders[0] as $order) {
                    ?>
                    <tr class="data-row">
                        <td><input data-order-check="<?php echo $order['IdOrder']; ?>" type="checkbox"/></td>
                        <!-- <th scope="row"><?php echo $order['Id']; ?></th>-->
                        <td><?php echo $order['OrderNumber']; ?></td>
                        <td>
                            <?php if ($order['Status'] == "Fresh") { ?>
                                <span data-toggle="tooltip" title ="" class="label status label-success">Fresh</span>
                            <?php } elseif ($order['Status'] == "Processed") {
                                ?>
                                <span class="label status label-default">Processed</span>
                            <?php } elseif ($order['Status'] == "New") {
                                ?>
                                <span class="label status label-default">New</span>
                            <?php } elseif ($order['Status'] == "Part Allocated") {
                                ?>
                                <span class="label status label-default">Part Allocated</span>
                            <?php } elseif ($order['Status'] == "Allocated") {
                                ?>
                                <span class="label status label-default">Allocated</span>
                            <?php } elseif ($order['Status'] == "Staged") {
                                ?>
                                <span class="label status label-default">Staged</span>
                            <?php } elseif ($order['Status'] == "Part Picked") {
                                ?>
                                <span class="label status label-default">Part Picked</span>
                            <?php } elseif ($order['Status'] == "Picked") {
                                ?>
                                <span class="label status label-default">Picked</span>
                            <?php } elseif ($order['Status'] == "Part Loaded") {
                                ?>
                                <span class="label status label-default">Part Loaded</span>
                            <?php } elseif ($order['Status'] == "Loaded") {
                                ?>
                                <span class="label status label-default">Loaded</span>
                            <?php } elseif ($order['Status'] == "Part Shipped") {
                                ?>
                                <span class="label status label-default">Part Shipped</span>
                            <?php } elseif ($order['Status'] == "Shipped") {
                                ?>
                                 <span class="label status label-default">Shipped</span>
                            <?php } elseif ($order['Status'] == "Issued") {
                                ?>
                                <span class="label status label-default">Issued</span>
                            <?php } elseif ($order['Status'] == "Cancelled") {
                                ?>
                                <span class="label status label-default">Cancelled</span>
                            <?php }elseif ($order['Status'] == "Blocked") {
                                ?>
                                <span data-toggle="tooltip" title="<?php echo $order['Message']; ?>!" class="label status label-default">Blocked</span>    
                            <?php } ?>
                            <button
                                data-order-id_order="<?php echo $order['IdOrder']; ?>"
                                data-order-OrderNumber="<?php echo $order['OrderNumber']; ?>"
                                data-order-ConsigneeName="<?php echo $order['ConsigneeName']; ?>"
                                data-order-ConsigneeCity="<?php echo $order['ConsigneeCity']; ?>"
                                data-order-ConsigneeAttention="<?php echo $order['ConsigneeAttention']; ?>"
                                data-order-ConsigneeZipCode="<?php echo $order['ConsigneeZipCode']; ?>"
                                data-order-ConsigneeCountryCode="<?php echo $order['ConsigneeCountryCode']; ?>"
                                data-order-ConsigneeAddress="<?php echo $order['ConsigneeAddress']; ?>"
                                data-order-Carrier="<?php echo $order['Carrier']; ?>"
                                data-order-ClearanceAgent="<?php echo $order['ClearanceAgent']; ?>"
                                data-order-SKU="<?php echo $order['SKU']; ?>"
                                data-order-Quantity="<?php echo $order['Quantity']; ?>"
                                data-order-Currency="<?php echo $order['Currency']; ?>"
                                data-order-UnitInvoicePrice="<?php echo $order['UnitInvoicePrice']; ?>"
                                data-order-TotalInvoicePrice="<?php echo $order['TotalInvoicePrice']; ?>"
                                data-order-Comments="<?php echo $order['Comments']; ?>"
                                data-order-Company="<?php echo $order['Company']; ?>"
                                data-order-Phone="<?php echo $order['Phone']; ?>"

                                type="button" class="btn  btn btn-primary account_so">Create order
                            </button>
                            <!-- <button data-order-in-name="<?php echo $order['name']; ?>"  data-order-in-price="<?php echo $order['total_price']; ?>"  data-order-in-weight="<?php echo $order['total_weight']; ?>" type="button" class="btn   button_orderin">WMS Order In</button>
                                                    <button data-order-out-name="<?php echo $order['name']; ?>" data-order-out-weight="<?php echo $order['total_weight']; ?>" type="button" class="btn   button_orderout">WMS Order Out</button>
                                                    <button data-order-sku-name="<?php echo $order['name']; ?>" type="button" class="btn  button_ordersku">WMS Order Sku</button>-->
                        </td>
                        <td>
                            <?php echo $order['AWB']; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php $page = 0; ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php
                $indicator = $savedOrders[3];
                $indicator[0] = (isset($indicator[0]))? $indicator[0]: "";
                if ($indicator[0] == "fresh" || $indicator[0] == "new" || $indicator[0] == "allocated" || $indicator[0] == "part_allocated" || $indicator[0] == "picked" || $indicator[0] == "part_picked" || $indicator[0] == "cancelled" || $indicator[0] == "issued") {
                    $extraLink = '&status='.$indicator[0];
                } elseif ($indicator[0] == "processed") {
                    $extraLink = '&status=processed';
                } elseif ($indicator[0] == "time") {
                    $extraLink = '&time1=' . $indicator[1] . '&time2=' . $indicator[2];
                } else {
                    $extraLink = "";
                }
                while ($page++ < $savedOrders[1]): ?>
                    <?php if ($page == $savedOrders[2]): ?>
                        <li class="active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=1"><?= $page ?></a></li>
                    <?php else:
                        ?>
                        <li>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?= $page ?><?= $extraLink ?>"><?= $page ?></a>
                        </li>
                    <?php endif ?>
                <?php endwhile ?>
            </ul>
        </nav>
    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<?php
require 'templates/order_so.php';
require 'templates/order_in.php';
require 'templates/order_out.php';
require 'templates/order_sku.php';
require 'templates/settings.php';
?>
</body>
</html>
