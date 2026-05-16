<?php if(!defined('ABSPATH')) exit;

function srm_points_key($id){
return 'student_points_'.sanitize_key($id);
}

function srm_current_studentid(){
return get_user_meta(
get_current_user_id(),
get_option('srm_student_meta_key','studentid'),
true
);
}

function srm_current_points(){
$sid=srm_current_studentid();
return (int)get_option(srm_points_key($sid),0);
}

add_shortcode('student_points_balance',function(){
return srm_current_points();
});

add_action('frm_after_create_entry',function($eid,$fid){

if(!class_exists('FrmEntryMeta')) return;

if((int)get_option('srm_form_id')!==(int)$fid) return;

$sid=FrmEntryMeta::get_entry_meta_by_field(
$eid,
get_option('srm_student_field'),
true
);

$pts=(int)FrmEntryMeta::get_entry_meta_by_field(
$eid,
get_option('srm_points_field'),
true
);

$act=FrmEntryMeta::get_entry_meta_by_field(
$eid,
get_option('srm_action_field'),
true
);

$key=srm_points_key($sid);

$bal=(int)get_option($key,0);

update_option(
$key,
($act==='deduct'||$act==='Deduct')
? max(0,$bal-$pts)
: $bal+$pts,
false
);

},20,2);
