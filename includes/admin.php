<?php if(!defined('ABSPATH')) exit;
add_action('admin_menu',function(){
add_menu_page('Rewards Marketplace','Rewards Marketplace','manage_options','srm-settings','srm_settings_page');
});

add_action('admin_init',function(){
foreach([
'srm_form_id',
'srm_student_field',
'srm_points_field',
'srm_action_field',
'srm_discount_rate',
'srm_student_meta_key'
] as $o){
register_setting('srm_group',$o);
}
if(!get_option('srm_student_meta_key')){
update_option('srm_student_meta_key','studentid');
}
});

function srm_field($n){
echo "<input style='width:320px' name='{$n}' value='".esc_attr(get_option($n))."' />";
}

function srm_settings_page(){
?>
<div class="wrap">
<h1>Student Rewards Marketplace Pro v2.8</h1>
<form method="post" action="options.php">
<?php settings_fields('srm_group'); ?>

<table class="form-table">
<tr><th>Form ID</th><td><?php srm_field('srm_form_id');?></td></tr>
<tr><th>Student Field ID</th><td><?php srm_field('srm_student_field');?></td></tr>
<tr><th>Points Field ID</th><td><?php srm_field('srm_points_field');?></td></tr>
<tr><th>Action Field ID</th><td><?php srm_field('srm_action_field');?></td></tr>
<tr><th>Discount Value Per Point</th><td><?php srm_field('srm_discount_rate');?></td></tr>
<tr><th>User Meta Key</th><td><?php srm_field('srm_student_meta_key');?></td></tr>
</table>

<?php submit_button(); ?>
</form>
</div>
<?php }