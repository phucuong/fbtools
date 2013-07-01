/* --------------------------------------------------
**
** common.js
** version : 0.1
** datte : 2013/03/10
**
** -------------------------------------------------- */
/* --------------------------------------------------
** html5
** -------------------------------------------------- */
/*document.createElement('section');
document.createElement('nav');
document.createElement('article');
document.createElement('aside');
document.createElement('hgroup');
document.createElement('header');
document.createElement('footer');*/


/* --------------------------------------------------
** function
** -------------------------------------------------- */

$(document).ready(function() {
	$("a#user_account_touroku").fancybox({
		'titlePosition'	: 'inside'
	});
	
	$("a#forgotpassword").fancybox({
		'titlePosition'	: 'inside'
	});
	
	$("#file_input").live('change',function(){
		$("#file_input").upload(
			'/regist/upload_image',
			{},
			function(data){
				if(data.status == 1){
					$("#p_img").attr('src',data.image_url);
				}
				else{
					var alertBox = new AlertBox();
					alertBox.show(data.error);
				}
			},
			'json'
		);
	});
	
	$("#reset_btn").live("click", function(){
		var send_data = {
			'user_mail' 			: $("#user_mail").val()
		};
		$("#err_forgetpass").hide();
		$.ajax({
			type: 'POST',
			url : '/ajax/forgotpassword',
			data: send_data,
			dataType: 'json',
			cache: false,
			success: function(data){
				if(data.ok == 1){
					var alertBox = new AlertBox();
					alertBox.show("メールを送信しました。");
					$.fancybox.close();
				} else {
					var error = "";
					if(data.ok == 2){
						error = "このメールアドレスは未登録です。";
					} else {
						error = "エラーが発生しました。少し時間を置いて試してみてください。";
					}
					$("#err_forgetpass").html(error);
					$("#err_forgetpass").show();
				}
			}
		});
	});
	
	$("#user_account_touroku_btn").live('click',function(){
		var p1 = $("#user_account_password").val();
		var p2 = $("#user_account_password_kakunin").val();
		if(p1 != p2){
			$("#err_user_account_password_kakunin").html("パスワードが一致しません。");
			$("#err_user_account_password_kakunin").show();
			return;
		} else {
			$("#err_user_account_password_kakunin").hide();
		}
		var send_data = {
			'user_account_mail' 			: $("#user_account_mail").val(),
			'user_account_first_name'		: $("#user_account_first_name").val(),
			'user_account_last_name'		: $("#user_account_last_name").val(),
			'user_account_password' 		: CybozuLabs.MD5.calc($("#user_account_password").val()),
			'user_account_avatar' 			: $("#p_img").attr('src')
		};
		$.ajax({
			type: 'POST',
			url : '/ajaxregist',
			data: send_data,
			dataType: 'json',
			cache: false,
			success: function(data){
				if(data.status == 0){
					$.each(data.error,function( key,value){
						var obj_show_err = $("#err_"+key);
						if(value.error != ''){
							obj_show_err.removeClass('hide');
							obj_show_err.addClass('show');
							obj_show_err.text(value.error);
						}
						else{
							obj_show_err.addClass('hide');
						}
					});
				}
				else{
					$.fancybox.close();
					var alertBox = new AlertBox();
					alertBox.show(data.error);
					//location.href = data.url;
				}
			}
		});
	});
	
	$("#login_btn").live('click',function(){
		var send_data = {
			'login_id'		: $("#login_id").val(),
			'login_password': CybozuLabs.MD5.calc($("#login_password").val())
		};
		$(".error").hide();
		$.ajax({
			type	: 'POST',
			url		: '/ajaxlogin',
			data	: send_data,
			dataType: 'json',
			cache	: false,
			success: function(data){
				if(data.ok == 1){
					window.location = "/home";
				} else {
					$("#error" + data.ok).show();
				}
			}
		});
	});
});
