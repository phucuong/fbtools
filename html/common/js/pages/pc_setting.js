$(document).ready(function(){
	$("#btn_update_user_info").live('click',function(){
		document.form1.submit();
	});
	
	$("#btn_update_user_config").click(function(){
		var send_data = {
			'receive_message' 	: $("#chk_receive_message").is(':checked') ? 1 : 0,
			'receive_like'		: $("#chk_receive_like").is(':checked') ? 1 : 0,
			'receive_comment'	: $("#chk_receive_comment").is(':checked') ? 1 : 0
		};

		$.ajax({
			type: 'POST',
			url: '/setting/update_config',
			data: send_data,
			dataType: 'json',
			cache: false,
			success : function(data){
				var alertBox = new AlertBox();
				alertBox.show(data.message);
			}
		});
	});
	
	$("#btn_update_user_pass").click(function(){
		document.form1.submit();
	});
});