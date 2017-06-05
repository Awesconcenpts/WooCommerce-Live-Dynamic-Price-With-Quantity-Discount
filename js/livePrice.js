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
var livePrice = {
	liveTimer: null,
	liveInterval: glp.interval,
	liveArgs: {products:[]},
	ajaxUrl: glp.ajax,
	storageData:[],
	initPrice: function () {
		"use strict";
		
		
		livePrice.getObject();
		livePrice.initDom();
	},
	initDom:function(){
		"use strict";
		jQuery(document).on("click",".tablist a",function(){
			jQuery(".glp_content,.tablist a").removeClass('active');
			jQuery("#"+jQuery(this).data('target')).addClass('active');
			jQuery(this).addClass('active');
		});
		jQuery(document).on("click",".decrease",function(){
			var exis=jQuery(this).closest(".quantity").find("input.qty");
			if(parseInt(exis.val())>1){
				var _inc=parseInt(exis.val())-1;
				exis.val(_inc); livePrice.addQtyDiscount(true);
			}
		});
		jQuery(document).on("click",".increase",function(){
			var exis=jQuery(this).closest(".quantity").find("input.qty");
				var _inc=parseInt(exis.val())+1;
				exis.val(_inc); livePrice.addQtyDiscount(true);
			
		});

	},
	addQtyDiscount:function(e){
		"use strict";
		
		if(jQuery(".summary.entry-summary").length>0){
			var __id=parseInt(jQuery(".summary.entry-summary .amount").data("pid"));
			if(!livePrice.storageData.length){
				var __storage=localStorage.getItem('livePrice');
				livePrice.storageData=JSON.parse(__storage);
			}
			var _getObj=livePrice.storageData.filter(function(e){return e.pi==__id; });
			if(livePrice.storageData.length && __id>0){
				if(_getObj.length){
					if(_getObj[0].tx>0){
						livePrice.withTax(e,__id,_getObj[0]);
					}else{
						livePrice.nonTax(e,__id,_getObj[0]);
					}
				}
			}else{
				jQuery(".summary.entry-summary .amount")
					.html(livePrice.getPriceHtml(_getObj[0],true))
					.closest(".price")
					.addClass('updated');
				setTimeout(function(){ 
					jQuery('.summary.entry-summary .price').removeClass('updated'); 
				},500);
			}
		}
	},
	nonTax:function(e,__id,_getObj){
				var _y=false;
				var __actual_price=_getObj.pr;
				var _discount=0;
				var __selling_price=0;
				var __cart_price=0;
				var e_=parseInt(jQuery(".summary.entry-summary").find("input.qty").val());
				jQuery(".tier-pricing tbody tr").removeClass("high-light");
				jQuery.each(jQuery(".tier-pricing tbody tr").get().reverse(),function(){ 
					var _min=parseInt(jQuery(this).data("min"));
					var _max=parseInt(jQuery(this).data("max"));
					if((e_>=_min && e_<=_max) && _y==false){
						_y=true;
						jQuery(this).addClass("high-light");
						_discount=parseFloat(jQuery(this).data("amount"));
						__selling_price = __actual_price - (__actual_price * (_discount / 100));
						__cart_price = __selling_price*e_;

					}
				});
				if(!_y){
					__selling_price = __actual_price - (__actual_price * (_discount / 100));
					__cart_price = __selling_price*e_;
				}
				jQuery(".summary.entry-summary .amount").html(__cart_price.formatMoney()).closest(".price").addClass('updated');
				jQuery("#prod_price").val(__selling_price.toFixed(2));
				setTimeout(function(){ jQuery('.summary.entry-summary .price').removeClass('updated'); }, (500));
				/* Lets set discounted prices */
				jQuery.each(jQuery(".tier-pricing tbody tr"),function(){
					var _min=parseInt(jQuery(this).data("min"));
					var _discount=parseFloat(jQuery(this).data("amount"));
					var __selling_price = __actual_price - (__actual_price * (_discount / 100));
					var __start_price = __selling_price*_min;
					var __ob1=jQuery(this).find("td:nth-child(2)>span");
					var __ob2=jQuery(this).find("td:nth-child(3)>span");
					__ob1.html((__selling_price.formatMoney()));
					__ob2.html((__start_price.formatMoney()));
					if(!e){
					__ob1.addClass('updated');
					__ob2.addClass('updated');
					}
				});
				if(!e){
				setTimeout(function(){ jQuery('td>span.updated').removeClass('updated'); }, (1000));
				}
	},
	withTax:function(e,__id,_getObj){
		var _y=false;
		var __actual_price=_getObj.pr;
		var __actual_price_tax=_getObj.pr+_getObj.tm;
		var __selling_price=0;
		var __selling_price_tax=0;
		var __cart_price=0;
		var __cart_price_tax=0;
		var _discount=0
		var e_=parseInt(jQuery(".summary.entry-summary").find("input.qty").val());
		jQuery(".tier-pricing tbody tr").removeClass("high-light");
		jQuery.each(jQuery(".tier-pricing tbody tr").get().reverse(),function(){ 
			var _min=parseInt(jQuery(this).data("min"));
			var _max=parseInt(jQuery(this).data("max"));
			if((e_>=_min && e_<=_max) && _y==false){
				_y=true;
				jQuery(this).addClass("high-light");
				_discount=parseFloat(jQuery(this).data("amount"));
				__selling_price_tax = __actual_price_tax - (__actual_price_tax * (_discount / 100));
				__cart_price_tax = __selling_price_tax*e_;
				
				// None tax
				__selling_price = __actual_price - (__actual_price * (_discount / 100));
				__cart_price = __selling_price*e_;
			}
		});
		if(!_y){
			__selling_price_tax = __actual_price_tax - (__actual_price_tax * (_discount / 100));
			__cart_price_tax = __selling_price_tax*e_;
			
			// None tax
			__selling_price = __actual_price - (__actual_price * (_discount / 100));
			__cart_price = __selling_price*e_;
		}
		var _price_text=__cart_price_tax.toFixed(2)+" (inc. VAT) - <span class='excl-vat'>"+glp.symbol+" "+__cart_price.toFixed(2)+" (excl. VAT)</span>";
		jQuery(".summary.entry-summary .amount").html(_price_text).closest(".price").addClass('updated');
		jQuery("#prod_price").val(__cart_price_tax.toFixed(2));
		setTimeout(function(){ jQuery('.summary.entry-summary .price').removeClass('updated'); }, (500));
				/* Lets set discounted prices */
				jQuery.each(jQuery(".tier-pricing tbody tr"),function(){
					var _min=parseInt(jQuery(this).data("min"));
					var _discount=parseFloat(jQuery(this).data("amount"));
					var __selling_price = __actual_price - (__actual_price * (_discount / 100));
					var __start_price = __selling_price*_min;
					//with tax
					var __selling_price_tax = __actual_price_tax - (__actual_price_tax * (_discount / 100));
					var __start_price_tax = __selling_price_tax*_min;
					
					var __ob1=jQuery(this).find("td:nth-child(2)>span");
					var __ob2=jQuery(this).find("td:nth-child(3)>span");
					var __ob3=jQuery(this).find("td:nth-child(4)>span");
					__ob1.html(__selling_price.formatMoney());
					__ob2.html((__selling_price_tax).formatMoney());
					__ob3.html((__start_price_tax.formatMoney()));
					if(!e){
						__ob1.addClass('updated');
						__ob2.addClass('updated');
						__ob3.addClass('updated');
					}
				});
				if(e){
				setTimeout(function(){ jQuery('td>span.updated').removeClass('updated'); }, (1000));
				}
	},
	getObject: function () {
		"use strict";
		jQuery.each(jQuery("[data-dyn]"), function () {	
			livePrice.liveArgs.products.push({
				w:jQuery(this).data('weight'),
				u:jQuery(this).data('units'),
				m:jQuery(this).data('metal'),
				p:jQuery(this).data('pid'),
				c:jQuery(this).data('currency')
			});
		});
		livePrice.getPrice();

	},
	getPrice: function () {
		"use strict";
		var urls={};
		urls.action='live_price';
		urls.p=livePrice.liveArgs.products;
		jQuery.getJSON(livePrice.ajaxUrl, urls, function (data) {
			localStorage.setItem('livePrice', JSON.stringify(data));
			livePrice.storageData=data;
			livePrice.setPriceProducts(data);
			clearTimeout(livePrice.liveTimer);
			livePrice.liveTimer = setTimeout(function(){ livePrice.getPrice(); }, (livePrice.liveInterval*60000));
		});
	},
	setPriceProducts:function(e){
		"use strict";
		
		jQuery.each(e,function(i,r){
			
			
			var _obj=jQuery('[data-pid="' + r.pi + '"][data-weight="'+r.we+'"][data-units="'+r.un+'"][data-metal="'+r.me+'"][data-currency="'+r.cu+'"]');
			jQuery.each(_obj,function(){
				var _showVat=false;
				if(jQuery(this).parent().parent().hasClass('entry-summary')){
					_showVat=true; 
				}else{
					_showVat=false; 
				}	jQuery(this).html(livePrice.getPriceHtml(r,_showVat)).closest('.price').removeClass('_loading').addClass('updated');
			});
			
		});
		setTimeout(function(){ jQuery('.price').removeClass('updated'); }, (1500));
		livePrice.addQtyDiscount();
	},
	getPriceHtml:function(_getObj,_showVat){
		"use strict";
		var e_=parseInt(jQuery(".summary.entry-summary").find("input.qty").val());
		if(!e_)e_=1;
		var __actual_price=_getObj.pr;
		var __cart_price = __actual_price*e_;
		var __actual_price_tax=__actual_price+_getObj.tm;
		var __cart_price_tax = __actual_price_tax*e_;
		if(_getObj.tm && _showVat){
			return __cart_price_tax.formatMoney()+" (inc. VAT) - <span class='excl-vat'>"+glp.symbol+" "+__cart_price.formatMoney()+" (excl. VAT)</span>";
		}
		return __cart_price.formatMoney();
	},
	getRound:function(value, precision, mode){
		  var m, f, isHalf, sgn // helper variables
		  // making sure precision is integer
		  precision |= 0
		  m = Math.pow(10, precision)
		  value *= m
		  // sign of the number
		  sgn = (value > 0) | -(value < 0)
		  isHalf = value % 1 === 0.5 * sgn
		  f = Math.floor(value)
		  if (isHalf) {
			switch (mode) {
			  case 'PHP_ROUND_HALF_DOWN':
			  // rounds .5 toward zero
				value = f + (sgn < 0)
				break
			  case 'PHP_ROUND_HALF_EVEN':
			  // rouds .5 towards the next even integer
				value = f + (f % 2 * sgn)
				break
			  case 'PHP_ROUND_HALF_ODD':
			  // rounds .5 towards the next odd integer
				value = f + !(f % 2)
				break
			  default:
			  // rounds .5 away from zero
				value = f + (sgn > 0)
			}
		  }
		  return (isHalf ? value : Math.round(value)) / m;
	}
};
jQuery(function () {
	"use strict";
	jQuery(document).ready(function(){
		if(glp.interval){
			livePrice.initPrice();
		}
	});
});
