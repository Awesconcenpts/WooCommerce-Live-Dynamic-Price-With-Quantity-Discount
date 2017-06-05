Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};
var livePriceAdmin = {
	ajaxUrl: glp.ajax,
	currency:glp.currency,
	feedPrice:glp.feed,
	initPrice: function () {
		"use strict";
		
		livePriceAdmin.fillPrice();
		
	},
	initDoms:function(){
		var $TABLE1 = jQuery('#date_table');
		jQuery('.table-add').click(function () {
			var $clone = $TABLE1.find('tr.hide').clone(true).removeClass('hide table-line');
			$clone.find('input').addClass("picker");
			$TABLE1.find('table').append($clone);   
		});
		jQuery('.table-remove').click(function () {
		  jQuery(this).closest('tr').detach();
		});
		jQuery('.table-up').click(function () {
		  var $row = jQuery(this).parents('tr');
		  if ($row.index() === 1) return;
		  $row.prev().before($row.get(0));
		});
		jQuery('.table-down').click(function () {
		  var $row = jQuery(this).parents('tr');
		  $row.next().after($row.get(0));
		});
		var $TABLE2 = jQuery('#date_table1');
		jQuery('.table-add1').click(function () {
			var $clone = $TABLE2.find('tr.hide').clone(true).removeClass('hide table-line');
			$clone.find('input').addClass("picker");
			$TABLE2.find('table').append($clone);   
		});
		jQuery('.table-remove1').click(function () {
		  jQuery(this).parents('tr').detach();
		});
		jQuery('.table-up1').click(function () {
		  var $row = jQuery(this).parents('tr');
		  if ($row.index() === 1) return;
		  $row.prev().before($row.get(0));
		});
		jQuery('.table-down1').click(function () {
		  var $row = jQuery(this).parents('tr');
		  $row.next().after($row.get(0));
		});
		jQuery.fn.pop = [].pop;
		jQuery.fn.shift = [].shift;
		
	},
	inCurrentUnit:function(price){
		var goldpricelive_units = jQuery("#_goldpricelive_units").val() || 'gram';
		if(goldpricelive_units == 'toz') {
			return price*31.1034768;
		} else if(goldpricelive_units == 'gram') {
			return price;
		}
	},
	loadFeed:function(){
		/*jQuery.post(livePriceAdmin.ajaxUrl,
                        { 'action': 'live_feed' },
                        function(response){
                            var json = JSON.parse(response);
                            if(json && !json.error) {
                                livePriceAdmin.feedPrice = {
                                    gold: json.ask['gold'],
                                    silver: json.ask['silver'],
                                    platinum: json.ask['platinum'],
                                    palladium: json.ask['palladium']
								};
								set
                                livePriceAdmin.fillPrice();
                            } else {
                                livePriceAdmin.feedPrice = false;
                            }
                        }
                    );
					*/
	},
	fillPrice:function(){
		"use strict";
		/* Start Price */
		var fixed_amount = parseFloat(jQuery("#_goldpricelive_fixed_amount").val()) || 0;
		var metal = jQuery("#_goldpricelive_metal").val(); 
		var weight = parseFloat(jQuery("#_goldpricelive_weight").val())|| 0;
		var units = jQuery("#_goldpricelive_units").val() || 'gram';

		var purity =parseFloat(jQuery("#_goldpricelive_purity").val());
		var markup = jQuery("#_goldpricelive_markup").val();
		
		var markdown = parseFloat(jQuery("#_goldpricelive_markdown").val()) || 0;
		var fixed_markdown = parseFloat(jQuery("#_goldpricelive_fixed_markdown").val()) || 0;
		var lowest = 0;
		//var buy_back = 0;
		var price = 0;
		var adjustement = 0; console.log(fixed_amount);
		if(livePriceAdmin.feedPrice && metal != "" && weight > 0 && units != "" && purity > 0 && markup >= 0) {
			var spot_price = livePriceAdmin.inCurrentUnit(livePriceAdmin.feedPrice[metal]);
			
			adjustement = parseFloat(spot_price * weight * purity* (1 + (markup / 100))) ;
			adjustement = (adjustement - ((adjustement/100)*markdown));
			price = parseFloat(spot_price * weight * purity * (1 + (markup / 100))) + parseFloat(fixed_amount);
			price = (price - ((price/100)*markdown))-fixed_markdown;
			
			if(price < lowest) {
				price = parseFloat(lowest);
			}
			jQuery("#_goldpricelive_adjustement").val(adjustement.toFixed(2)+' '+livePriceAdmin.currency.toUpperCase());
			jQuery("#_goldpricelive_price").val(price.toFixed(2)+' '+livePriceAdmin.currency.toUpperCase());
			jQuery("#_goldpricelive_price_num").val(price.toFixed(2));
			jQuery("#_regular_price").val(price.toFixed(2));
			/*if(buy_back > 0 && buy_back.length > 0 && !isNaN(buy_back)) {
				var buy_back_price = parseFloat(price-(price*(parseFloat(buy_back)/100)));
				document.getElementById("_goldpricelive_buy_back_price").value = buy_back_price.toFixed(2)+' '+goldpricelive_currency.toUpperCase();
				document.getElementById("_goldpricelive_buy_back_price_num").value = buy_back_price.toFixed(2);
			} else {
				document.getElementById("_goldpricelive_buy_back_price").value = "";
				document.getElementById("_goldpricelive_buy_back_price_num").value = "";
			}
			*/
			var str="Current Precious Material Market Price: "+spot_price.formatMoney()+" "+livePriceAdmin.currency.toUpperCase();
		jQuery("#spot-price").html(str);
		} else {
			jQuery("#_goldpricelive_adjustement").val("");
			jQuery("#_goldpricelive_price").val("");
			//jQuery("#_goldpricelive_buy_back_price").val("");
			jQuery("#_goldpricelive_price_num").val("");
			//jQuery("#_goldpricelive_buy_back_price_num").val("");
		}
		/* End Price */
	},
};
jQuery(function () {
	"use strict";
	jQuery(document).ready(function(){
		if(jQuery("#_goldpricelive_metal").length>0){
			livePriceAdmin.initPrice();
		}	
		livePriceAdmin.initDoms();
	});
});
