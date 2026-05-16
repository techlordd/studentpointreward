<?php if(!defined('ABSPATH')) exit;

/* PRODUCT META */
add_action('woocommerce_product_options_general_product_data',function(){

woocommerce_wp_text_input([
'id'=>'reward_points_cost',
'label'=>'Reward Points Cost'
]);

});

add_action('woocommerce_process_product_meta',function($id){

if(isset($_POST['reward_points_cost'])){

update_post_meta(
$id,
'reward_points_cost',
sanitize_text_field($_POST['reward_points_cost'])
);

}

});

/* PRODUCT PAGE UI */
add_action('woocommerce_single_product_summary',function(){

global $product;

$cost=(int)get_post_meta(
$product->get_id(),
'reward_points_cost',
true
);

if(!$cost) return;

$points=function_exists('srm_current_points')
? srm_current_points()
:0;

echo '<div style="padding:15px;border:1px solid #ddd;margin:20px 0;">';

echo '<strong>Redeem with '.$cost.' points</strong><br>';

echo 'You have '.$points.' points<br>';

if($points >= $cost){

echo '<p style="color:green;">Eligible for redemption</p>';

echo '<a class="button" href="?redeem_points=1&product_id='.
$product->get_id().
'">
Redeem With Points
</a>';

}else{

echo '<p style="color:red;">Need '.($cost-$points).' more points</p>';

}

echo '</div>';

},25);

/* REDEEM FLOW */
add_action('template_redirect',function(){

if(
isset($_GET['redeem_points'])
&& isset($_GET['product_id'])
&& is_user_logged_in()
){

$product_id=(int)$_GET['product_id'];

$cost=(int)get_post_meta(
$product_id,
'reward_points_cost',
true
);

$points=function_exists('srm_current_points')
? srm_current_points()
:0;

if ($points >= $cost) {

$redeem_error_message='Unable to redeem this product right now. Please try again.';

if (!WC()->cart) {

wc_add_notice(
$redeem_error_message,
'error'
);

wp_safe_redirect(get_permalink($product_id));

exit;

}

$existing_item_keys = array_keys(WC()->cart->get_cart());

$cart_item_key=WC()->cart->add_to_cart($product_id);

if ($cart_item_key) {

foreach ($existing_item_keys as $existing_item_key) {

WC()->cart->remove_cart_item($existing_item_key);
}

WC()->session->set('srm_reward_checkout',1);

wp_safe_redirect(wc_get_checkout_url());

exit;

}

wc_add_notice(
$redeem_error_message,
'error'
);

wp_safe_redirect(get_permalink($product_id));

exit;

}

}

});

/* CHECKOUT DISCOUNT OPTION */
add_action('woocommerce_review_order_before_payment',function(){

if(!is_user_logged_in()) return;

echo '<p>
<label>
<input type="checkbox" name="use_points_discount" value="1">
Use my points for discount
</label>
</p>';

});

add_action('woocommerce_checkout_update_order_review',function($posted){

parse_str($posted,$d);

if(isset($d['use_points_discount'])){

WC()->session->set('use_points_discount',1);

}else{

WC()->session->__unset('use_points_discount');

}

});

/* APPLY DISCOUNT */
add_action('woocommerce_cart_calculate_fees',function($cart){

if(is_admin() && !defined('DOING_AJAX')) return;

if(!WC()->session || !WC()->session->get('use_points_discount')) return;

$points=function_exists('srm_current_points')
? srm_current_points()
:0;

if($points<=0) return;

$rate=(float)get_option('srm_discount_rate',0.10);

$discount=min(
$cart->subtotal,
$points*$rate
);

if($discount>0){

$cart->add_fee(
'Student Points Discount',
-$discount
);

}

},20);

/* DEDUCT POINTS AFTER ORDER */
add_action('woocommerce_checkout_order_processed',function($order_id){

if(!is_user_logged_in()) return;

$sid=function_exists('srm_current_studentid')
? srm_current_studentid()
:'';

if(!$sid) return;

$key='student_points_'.sanitize_key($sid);

$bal=(int)get_option($key,0);

$order=wc_get_order($order_id);

$total_deduct=0;

foreach($order->get_items() as $item){

$total_deduct += (int)get_post_meta(
$item->get_product_id(),
'reward_points_cost',
true
);

}

if($total_deduct>0){

update_option(
$key,
max(0,$bal-$total_deduct),
false
);

}

},20);
