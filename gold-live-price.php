<?php
/*
  Plugin Name: Gurkha Live Price
  Plugin URI: https://www.awesconcepts.com/
  Description: WordPress with Gold Price
  Version: 1.0
  Author: Awes Concepts
  Author URI: https://www.awesconcepts.com/
*/
ob_start();
define('GLP', '1.0.0');
define('GLP__PLUGIN_URL', plugin_dir_url(__FILE__));
define('GLP__PLUGIN_DIR', plugin_dir_path(__FILE__));
$upload_dir = wp_upload_dir();
/* ********************************************* */
/* Plugin Activation & Deactivation Code Start * */
/* ********************************************* */
register_activation_hook(__FILE__, 'glp_install');
register_deactivation_hook(__FILE__, 'glp_deactivate');
register_uninstall_hook(__FILE__, 'glp_uninstall');
/* ********************************************* */
/* Upgrading Files if Required ***************** */
/* ********************************************* */
function glp_install() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	add_option('glp_interval','0.25');
	add_option('glp_refresh','1');
	add_option('glp_feed_url','http://www.xmlcharts.com/live/precious-metals.php?format=json');
}

function glp_uninstall(){
	delete_option('glp_interval');
	delete_option('glp_refresh');
}
function glp_deactivate() {
	delete_option("glp");
}

function glp_config() {
    include_once(GLP__PLUGIN_DIR.'glp-config.php');
}
function glp_textdomain() {
	$path=GLP__PLUGIN_DIR . 'languages/';
	load_plugin_textdomain('GLP', false, $path);
}
add_action('plugins_loaded', 'glp_textdomain');

/* ********************************************* */
/* Register CSS and JS Dependencies ************ */
/* ********************************************* */
function glp() {
    add_menu_page('GLP Feeds', 'GLP Feeds', 'manage_options', 'config', 'glp_config', GLP__PLUGIN_URL . '/images/favicon.ico');
}
add_action('admin_menu', 'glp');
/* ********************************************* */
/* Option to JS Dependencies ************ */
/* ********************************************* */
function getFeeds(){
	$content='';
	if(get_option("glp_feed_url")=='xmlcharts'){
		$content=file_get_contents('http://www.xmlcharts.com/live/precious-metals.php?format=json');
		if (!$content) die('Something went wrong, retry loading.'); 
		return json_decode($content, true);
	}else{
		//
	}
	
		
}
$glp_refresh=get_option('glp_refresh');
$glp_interval=get_option('glp_interval');
function glp_object_option() {
	$content='';
	$json=array();
	if(!isset($_SESSION['livePrice']) && sizeof($_SESSION['livePrice'])==0){
		$json=getFeeds();
		$_SESSION['livePrice']=$json;
	}else{
		$json=$_SESSION['livePrice'];
	}
	$cur=get_woocommerce_currency();
    echo '<script type="text/javascript"> 
	var glp={ajax:"'.admin_url("admin-ajax.php").'",refresh:'.get_option("glp_refresh").',currency:"'.strtolower($cur).'",interval:'. get_option("glp_interval").',feed:'.json_encode($json[strtolower($cur)]).',symbol:"'.get_woocommerce_currency_symbol($cur).'"}; 
	</script>';
}
add_action('admin_head', 'glp_object_option');
add_action('wp_head', 'glp_object_option');
/* ********************************************* */
/* GLP Styles ********************************** */
/* ********************************************* */
function glp_styles() {
	if(is_admin()){
        
		/* Admin Core Stylesheet */
		wp_register_style('glp_admin_css', GLP__PLUGIN_URL . 'css/glp.admin.css');
        wp_enqueue_style('glp_admin_css');
	}else{
		/* Core CSS for Frontend */
		wp_register_style('glp_front_css', GLP__PLUGIN_URL . 'css/glp.front.css');
		wp_enqueue_style('glp_front_css');
	}
}

add_action('init', 'glp_styles');
/* ********************************************* */
/* GLP Scripts ********************************** */
/* ********************************************* */

function glp_script() {
	if(!is_admin()){
	wp_enqueue_script( 'glp-liveprice',GLP__PLUGIN_URL. 'js/livePrice.js', array( 'jquery' ), '2014-02-01', true );
	}else{
	wp_enqueue_script( 'glp-liveprice-admin',GLP__PLUGIN_URL. 'js/livePriceAdmin.js', array( 'jquery' ), '2014-02-01', true );
	}
}
add_action( 'init', 'glp_script' );
add_action( 'wp_ajax_nopriv_live_price', 'livePrice' );
add_action( 'wp_ajax_live_price', 'livePrice' );
function livePrice() {
	$json=getFeeds();
	$_SESSION['livePrice']=$json;
	$toReturn=[];
	foreach($_REQUEST['p'] as $post){
		$_weight=$post['w'];
		$_units=$post['u'];
		$_metal=$post['m'];
		$_pid=$post['p'];
		$_currency=$post['c'];
		
		//formula
		$forms=gpl_fomulas();
		
		// for calculation
		$am=($json[$_currency][$_metal]*$forms[$_units])*$_weight;
		$_price=array("price"=>$am,"tax"=>0,"tax_amount"=>0);
		/* if product id is set */
		if(!empty($_pid) || $_pid!='0'){ 
			
			$_price=getPruductPrice($_pid,$_currency);
			
			//update_post_meta($_pid, '_price', $_price);
		}
		/* end if product id is set */
		$toReturn[]=array(
			"we"=>$_weight,
			"un"=>$_units,
			"me"=>$_metal,
			"pi"=>$_pid,
			"pr"=>$_price['price'],
			"cu"=>$_currency,
			"tx"=>$_price['tax'],
			"tm"=>$_price['tax_amount']
			);
	}
	echo json_encode($toReturn);
	die;
}

function gpl_fomulas(){
	return array(
		'gram'=>1,
		'toz'=>31.1034768,
		'oz'=>31.1034768,
		'tola'=>12.0045888,
	);
}
function register_session(){
    if( !session_id() )
        session_start();
}
add_action('init','register_session');
function getPruductPrice($id,$_currency_=''){
	$forms=gpl_fomulas();
	$json=$_SESSION['livePrice'];
	$meta = get_post_meta( $id );
	$_weight=meta_val($meta,'_goldpricelive_weight');
	if($_weight=='')$_weight='1';
	$_metal=meta_val($meta,'_goldpricelive_metal');
	if($_metal=='')$_metal='gold';
	$_units=meta_val($meta,'_goldpricelive_units');
	if($_units=='')$_units='gram';

	$_currency=$_currency_;
	if($_currency_==''){
		$_currency=strtolower(get_woocommerce_currency());
	}
	$_price=0;
	$purity=floatval(meta_val($meta,'_goldpricelive_purity'));
	if($purity=='')$purity='1';
	$markup=floatval(meta_val($meta,'_goldpricelive_markup'));
	if($markup=='')$markup=0;
	$fixed_amount=floatval(meta_val($meta,'_goldpricelive_fixed_amount'));
	if($fixed_amount=='')$fixed_amount=0;

	$spot_price = $json[$_currency][$_metal]*$forms[$_units]; 
	
	$price = ($spot_price * $_weight * $purity * (1 + ($markup / 100))) + $fixed_amount;
	
	
	if($_metal != "" && $_weight > 0 && $_units != "" && $purity > 0 && $markup >= 0) {
		$_price=$price;
	}
	/* Start markdown */
	$_goldpricelive_markdown=floatval(meta_val($meta,'_goldpricelive_markdown'));
	if($_goldpricelive_markdown!="" && $_goldpricelive_markdown!=0){
		$_price=$_price-(($_price/100)*$_goldpricelive_markdown);
	}
	
	$_goldpricelive_fixed_markdown=floatval(meta_val($meta,'_goldpricelive_fixed_markdown'));
	if($_goldpricelive_fixed_markdown!="" && $_goldpricelive_fixed_markdown!=0){
		$_price=$_price-$_goldpricelive_fixed_markdown;
	}
	/* End markdown */
	$toReturn=array(
	"price"=>$_price,
	"tax_amount"=>0,
	"tax"=>0
	);
	//return $_price;
	$_tax = new WC_Tax();
	$_product = get_product($id);
	$_rates = array_shift($_tax->get_rates( $_product->get_tax_class() ));
	if($_product->get_tax_class()!='' && isset($_rates['rate'])){
		$item_rate = round($_rates['rate'],2);
		$tax_amount=($_price/100)*$item_rate;
		$toReturn['tax']=$item_rate;
		$toReturn['tax_amount']=$tax_amount;
	}
	return $toReturn;
	
}
function price_with_pid($atts){
	extract(shortcode_atts(array(
        'id'=> '',
		'entity'=> ''
	), $atts));
	$meta = get_post_meta( $id );//print_r($meta);

	$weight=meta_val($meta,'_goldpricelive_weight');
	if($weight=='')$weight='1';
	$metal=meta_val($meta,'_goldpricelive_metal');
	if($metal=='')$metal='gold';
	$units=meta_val($meta,'_goldpricelive_units');
	if($units=='')$units='gram';
	$currency=strtolower(get_woocommerce_currency());
	return '<span class="price _loading"><span class="symbol">'.getSymbols($currency).'</span><span class="amount" data-units="'.$units.'" data-weight="'.$weight.'"  data-metal="'.$metal.'" data-pid="'.$id.'" data-currency="'.$currency.'" data-dyn>'.get_imgs().'</span></span>';
}
add_shortcode("woocommerceproduct","price_with_pid");
function getSymbols($e){
	$list=array(
		"eur"=>'€',
		"usd"=>'$',
		"gbp"=>'£',
	);
	if(isset($list[$e])){
		return $list[$e];
	}
	return $e;
	
}
function glp_price($atts){
	extract(shortcode_atts(array(
        'weight'=> '1',
		'metal'=> 'gold',
		'units'=> 'gram',
		'id'=> '0',
		'currency'=> strtolower(get_woocommerce_currency())
	), $atts));
	if(!empty($id) || $id!='0'){
	$meta = get_post_meta( $id );
	$weight=meta_val($meta,'_goldpricelive_weight');
	if($weight=='')$weight='1';
	$metal=meta_val($meta,'_goldpricelive_metal');
	if($metal=='')$metal='gold';
	$units=meta_val($meta,'_goldpricelive_units');
	if($units=='')$units='gram';
	}
	return '<span class="price _loading"><span class="symbol">'.getSymbols($currency).'</span><span class="amount" data-units="'.$units.'" data-weight="'.$weight.'"  data-metal="'.$metal.'" data-pid="'.$id.'" data-currency="'.$currency.'" data-dyn>'.get_imgs().'</span></span>';
}
add_shortcode("glp","glp_price");
function meta_val($e,$k){
	if(isset($e[$k]) && isset($e[$k][0])){
		return $e[$k][0];
	}
	return '';
}
add_filter( 'woocommerce_get_price_html', 'glp_price_html', 100, 2 );
function glp_price_html( $price, $product ){
	//print_r($product);
	$meta = get_post_meta( $product->id );
	if(!is_admin()){
	$weight=meta_val($meta,'_goldpricelive_weight');
	if($weight=='')$weight='1';
	$metal=meta_val($meta,'_goldpricelive_metal');
	if($metal=='')$metal='gold';
	$units=meta_val($meta,'_goldpricelive_units');
	if($units=='')$units='gram';
	$currency=strtolower(get_woocommerce_currency());
    return '<span class="symbol">'.getSymbols($currency).'</span><span class="amount" data-units="'.$units.'" data-weight="'.$weight.'"  data-metal="'.$metal.'"  data-pid="'.$product->id.'" data-currency="'.$currency.'" data-dyn>'.get_imgs().'</span>';
	}
	return $price;
}
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price', 15, 3 );
function add_custom_price( $cart_object ) {
	foreach ( $cart_object->get_cart() as $item_values ) {

        ##  Get cart item data
        $item_id = $item_values['data']->id; // Product ID
        $item_qty = $item_values['quantity']; // Item quantity
        $original_price = $item_values['data']->price; // Product original price

        // Getting the object
        $product = new WC_Product( $item_id );
        if($product){
			$__pdata=getPruductPrice($item_id);
			$__actual_price=$__pdata['price'];
			$_discount=getQtyDiscountPercentage($item_id,$item_qty);
			$__selling_price=$__actual_price - ($__actual_price * ($_discount / 100));
			if(!$_discount)$__selling_price=$__actual_price;
            $item_values['data']->set_price($__selling_price);//$__selling_price;

        }
    }
	
}
add_filter( 'woocommerce_cart_item_price', 'cart_item_price', 15, 3 );
function cart_item_price( $price, $cart_item, $cart_item_key ) {
	//print_r($cart_item);
    if (isset($cart_item[ 'data' ])){
        $price = $cart_item[ 'line_subtotal' ]/$cart_item[ 'quantity'];
    }
	$txt='';
	if (isset($cart_item[ 'line_subtotal_tax' ]) && floatval($cart_item[ 'line_subtotal_tax' ])>0){
        $price = ($cart_item[ 'line_subtotal']+$cart_item[ 'line_subtotal_tax'])/$cart_item[ 'quantity'];
		$txt = " (inc. VAT)";
    }

    return number_format($price,2) .$txt;
}

//add_filter('woocommerce_add_cart_item', 'add_cart_item', 10, 1);
function add_cart_item($cart_item) {
    return $cart_item;
}
function getQtyDiscountPercentage($id,$qty){
	$_data=getProdDiscount($id);
	foreach($_data['discount'] as $_di){
		if($qty>=$_di->min && $qty<=$_di->max){
			return floatval($_di->amount);
		}
	}
	return 0;
}
function getDiscountArray($id){
	$_goldpricelive_exclude_discount=get_post_meta($id, '_goldpricelive_exclude_discount', true );
	if($_goldpricelive_exclude_discount=='1') return array();
	
	
	$terms = get_the_terms ($id, 'product_cat' );
	$_term_ids=_toSerialArray($terms);
	$_glp_qty_discounts='';
	foreach($_term_ids as $_term_id){
		$glp_qty_discounts = get_term_meta($_term_id, 'quantity_discounts', true);
		if(!empty($glp_qty_discounts)){
			$_glp_qty_discounts=$glp_qty_discounts;
			break;
		}
	}
	$_discounts=json_decode($_glp_qty_discounts);
	if(!is_array($_discounts)){
		$_discounts=array();
	}
	return $_discounts;
}
function getProdDiscount($id){
	$_goldpricelive_metal=get_post_meta($id, '_goldpricelive_metal',true);
	return array("discount"=>getDiscountArray($id),"metal"=>$_goldpricelive_metal,"id"=>$id);
}
//woocommerce_before_add_to_cart_form
add_action( 'woocommerce_after_add_to_cart_button', 'product_special_shortcode', 20 );
function product_special_shortcode ($e) {
	global $post; 
	$_data=getProdDiscount($post->ID);
	$_price_data=getPruductPrice($post->ID);
	$_discounts=$_data['discount'];
	if(is_array($_discounts) && sizeof($_discounts)>0){
		if($_price_data['tax_amount']!=0){
			include_once(GLP__PLUGIN_DIR.'glp-qty-discounts-tax.php');
		}else{
			include_once(GLP__PLUGIN_DIR.'glp-qty-discounts.php');
		}
	}
}
function glp_top_table($atts){
	extract(shortcode_atts(array(
        'symbols'=> '£,€,$',
		'currencys'=> 'gbp,eur,usd',
		'metals'=> 'gold,silver',
		'units'=> 'oz,gram,tola',
	), $atts));
	$listc=['gbp','eur','usd'];
	$lists=['£','€','$'];
	$listm=['gold','silver'];
	$listu=['oz','gram','tola'];
	if(!empty($symbols)){
		$lists=explode(',',$symbols);
	}
	if(!empty($currencys)){
		$listc=explode(',',$currencys);
	}
	if(!empty($metals)){
		$listm=explode(',',$metals);
	}
	if(!empty($units)){
		$listu=explode(',',$units);
	}
	
	
	$forms=gpl_fomulas();
	?>
	<div class="glp_tabstop_container">
		<ul class="tablist">
			<?php 
			$ind='';
			foreach($listc as $_ck => $ck){ 
				$class=$ind==''?'active':'';
				$ind='activated';
			?>
			<li>
				<a href="javascript:void(0);" class="<?php echo $class; ?>" data-target="<?php echo $ck; ?>">
					<?php echo $ck; ?>
				</a>
			</li>
			<?php } ?>
		</ul>
		<div class="glp_tabstop_contents">
			<?php 
			$ind='';
			foreach($listc as $_ck => $ck){ 
			$class=$ind==''?'active':'';
				$ind='activated';
			?>
			<div class="glp_content <?php echo $class; ?>" id="<?php echo $ck; ?>">
				<table>
					<tbody>
						<tr>
							<td>
								Metals
							</td>
							<?php 
							foreach($listu as $_uk => $um){
							?>
							<td>
								<?php echo $um; ?>
							</td>
							<?php
							}
							?>
						</tr>
						<?php 
						foreach($listm as $_mk => $m){
						?>
						<tr>
							<td>
								<?php echo ucfirst($m); ?>
							</td>
							<?php foreach($listu as $_uk => $_u){ ?>
							<td>
								<span class="price">
									<span class="symbol"><?php echo $lists[$_ck]; ?></span>
									<span class="amount" data-units="gram" data-weight="<?php echo $forms[$_u]; ?>" data-metal="<?php echo $m; ?>" data-pid="0" data-currency="<?php echo $ck; ?>" data-dyn=""><?php echo get_imgs(); ?></span>
								</span>
							</td>
							<?php } ?>
						</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php
}
add_shortcode('glp_top','glp_top_table');
function add_wpdocs_meta_box() {
    $var1 = 'this';
    $var2 = 'that';
    add_meta_box(
        'metabox_id',
        __('Price Settings', 'textdomain'),
        'glp_meta_fields',
        'product',
        'side',
        'high',
        array( 'foo' => $var1, 'bar' => $var2 )
    );
}
 
/**
 * Get post meta in a callback
 *
 * @param WP_Post $post    The current post.
 * @param array   $metabox With metabox id, title, callback, and args elements.
 */
 
function glp_meta_fields( $post, $metabox ) {
    // Output value of custom field.
    $_goldpricelive_metal=get_post_meta( $post->ID, '_goldpricelive_metal', true );
	if($_goldpricelive_metal=="")$_goldpricelive_metal="gold";
	$_goldpricelive_weight=get_post_meta( $post->ID, '_goldpricelive_weight', true );
	if($_goldpricelive_weight=="")$_goldpricelive_weight="0";
	$_goldpricelive_units=get_post_meta( $post->ID, '_goldpricelive_units', true );
	if($_goldpricelive_units=="")$_goldpricelive_units="gram";
	$_goldpricelive_purity=get_post_meta( $post->ID, '_goldpricelive_purity', true );
	if($_goldpricelive_purity=="")$_goldpricelive_purity="1";
	$_goldpricelive_markup=get_post_meta( $post->ID, '_goldpricelive_markup', true );
	if($_goldpricelive_markup=="")$_goldpricelive_markup="0";
	$_goldpricelive_fixed_amount=get_post_meta( $post->ID, '_goldpricelive_fixed_amount', true );
	if($_goldpricelive_fixed_amount=="")$_goldpricelive_fixed_amount="0";
	$_goldpricelive_purity=get_post_meta( $post->ID, '_goldpricelive_purity', true );
	if($_goldpricelive_adjustement=="")$_goldpricelive_adjustement="0";
	/* Markdown */
	$_goldpricelive_markdown=get_post_meta( $post->ID, '_goldpricelive_markdown', true );
	if($_goldpricelive_markdown=="")$_goldpricelive_markdown="0";
	$_goldpricelive_fixed_markdown=get_post_meta( $post->ID, '_goldpricelive_fixed_markdown', true );
	if($_goldpricelive_fixed_markdown=="")$_goldpricelive_fixed_markdown="0";
	// markdown end
	
	$_goldpricelive_price=get_post_meta( $post->ID, '_goldpricelive_price', true );
	if($_goldpricelive_price=="")$_goldpricelive_price="0";
	$_goldpricelive_price_num=get_post_meta( $post->ID, '_goldpricelive_price_num', true );
	if($_goldpricelive_price_num=="")$_goldpricelive_price_num="0";
	
	$_goldpricelive_exclude_discount=get_post_meta( $post->ID, '_goldpricelive_exclude_discount', true );
	?>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Metal Type: ','GLP'); ?></label>
			<div class="glp_field">
<select class="full" name="_goldpricelive_metal" id="_goldpricelive_metal" onChange="livePriceAdmin.fillPrice()">
<option value="gold" <?php echo ($_goldpricelive_metal=='gold')?'selected="selected"':''; ?>>Gold</option>
<option value="silver" <?php echo ($_goldpricelive_metal=='silver')?'selected="selected"':''; ?>>Silver</option>
</select>
				<div class="clear"></div>
			</div>

			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Weight: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="0.01" class="weight" value="<?php echo $_goldpricelive_weight; ?>" onChange="livePriceAdmin.fillPrice()" name="_goldpricelive_weight" id="_goldpricelive_weight" />
				<select class="units" name="_goldpricelive_units" id="_goldpricelive_units">
					<option value="gram" <?php echo ($_goldpricelive_units=='gram')?'selected="selected"':''; ?>>Grams</option>
					<option value="toz" <?php echo ($_goldpricelive_units=='toz')?'selected="selected"':''; ?>>troy ounce</option>
				</select>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Purity: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="0.01" class="full" value="<?php echo $_goldpricelive_purity; ?>" id="_goldpricelive_purity" name="_goldpricelive_purity" onChange="livePriceAdmin.fillPrice()" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Fixed Markup Percentage: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="any" value="<?php echo $_goldpricelive_markup; ?>" class="full" id="_goldpricelive_markup" name="_goldpricelive_markup" onChange="livePriceAdmin.fillPrice()" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Fixed Markup: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="any" class="full" value="<?php echo $_goldpricelive_fixed_amount; ?>" id="_goldpricelive_fixed_amount" name="_goldpricelive_fixed_amount" onChange="livePriceAdmin.fillPrice()" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<!-- Markdown -->
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Fixed Markdown Percentage: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="any" value="<?php echo $_goldpricelive_markdown; ?>" class="full" id="_goldpricelive_markdown" name="_goldpricelive_markdown" onChange="livePriceAdmin.fillPrice()" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Fixed Markdown: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="number" step="any" class="full" value="<?php echo $_goldpricelive_fixed_markdown; ?>" id="_goldpricelive_fixed_markdown" name="_goldpricelive_fixed_markdown" onChange="livePriceAdmin.fillPrice()" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<!-- Markdown -->
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Total of Precious Metal Adjustement: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="text" class="full" value="<?php echo $_goldpricelive_adjustement; ?>" id="_goldpricelive_adjustement" name="_goldpricelive_adjustement" />
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label"><?php echo __('Total Product Price with Metal Adjustement: ','GLP'); ?></label>
			<div class="clear"></div>
			<div class="glp_field">
				<input type="text" class="full" value="<?php echo $_goldpricelive_price; ?>" id="_goldpricelive_price" name="_goldpricelive_price" /><input type="hidden" name="_goldpricelive_price_num" id="_goldpricelive_price_num" value="">
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="glp_row metabox">
			<label class="glp_label">
				<input type="checkbox" name="_goldpricelive_exclude_discount" value="<?php $$_goldpricelive_exclude_discount; ?>" <?php echo ($_goldpricelive_exclude_discount=='1')?" checked='checked' ":""; ?> />
				<?php echo __('Exclude Quantity Discount: ','GLP'); ?>
			</label>
			<div class="clear"></div>
		</div>
		<p id="spot-price"></p>
	<?php

}
add_action( 'add_meta_boxes', 'add_wpdocs_meta_box' );
function save_wpdocs_meta_box($post_id){
	if(isset($_POST['_goldpricelive_metal'])){
		update_post_meta($post_id,'_goldpricelive_metal', $_POST['_goldpricelive_metal']);

		update_post_meta($post_id, '_goldpricelive_weight', $_POST['_goldpricelive_weight'] );

		update_post_meta($post_id, '_goldpricelive_units', $_POST['_goldpricelive_units'] );

		update_post_meta($post_id, '_goldpricelive_purity', $_POST['_goldpricelive_purity'] );

		update_post_meta($post_id, '_goldpricelive_markup', $_POST['_goldpricelive_markup'] );

		update_post_meta($post_id, '_goldpricelive_fixed_amount', $_POST['_goldpricelive_fixed_amount'] );

		update_post_meta($post_id, '_goldpricelive_purity', $_POST['_goldpricelive_purity'] );

		update_post_meta($post_id, '_goldpricelive_price', $_POST['_goldpricelive_price'] );

		update_post_meta($post_id, '_goldpricelive_price_num', $_POST['_goldpricelive_price_num'] );
		/* Markdown */
		update_post_meta($post_id, '_goldpricelive_markdown', $_POST['_goldpricelive_markdown'] );

		update_post_meta($post_id, '_goldpricelive_fixed_markdown', $_POST['_goldpricelive_fixed_markdown'] );
		/* Markdown end */
		
		if(isset($_POST['_goldpricelive_exclude_discount'])){
			$_goldpricelive_exclude_discount='1';
		}else{
			$_goldpricelive_exclude_discount='0';
		}
		update_post_meta($post_id, '_goldpricelive_exclude_discount', $_goldpricelive_exclude_discount);
		
	}
}
add_action('save_post', 'save_wpdocs_meta_box');
add_filter('the_title', 'to_sale_page_title', 10, 2);
function to_sale_page_title($title, $id) {
	if( ( is_product() && in_the_loop() && has_term( 'sell-to-us', 'product_cat' )) ) {
		//Logic for changing the WooCommerce Product Title on a Single Product page goes here
		return $title ."<a class='call-now' href='".get_site_url()."/contact-us/' style='font-size:18px!important;font-weight:normal!important;text-transform:initial!important;'>Call now for detail</a>";
	}
	//Return the normal Title if conditions aren't met
	return $title;
}
/// SHOP PAGE *
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' );
function custom_pre_get_posts_query( $q ) {
	if( is_shop()){
		$q->set( 'tax_query', array(array(
		   'taxonomy' => 'product_cat',
		   'field' => 'slug',
		   'terms' => array( 'sell-to-us' ), 
		   'operator' => 'NOT IN'
		)));
	}

}
/// SHOP SIDEBAR *
add_filter( 'woocommerce_products_widget_query_args', 'wpsites_exclude_product_cat_widget' );
function wpsites_exclude_product_cat_widget( $args ) {
	$args['tax_query'] =  array(array(
			   'taxonomy' => 'product_cat',
			   'field' => 'slug',
			   'terms' => array( 'sell-to-us' ), 
			   'operator' => 'NOT IN'
	));

	return $args;
}

/////// To Remove From HOME NEW section
add_action( 'woocommerce_shortcode_products_query', 'custom_pre_get_posts_query_' );
function custom_pre_get_posts_query_($args ) {
	if(is_home() || is_front_page()){
		$args['tax_query'] =  array(array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => array( 'sell-to-us' ), 
			'operator' => 'NOT IN'
		));
	}
return $args;
}
////////////////////////




add_filter( 'woocommerce_locate_template', 'glp_woocommerce_locate_template', 10, 3 );
function glp_woocommerce_locate_template( $template, $template_name, $template_path ) {
 
  global $woocommerce;
 
 
 
  $_template = $template;
 
  if ( ! $template_path ) $template_path = $woocommerce->template_url;
 
  $plugin_path  = GLP__PLUGIN_DIR . 'woocommerce/';
 
 
 
  // Look within passed path within the theme - this is priority
 
  $template = locate_template(
 
    array(
 
      $template_path . $template_name,
 
      $template_name
 
    )
 
  );
 
 
  // Modification: Get the template from this plugin, if it exists
  if ( ! $template && file_exists( $plugin_path . $template_name ) )
 
    $template = $plugin_path . $template_name;
 
 
 
  // Use default template
 
  if ( ! $template )
 
    $template = $_template;
 
 
 
  // Return what we found
 
  return $template;
 
}
class Glp_Widgets {
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'load' ), 9 );
		add_action( 'widgets_init', array( $this, 'init' ), 10 );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	public function load() {
		include_once( GLP__PLUGIN_DIR . 'widgets/glp-price-table-widget.php' );
	}

	public function init() {
		if ( ! is_blog_installed() ) {
			return;
		}

		register_widget( 'WP_Price_List_Table' );
	}

	public function uninstall() {}
}

$custom_post_type_widgets = new Glp_Widgets();
function get_imgs(){
	ob_start();
	?>
	<img src="<?php echo GLP__PLUGIN_URL; ?>images/loading.gif" width="20" height="20" />
	<?php
	$o=ob_get_contents(); 
	ob_get_clean();
	return $o;
}
add_shortcode("Loading",function(){
return 	get_imgs();
});
function _toSerialArray($array=array(),$e=0,$_data=array()){
	$e=$e;
	$array=$array;
	$_data=$_data;
	foreach($array as $_a){
		if($_a->parent==$e){
			$_data[]=$_a->term_id;
			return _toSerialArray($array,$_a->term_id,$_data);
		}
	}
	return array_reverse($_data);
}
/* Hook for product categories */
function field_create_taxonomy_qty_discount() {
    ?>
    <div class="form-field">
        <label for="wh_meta_title"><?php _e('Quantity Discount'); ?></label>
        			<?php /* Qty Discount for Gold: Start */ ?>
                <div class="glp_field">
					<div id="date_table" class="table-editable add" style="width:432px;">
						<span class="table-add dashicons dashicons-plus"></span>
						<table class="table" style="border:1px solid #c3c3c3; border-radius:5px;float:left;">
							<tr>

								<th>Min</th>
								<th>Max</th>
								<th>Qty</th>
								<th style="width:70px;"></th>
							</tr>
							<tr class="hide">
								<td>
									<input type="text" name="min_gold[]" class=""/>
								</td>
								<td>
									<input type="text" name="max_gold[]" class=""/>
								</td>
								<td>
									<input type="text" name="amount_gold[]" class=""/>
								</td>
								<td>
									<span class="table-remove dashicons dashicons-no-alt"></span>
									<span class="table-up dashicons dashicons-arrow-up-alt2"></span>
									<span class="table-down dashicons dashicons-arrow-down-alt2"></span>
								</td>
							</tr>
						</table>
					</div>
               
               
               
                </div>
                <div class="clear"></div>
			<?php /* Qty Discount for Gold: End */ ?>
    </div>
    
    <?php
}

//Product Cat Edit page
function field_edit_taxonomy_qty_discount($term) {
    //getting term ID
    $term_id = $term->term_id;
    // retrieve the existing value(s) for this meta field.
    $glp_qty_discounts = get_term_meta($term_id, 'quantity_discounts', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="wh_meta_title"><?php _e('Quantity Discount'); ?></label></th>
        <td>
            			<?php /* Qty Discount for Gold: Start */ ?>
            <div class="glp_row">
                <div class="glp_field">
					<div id="date_table" class="table-editable" style="width:600px;">
						<span class="table-add dashicons dashicons-plus" style="top: 20px;"></span>
						<table class="table" style="border:1px solid #c3c3c3; border-radius:5px;float:left;">
							<tr>

								<th>Min</th>
								<th>Max</th>
								<th>Qty</th>
								<th style="width:70px;"></th>
							</tr>
							<?php
							$json = json_decode( $glp_qty_discounts ); 
							if ( is_array( $json ) ) {
								foreach ( $json as $val ) {
									?>
							<tr>

								<td>
									<input type="text" name="min_gold[]" class="" id="input<?php echo $val->min; ?>" value="<?php echo $val->min ?>"/>
								</td>
								<td>
									<input type="text" name="max_gold[]" class="" id="input<?php echo $val->max; ?>" value="<?php echo $val->max; ?>"/>
								</td>
								<td>
									<input type="text" name="amount_gold[]" class="" id="input<?php echo $val->amount; ?>" value="<?php echo $val->amount; ?>"/>
								</td>
								<td>
									<span class="table-remove dashicons dashicons-no-alt"></span>
									<span class="table-up dashicons dashicons-arrow-up-alt2"></span>
									<span class="table-down dashicons dashicons-arrow-down-alt2"></span>
								</td>
							</tr>
							<?php
							}
							}
							?>
							<tr class="hide">
								<td>
									<input type="text" name="min_gold[]" class=""/>
								</td>
								<td>
									<input type="text" name="max_gold[]" class=""/>
								</td>
								<td>
									<input type="text" name="amount_gold[]" class=""/>
								</td>
								<td>
									<span class="table-remove dashicons dashicons-no-alt"></span>
									<span class="table-up dashicons dashicons-arrow-up-alt2"></span>
									<span class="table-down dashicons dashicons-arrow-down-alt2"></span>
								</td>
							</tr>
						</table>
					</div>
               
               
               
                </div>
                <div class="clear"></div>
            </div>
			<?php /* Qty Discount for Gold: End */ ?>
        </td>
    </tr>
    <?php
}

add_action('product_cat_add_form_fields', 'field_create_taxonomy_qty_discount', 10, 1);
add_action('product_cat_edit_form_fields', 'field_edit_taxonomy_qty_discount', 10, 1);

// Save extra taxonomy fields callback function.
function save_taxonomy_qty_discount($term_id) {
	$dis_data=array();
	if(isset($_POST['min_gold'])){
		if(is_array($_POST['min_gold'])){
			foreach($_POST['min_gold'] as $_key=>$_data){
				if(!empty($_data) && !empty(floatval($_POST['max_gold'][$_key])) && !empty(floatval($_POST['amount_gold'][$_key]))){
				$dis_data[]=array("min"=>floatval($_data),"max"=>floatval($_POST['max_gold'][$_key]),"amount"=>floatval($_POST['amount_gold'][$_key]));
				}
			}
		}
	}
	if(sizeof($dis_data)>0){
		update_term_meta($term_id,'quantity_discounts',json_encode($dis_data));
	}else{
		update_term_meta($term_id,'quantity_discounts','');
	}
}

add_action('edited_product_cat', 'save_taxonomy_qty_discount', 10, 1);
add_action('create_product_cat', 'save_taxonomy_qty_discount', 10, 1);
?>