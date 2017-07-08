<?php
if(isset($_REQUEST['glp_interval'])){
update_option('glp_interval',$_REQUEST['glp_interval']);
update_option('glp_refresh',$_REQUEST['glp_refresh']);
update_option('glp_feed_url',$_REQUEST['glp_feed_url']);
update_option('glp_contact_more_qty',$_REQUEST['glp_contact_more_qty']);
}
if(isset($_POST['min_gold'])){
	if(is_array($_POST['min_gold'])){
	$dis_data=array();
	foreach($_POST['min_gold'] as $_key=>$_data){
		if(!empty($_data) && !empty(floatval($_POST['max_gold'][$_key])) && !empty(floatval($_POST['amount_gold'][$_key]))){
		$dis_data[]=array("min"=>floatval($_data),"max"=>floatval($_POST['max_gold'][$_key]),"amount"=>floatval($_POST['amount_gold'][$_key]));
		}
	}
	update_option('glp_qty_discounts_gold',json_encode($dis_data));
	}else{
		delete_option('glp_qty_discounts_gold');
	}
}

if(isset($_POST['min_silver'])){
	if(is_array($_POST['min_silver'])){
		$dis_data=array();
		foreach($_POST['min_silver'] as $_key=>$_data){
			if(!empty($_data) && !empty(floatval($_POST['max_silver'][$_key])) && !empty(floatval($_POST['amount_silver'][$_key]))){
			$dis_data[]=array("min"=>floatval($_data),"max"=>floatval($_POST['max_silver'][$_key]),"amount"=>floatval($_POST['amount_silver'][$_key]));
			}
		}
		update_option('glp_qty_discounts_silver',json_encode($dis_data));
	}else{
		delete_option('glp_qty_discounts_silver');
	}
	
}
$glp_refresh=get_option('glp_refresh');
$glp_interval=get_option('glp_interval');
$glp_feed_url=get_option('glp_feed_url');
$glp_contact_more_qty=get_option('glp_contact_more_qty');
?>
<div class="wrap cstm-tble">

	<h2><?php echo __('Awesome Live Price','GLP'); ?></h2><br>

   <form action="" method="post" name="form-targetted" id="form-targetted">
    <div class="glp_holder" role="tablist">
        <h3><?php echo __('General Configuration','GLP'); ?></h3>
		
        <div class="glpoptions">
			
			<?php /* Enable Auto Refresh Style: Start */ ?>
            <div class="glp_row">
                <label class="glp_label"><?php echo __('Enable Auto refresh:','GLP'); ?></label>
                <div class="glp_field">
                    <label><input type="radio" name="glp_refresh" value="1" <?php if($glp_refresh=='1'){ ?>  checked="checked" <?php } ?>></inpu>Yes</label>
					<label><input type="radio" name="glp_refresh" value="0"  <?php if($glp_refresh=='0'){ ?> checked="checked" <?php } ?>>No</label>
                    <div class="clear"></div>
                    <div class="nfos"><?php echo __('Select yes to enable auto refresh.','GLP'); ?></div>
                    <div class="clear"></div>
                </div>

                <div class="clear"></div>
            </div>
			<?php /* Enable Auto Refresh Style: End */ ?>
			<?php /* Auto Refresh Speed Options: Start */ ?>
            <div class="glp_row">
               <label class="glp_label"><?php echo __('Refresh Interval:','GLP'); ?></label>
                <div class="glp_field">
                        <input type="number" step="0.01" name="glp_interval" id="glp_interval" placeholder="0.25, 0.50, 0.75, 1, 2" value="<?php echo $glp_interval; ?>"  />
                        <div class="clear"></div>
                    		<div class="nfos"><?php echo __('Time in minutes eg: 0.25 is 15 seconds,0.50 is 30 seconds and 1 is one minute.','GLP'); ?></div>
                    	<div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
			<?php /* Auto Refresh Speed Options:: End */ ?>
			
			<?php /* Auto Refresh Speed Options: Start */ ?>
            <div class="glp_row">
               <label class="glp_label"><?php echo __('Contact No:','GLP'); ?></label>
                <div class="glp_field">
                        <input type="text" name="glp_contact_more_qty" id="glp_contact_more_qty" placeholder="Eg: 9851189071" value="<?php echo $glp_contact_more_qty; ?>"  />
                        <div class="clear"></div>
                    		<div class="nfos"><?php echo __('Contact no for larger quantities.','GLP'); ?></div>
                    	<div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
			<?php /* Auto Refresh Speed Options:: End */ ?>
			<?php /* Auto Refresh Speed Options: Start */ ?>
            <div class="glp_row">
               <label class="glp_label"><?php echo __('Feed Url:','GLP'); ?></label>
                <div class="glp_field">
                      <label><input type="radio" name="glp_feed_url" value="xmlcharts" <?php if($glp_feed_url=='xmlcharts'){ ?>  checked="checked" <?php } ?>></inpu>Xml Charts</label>
					<label style="display: none;"><input type="radio" name="glp_feed_url" value="0"  <?php if($glp_feed_url=='0'){ ?> checked="checked" <?php } ?>>No</label>
                    <div class="clear"></div>
                    <div class="nfos"><?php echo __('Choose a API to update price','GLP'); ?></div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
			<?php /* Auto Refresh Speed Options:: End */ ?>
			<?php /* Available shortcode: Start */ ?>
            <div class="glp_row">
               <label class="glp_label"><?php echo __('Available Shortcodes:','GLP'); ?></label>
                <div class="glp_field">
                      <strong>To get price</strong><br>
                      [glp id="PRODICT ID"] <br>or<br> [glp metal="gold|silver" units="gram|toz|tola" weight="WEIGHT" currency="usd|gbp|eur"]
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
			<?php /* Available shortcode: End */ ?>
			<?php /* Choose Position for tab */ ?>
            <?php /*?><div class="glp_row" id="display_position" style="<?php echo (get_option('spritz_flyout_display_type')=='tab' || get_option('spritz_flyout_display_type')=='')?'display: block':'display: none'; ?> ">
                <label class="glp_label"><?php echo __('Choose Tab Button Position','GLP'); ?></label>
                <div class="glp_field">
                    <select class="select" name="popup_position">
                        <option value="left" <?php echo (get_option('popup_position')=='left')?'selected="selected"':''; ?>><?php echo __('Left','GLP'); ?></option>
                        <option value="right" <?php echo (get_option('popup_position')=='right')?'selected="selected"':''; ?>><?php echo __('Right','GLP'); ?></option>
                        <option value="top" <?php echo (get_option('popup_position')=='top')?'selected="selected"':''; ?>><?php echo __('Top','GLP'); ?></option>
                        
                    </select>

                    <div class="nfos"><?php echo __('Select position of the tab button on the screen.','GLP'); ?></div>

                    <div class="clear"></div>
                </div>

                <div class="clear"></div>
            </div><?php */?>
			<?php /* Choose Position: End */ ?>

            
			<?php /* Notice: Start */ ?>
            <div class="glp_row">
                <label class="glp_label">
                    <?php wp_nonce_field (plugin_basename (__FILE__), 'ps_nonce'); ?>
                    <input type="hidden" name="isconfic" value="isconfic" />
                </label>
                <div class="glp_field">
                    <input type="submit" value="<?php echo __('Save Changes','GLP'); ?>" class="button button-primary submit-targetted" id="submit-targetted" name="submit-targetted" />
                    <span class="glp-status"></span>

                    <div class="clear"></div>
                </div>

                <div class="clear"></div>
            </div>
			<?php /* Notice: End */ ?>
			
            <div class="clear"></div>
        </div>

        <div class="clear"></div>
    </div>
</form>
</div>
<!-- -->