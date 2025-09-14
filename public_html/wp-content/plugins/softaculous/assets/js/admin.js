(function($, nonce, admin_url, ajax_url){
$(document).ready(function(){

	$(document).on("click", ".my-soft-dismiss-notice .notice-dismiss", function(){
		var data = {
			action: "softaculous_dismissnotice",
			softaculous_security: nonce
		};
		$.post(ajax_url, data, function(response){});
	});

	//View connection key script
	var soft_conn_key_dialog = $("#soft_connection_key_dialog");
	$("#soft_connection_key").click(function(e) {
		e.preventDefault();
		soft_conn_key_dialog.dialog({
			draggable: false,
			resizable: false,
			modal: true,
			width: "1070px",
			height: "auto",
			title: "Softaculous Connection Key",
			close: function() {
				$(this).dialog("destroy");
			}
		});
	});

	$("#soft_promo .soft_promo-close").click(function(){
		var data = {
			softaculous_security: nonce
		};

		// Hide it
		$("#soft_promo").hide();
		
		// Save this preference
		$.post(admin_url + '?softaculous_promo=0', data, function(response) {
			//alert(response);
		});
	});

	function dotweet(ele){
		window.open($("#"+ele.id).attr("action")+"?"+$("#"+ele.id).serialize(), "_blank", "scrollbars=no, menubar=no, height=400, width=500, resizable=yes, toolbar=no, status=no");
		return false;
	}
});
})(jQuery, soft_obj.nonce, soft_obj.admin_url, soft_obj.ajax_url);