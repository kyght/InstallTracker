jQuery(document).ready(
	function(){
		jQuery("#submit").click(
				function(){
	        var id = jQuery("#id").val();
					var name = jQuery("#name").val();
    			var address = jQuery("#address").val();
    			var city = jQuery("#city").val();
    			var state = jQuery("#state").val();
    			var contact = jQuery("#contact").val();
    			var email = jQuery("#email").val();
    			var phone = jQuery("#phone").val();
    			var product = jQuery("#product").val();
    			var version = jQuery("#version").val();
    			var custom = jQuery("#custom").val();
    			var zipcode = jQuery("#zipcode").val();
    			
    			var action = jQuery("#action").val();
						jQuery.ajax({
							type: 'POST',
							url: KyITrack.ajaxurl,
							data: {"action": action, "id":id, "name":name, "address":address, "city":city, "state":state, "contact":contact, "email":email, "phone":phone, "product":product, "version":version, "custom":custom, "zipcode":zipcode},
							success: function(data){
									alert(data.msg);
									window.location.href = 'admin.php?page=kytracker_admin_reg_view&id='+id;
									}
				});
	});
});
