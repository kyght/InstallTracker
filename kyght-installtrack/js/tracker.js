jQuery(document).ready(
	function(){
		jQuery("#submit").click(
				function(){
					var id = jQuery("#id").val();
    			var prod = jQuery("#product").val();
    			var ver = jQuery("#version").val();
    			var vnum = jQuery("#vernum").val();
    			var cus = jQuery("#custom").val();
    			var ul = jQuery("#url").val();
    			var action = jQuery("#action").val();
    			var notesurl = jQuery("#notesurl").val();
						jQuery.ajax({
							type: 'POST',
							url: KyITrack.ajaxurl,
							data: {"action": action, "id":id, "product":prod, "version":ver, "vernum":vnum, "custom":cus, "url":ul, "notesurl":notesurl},
							success: function(data){
									alert(data.msg);
									if (data.valid == "TRUE") window.location.href = 'admin.php?page=kytracker_admin_upg';
									}
				});
	});
});
