<?php
/**
 * Description: Fastway Couriers currently operates across key metropolitan and regional locations across Australia, offering a low cost and fast courier delivery service. Franchise opportunities also available.
 * Author: softwarehtec.com
 * Author URI: http://www.softwarehtec.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return false;
}


function fastway_au_shipping_zone_method() {
    if ( ! class_exists( 'Fastway_Au_Shipping_Zone_Method' ) ) {
        class Fastway_Au_Shipping_Zone_Method extends WC_Shipping_Method {
            var $api_key,$pickup_rfcode,$support_type,$custom_local_parcel_price;
            public function __construct($instance_id = 0 ) {
		$this->instance_id 			 = absint( $instance_id );
                $this->id                 = 'fastway_au-zone'; 
                $this->method_title       = __( 'Fastway AU', 'softwarehtec-fastwayau' );  
                $this->method_description = __( 'Fastway Couriers currently operates across key metropolitan and regional locations across Australia, offering a low cost and fast courier delivery service. Franchise opportunities also available.<br/><strong style="color:red">Currency Of Shipping Price Is In Australian Dollar</strong><br/><strong style="color:black">Support URL: <a href="http://www.softwarehtec.com/contact-us/" target="_blank">http://www.softwarehtec.com/contact-us/</a></strong><br/><strong style="color:black">Plugin URL: <a href="http://www.softwarehtec.com/project/woocommerce-australia-fastway-shipping-method/" target="_blank">http://www.softwarehtec.com/project/woocommerce-australia-fastway-shipping-method/</a></strong><br/><a href="http://au.api.fastway.org/latest/docs/page/GetAPIKey.html" target="_blank" style="font-weight:bold;">Get Fastway API Key</a> ', 'softwarehtec-fastwayau' ); 
                //$this->availability = 'including';

		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal');

                //$this->countries = array(
                //'AU'
                //);
                $this->init();

                //$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';

                $title = $this->get_option('title');

                $this->title = !empty( $title ) ? $title : __( 'Fastway AU Shipping', 'softwarehtec-fastwayau' );



                $this->api_key = $this->get_option('api_key');

                if(empty($this->api_key)){
                    $this->api_key = "b5056fe957ea82692b615808cfd881bc";
                }


                $this->pickup_rfcode = $this->get_option('pickup_rfcode');
                $this->support_type = $this->get_option('support_type');
                $this->tax_status = $this->get_option('tax_status');

                $this->surcharge_type = $this->get_option('surcharge_type');
                $this->surcharge_fee = $this->get_option('surcharge_fee');

                $this->custom_white_zone_parcel_price = $this->get_option('custom_white_zone_parcel_price');
                $this->custom_red_zone_parcel_price = $this->get_option('custom_red_zone_parcel_price');
                $this->custom_orange_zone_parcel_price = $this->get_option('custom_orange_zone_parcel_price');
                $this->custom_green_zone_parcel_price = $this->get_option('custom_green_zone_parcel_price');
                $this->custom_white_zone_parcel_price = $this->get_option('custom_white_zone_parcel_price');
                $this->custom_grey_zone_parcel_price = $this->get_option('custom_grey_zone_parcel_price');

                $this->custom_nat_a2_satchel_price = $this->get_option('custom_nat_a2_satchel_price');
                $this->custom_nat_a3_satchel_price = $this->get_option('custom_nat_a3_satchel_price'); 
                $this->custom_nat_a4_satchel_price = $this->get_option('custom_nat_a4_satchel_price'); 
                $this->custom_nat_a5_satchel_price = $this->get_option('custom_nat_a5_satchel_price');

                $this->custom_local_satchel_price = $this->get_option('custom_local_satchel_price'); 
                $this->custom_pink_parcel_price = $this->get_option('custom_pink_parcel_price'); 
                $this->custom_lime_parcel_price = $this->get_option('custom_lime_parcel_price'); 
                $this->custom_local_parcel_price = $this->get_option('custom_local_parcel_price'); 
                $this->custom_parcel_excess_price = $this->get_option('custom_parcel_excess_price'); 


            }

            function init() {
                $this->init_form_fields(); 
                $this->init_settings(); 
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }



            function init_form_fields() { 

                $rfcode = array(""=>"Please Select");
                $api_key = "";

                $shipping_option_key = $this->instance_id ? "woocommerce_" . "fastway_au-zone" . '_' . $this->instance_id . '_settings' : '';

                $formsetting = get_option($shipping_option_key);
 


                if(is_array($formsetting) && count($formsetting) > 0){
                    $api_key = $formsetting["api_key"];
                }

                if(empty($api_key)){
                    $api_key = "b5056fe957ea82692b615808cfd881bc";
                }

                $rfcode = get_option( "rfcode_".$this->instance_id) ;
 

                if ( !empty($rfcode) && $rfcode != "b:0;" && $rfcode != 'a:1:{s:0:"";s:13:"Please Select";}' ) {

                    $rfcode = unserialize($rfcode);

                } else {

                    if(!empty($api_key)){

                        $rfcode = array(""=>"Please Select");

                        if(!is_callable('curl_init')){
                            add_action( 'admin_notices', 'fastway_au_curl_error' );
                        }


                        $url = "http://au.api.fastway.org/latest/psc/listrfs?CountryCode=1&api_key=". $api_key;


                        $handle=curl_init($url);

                        curl_setopt($handle, CURLOPT_VERBOSE, true);
                        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
       
                        $content = curl_exec($handle);
                        $result = json_decode( $content); // show target page

                        $fastway_error = get_option( "fastway_error_".$this->instance_id );


                        if(isset($result->error)){
                            if ( $fastway_error !== false ) {
                                update_option("fastway_error_".$this->instance_id, $result->error );
                            } else {

                                $deprecated = null;
                                $autoload = 'no';
                                add_option( "fastway_error_".$this->instance_id, $result->error, $deprecated, $autoload );
                            }
                        }else{
                            if ( $fastway_error !== false ) {
                                update_option("fastway_error_".$this->instance_id, "" );
                            } else {

                                $deprecated = null;
                                $autoload = 'no';
                                add_option( "fastway_error_".$this->instance_id,"", $deprecated, $autoload );
                            }
                        }

                        if(isset($result->result)){
                            if(is_array($result->result)){
                                if(count($result->result) > 0){
                                    foreach($result->result as $v){
                                        $rfcode[$v->FranchiseCode] = $v->FranchiseName."( ".$v->Add1." ".$v->Add2." ".$v->Add3." ".$v->Add4." )";
                                    }
                                }
                            }
                        }
 
                    }
 
  
                    if ( get_option("rfcode_".$this->instance_id  ) !== false ) {
                        if(count($rfcode) > 0 ){
                            update_option( "rfcode_".$this->instance_id  , serialize($rfcode), null, 'no');
                        }else{
                            update_option( "rfcode_".$this->instance_id  , '', null, 'no');
                        }
                    }else{
                        if(count($rfcode) > 0 ){
                            add_option( "rfcode_".$this->instance_id , serialize($rfcode), null, 'no');
                        }else{
                            add_option( "rfcode_".$this->instance_id , '', null, 'no');
                        }
                    }
                }



                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'softwarehtec-fastwayau' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this shipping.', 'softwarehtec-fastwayau' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'softwarehtec-fastwayau' ),
                        'type' => 'text',
                        'description' => __( 'Title to be display on site', 'softwarehtec-fastwayau' ),
                        'default' => __( 'Fastway AU Shipping', 'softwarehtec-fastwayau' )
                    ),
                    'tax_status' => array(
                    'title'   => __( 'Tax status', 'woocommerce' ),
                    'type'    => 'select',
                    'class'   => 'wc-enhanced-select',
                    'default' => 'taxable',
                    'options' => array(
                        'taxable' => __( 'Taxable', 'woocommerce' ),
                        'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
                    ),
                    ),
                    'api_key' => array(
                        'title' => __( 'API Key', 'softwarehtec-fastwayau' ),
                        'type' => 'password',
                        'description' => __( '<a href="http://au.api.fastway.org/latest/docs/page/GetAPIKey.html" target="_blank" style="font-weight:bold;">Get Your Own Fastway API Key</a> or leave as empty', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'pickup_rfcode' => array(
                        'title' => __( 'Pickup Franchise', 'softwarehtec-fastwayau' ),
                        'type' => 'select',
                        'description' => __( 'Options will be presented after API Key was filled and saved ', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' ),
                        'options' => $rfcode 
                    ),
                    'surcharge_type' => array(
                        'title' => __( 'Surcharge Type', 'softwarehtec-fastwayau' ),
                        'type' => 'select',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' ),
                        'options' => array("fixed"=>"Fixed","percentage"=>"Percentage")
                    ),
                    'surcharge_fee' => array(
                        'title' => __( 'Surcharge Fee', 'softwarehtec-fastwayau' ),
                        'type' => 'text',
                        'description' => __( 'For "Fixed": subtotal + surcharge fee. For "Percentage": subtotal * surcharge', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'support_type' => array(
                        'title' => __( 'Service Type', 'softwarehtec-fastwayau' ),
                        'type' => 'select',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' ),
                        'options' => array(""=>"All","Parcel"=>"Parcel","Satchel"=>"Satchel")
                    ),
                    'custom_local_parcel_price' => array(
                        'title' => __( 'Custom Local Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_lime_parcel_price' => array(
                        'title' => __( 'Custom Lime Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_pink_parcel_price' => array(
                        'title' => __( 'Custom Pink Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_red_zone_parcel_price' => array(
                        'title' => __( 'Custom Red Zone Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),

                    'custom_orange_zone_parcel_price' => array(
                        'title' => __( 'Custom Orange Zone Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),

                    'custom_green_zone_parcel_price' => array(
                        'title' => __( 'Custom Green Zone Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),

                    'custom_white_zone_parcel_price' => array(
                        'title' => __( 'Custom White Zone Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),

                    'custom_grey_zone_parcel_price' => array(
                        'title' => __( 'Custom Grey Zone Parcel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_parcel_excess_price' => array(
                        'title' => __( 'Custom Parcel Excess Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_local_satchel_price' => array(
                        'title' => __( 'Custom Local Satchel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_nat_a5_satchel_price' => array(
                        'title' => __( 'Custom National A5 Satchel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_nat_a4_satchel_price' => array(
                        'title' => __( 'Custom National A4 Satchel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_nat_a3_satchel_price' => array(
                        'title' => __( 'Custom National A3 Satchel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),
                    'custom_nat_a2_satchel_price' => array(
                        'title' => __( 'Custom National A2 Satchel Price', 'softwarehtec-fastwayau' ),
                        'type' => 'decimal',
                        'description' => __( '', 'softwarehtec-fastwayau' ),
                        'default' => __( '', 'softwarehtec-fastwayau' )
                    ),

                );
 
            }


            public function calculate_shipping( $package = array() ) {

                $weight = 0;
                $cost = 0;
                $country = $package["destination"]["country"];
                if($country != "AU"){
                    return ;
                }

                foreach ( $package['contents'] as $item_id => $values ) { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }

                $weight = wc_get_weight( $weight, 'kg' );
                if($weight == 0 || $weight  > 25){
                    return ;
                }
 

                $d_suburb = urlencode($package["destination"]["city"]);
                $d_postcode = urlencode($package["destination"]["postcode"]);

                if(empty($this->pickup_rfcode ) || empty($this->api_key)){
                    return ;
                }
                if(empty($d_suburb ) || empty($d_postcode)){
                    return ;
                }




 
                $url = "http://au.api.fastway.org/v3/psc/lookup/".$this->pickup_rfcode ."/".$d_suburb."/".urlencode($d_postcode)."/".($weight)."?api_key=".$this->api_key;
                $url  = str_replace ( '+', '%20', $url );
                $content = file_get_contents( $url );
 
                $result = json_decode( $content); // show target page
 
                $fastway_error = get_option( "fastway_error_".$this->instance_id );

                if(isset($result->error)){
                    if ( $fastway_error  !== false ) {
                        update_option("fastway_error_".$this->instance_id, $result->error );

                    } else {

                        $deprecated = null;
                        $autoload = 'no';
                        add_option( "fastway_error_".$this->instance_id, $result->error, $deprecated, $autoload );
                    }
                }
                if(isset($result->result)){

                    if ( $fastway_error !== false ) {
                        update_option("fastway_error_".$this->instance_id, "" );

                    } else {

                        $deprecated = null;
                        $autoload = 'no';
                        add_option( "fastway_error_".$this->instance_id, "", $deprecated, $autoload );
                    }
                    $parcel_price = 999999;
                    $satchel_price = 999999;
                    $excess_package = 0;

                    if(count($result->result->services) > 0){

                        foreach($result->result->services as $k => $r){
 
                            if($r->type == "Parcel"){

                                 
                                $tmp_price = "";
                                $exc_price = $this->custom_parcel_excess_price;

                                if($r->name == "Local"){
                                    $tmp_price = $this->custom_local_parcel_price;
                                }else{
                                    if($r->labelcolour == "LIME"){
                                        $tmp_price = $this->custom_lime_parcel_price;
                                    }else if($r->labelcolour == "PINK"){
                                        $tmp_price = $this->custom_pink_parcel_price;
                                    }else if($r->labelcolour == "RED"){
                                        $tmp_price = $this->custom_red_zone_parcel_price;
                                    }else if($r->labelcolour == "ORANGE"){
                                        $tmp_price = $this->custom_orange_zone_parcel_price;
                                    }else if($r->labelcolour == "GREEN"){
                                        $tmp_price = $this->custom_green_zone_parcel_price;
                                    }else if($r->labelcolour == "WHITE"){
                                        $tmp_price = $this->custom_white_zone_parcel_price;
                                    }else if($r->labelcolour == "GREY"){
                                        $tmp_price = $this->custom_grey_zone_parcel_price;
                                    }
                                }

                                if(is_numeric($tmp_price)){
                                    $exc = $r->excess_labels_required;

                                    if($exc > 0){
                                        if(is_numeric($exc_price) && !empty($exc_price)){
                                            $tmp_price = $tmp_price + ($exc_price * $exc);
                                        }else{
                                            $tmp_price = $tmp_price + $r->excess_label_price_normal;
                                        }
                                    }

                                    if($parcel_price > $tmp_price){
                                        $parcel_price = $tmp_price;
                                    }
                                }


                                if($parcel_price > $r->totalprice_normal && !is_numeric($tmp_price)){
                                    $parcel_price = $r->totalprice_normal;
                                }
                            }
                            if($r->type == "Satchel"){

                                $tmp_price = "";
                                if($r->labelcolour == "SAT-LOC-A3"){
                                    $tmp_price = $this->custom_local_satchel_price;
                                }else
                                if($r->labelcolour == "SAT-NAT-A2"){
                                    $tmp_price = $this->custom_nat_a2_satchel_price;
                                }else
                                if($r->labelcolour == "SAT-NAT-A3"){
                                    $tmp_price = $this->custom_nat_a3_satchel_price;
                                }else
                                if($r->labelcolour == "SAT-NAT-A4"){
                                    $tmp_price = $this->custom_nat_a4_satchel_price;
                                }else
                                if($r->labelcolour == "SAT-NAT-A5"){
                                    $tmp_price = $this->custom_nat_a5_satchel_price;
                                }


                                if(is_numeric($tmp_price)){
                                    if($satchel_price > $tmp_price){
                                        $satchel_price = $tmp_price;
                                    }
                                }


                                if($satchel_price > $r->totalprice_normal && !is_numeric($tmp_price)){
                                    $satchel_price = $r->totalprice_normal;
                                }
                            }
                        }

                        $extra_fee = 0;

                        if(!empty($this->surcharge_fee) && is_numeric($this->surcharge_fee)){
                            if($this->surcharge_type == "fixed"){
                                $extra_fee = $this->surcharge_fee;
                            }else{
                                $extra_fee = $this->surcharge_fee * $package["cart_subtotal"];
                            }
                        }


                        if(empty($this->support_type) || $this->support_type == "Parcel"){
                            if($parcel_price != 999999){
                                if($this->tax_status  != "none"){
                                    $rate = array(
                                    'id' => $this->id."-parcel",
                                    'label' => $this->title." - Parcel (".$result->result->delivery_timeframe_days." Days) ",
                                    'cost' => $parcel_price+$extra_fee,
                                    'calc_tax' => 'per_order'
                                    );

                                }else{
                                    $rate = array(
                                    'id' => $this->id."-parcel",
                                    'label' => $this->title." - Parcel (".$result->result->delivery_timeframe_days." Days) ",
                                    'cost' => $parcel_price+$extra_fee,
                                    'taxes' => false
                                    );
 
                                }
                                $this->add_rate( $rate );
                            }
                        }

                        if(empty($this->support_type) || $this->support_type == "Satchel"){
                            if($satchel_price != 999999){
                                if($this->tax_status  != "none"){
                                    $rate = array(
                                    'id' => $this->id."-satchel",
                                    'label' => $this->title." - Satchel  (".$result->result->delivery_timeframe_days." Days) ",
                                    'cost' => $satchel_price+$extra_fee,
                                    'calc_tax' => 'per_order'
                                    );
                                }else{
                                    $rate = array(
                                    'id' => $this->id."-satchel",
                                    'label' => $this->title." - Satchel  (".$result->result->delivery_timeframe_days." Days) ",
                                    'cost' => $satchel_price+$extra_fee,
                                    'taxes' => false
                                    );
                                }
                                $this->add_rate( $rate );
                            }
                        }

                    }
                }
            }
        }
    }
}

add_action( 'woocommerce_shipping_init', 'fastway_au_shipping_zone_method' );
 
function add_fastway_au_shipping_zone_method( $methods ) {
    $methods['fastway_au-zone'] = 'Fastway_Au_Shipping_Zone_Method';
    return $methods;
}
 
add_filter( 'woocommerce_shipping_methods', 'add_fastway_au_shipping_zone_method' );

