$(document).ready(function(){
	$("#btnSubmit").click(function(){
		var values = new Array();
		var mes = $("#txt_message").val().trim();
		if(mes == ''){
			alert('Nhập message đi thím!');
			return;
		}
		var isChecked = $("#chkAll").is(':checked');
		if(isChecked){
			document.form1.submit();
			return;
		}
		var flag = 0;
		$("input[type='checkbox']:checked").each( function(val){
			values.push($(this).val());
			flag = 1;
		});
		if(flag == 0){
			alert('Không chọn group nào làm sao post bài!');
			return;
		}
		$("#hid_group").val(values);
		document.form1.submit();
	});
});
