<?php
/*
Plugin Name: Woocommerce Western Union Payment Addon
Plugin URI: https://daxdax89.com
Description: Integrates Western Union with Woocommerce with name randomizing and order settings
Version: 2.0
Author: DaX
Author URI: https://daxdax89.com
License: GPL2
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
echo '<div class="form-wu">
<form method="post" class="login-form-wu">';
if ($data) {
    echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" value="'.$data['order-id'].'"/>';
} elseif ($_GET['orderid']) {
    echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" value="'.$_GET['orderid'].'" readonly/>';
} else {
    echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" required/>';
}
?>
<label for="payment-method">Payment Method:</label> <input type="text" name="payment-method" value="Western Union" readonly/>
<label for="customer-email">Your registered Email:</label> <input type="email" name="customer-email" required/>
<label for="customer-name">Receiver First name:</label> <input type="text" name="customer-name" required/>
<label for="customer-last-name">Receiver Last name:</label> <input type="text" name="customer-last-name" required/>
<label for="customer-payment-country">Payment Country:</label> <input type="text" name="customer-payment-country" required/>
<label for="customer-mtcn">MTCN#:</label> <input type="text" name="customer-mtcn" required/>
<input type="submit" value="Submit">
</form>
</div>
