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
	
	$("#btn_reset").live("click", function(){
		if($("#newpass").val() == "" || $("#confirmpass").val() == "") return;
		if($("#newpass").val() != $("#confirmpass").val()){
			$("#error").show();
			$("#error").html("パスワードが一致しません。");
			return;
		}
		var params = {
			"newpass"		: CybozuLabs.MD5.calc($("#newpass").val()),
			"reset_code"	: $("#code").val()
		};
		$("#error").hide();
		$.ajax({
			type: "POST",
			url: "/ajax/resetpassword/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					new AlertBox().show("パスワードをリセットしました。");
					setTimeout(function(){
						window.location = "/";
					}, 1000);
				} else {
					$("#error").show();
					$("#error").html("パスワードが再入力されたパスワードと異なります。");
				}
			}
		});
	});
	
});
