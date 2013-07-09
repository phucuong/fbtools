$(document).ready(function(){
	getPmList();
	
	$("#create").click(function(){
		var params = {
			"message" 		: $("#msg").val(),
			"pm_name" 		: $("#pm_name").val(),
			"user_list" 	: $("#msg_user_id").val(),
		};
		$.ajax({
			type: "POST",
			url: "/ajax/newpm/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					window.location = "/message/" + data.pm_id;
				}
			}
		});
	});
	
	$(".delete").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("deletemsg_","");
		var params = {
			"message_id" 	: id,
		};
		$.ajax({
			type: "POST",
			url: "/ajax/deletetimelinemessage/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					$("#timeline_list_" + id).remove();
				}
			}
		});
	});
	
	$("#msg_user_search").keyup(function(event){
		var str = $.trim($("#msg_user_search").val());
		if(str != ""){
			var params = {
				"name" 		: str,
				"not_in"	: $("#msg_user_id").val()
			};
			$.ajax({
				type: "POST",
				url: "/ajax/searchuser/",
				data: params,
				cache: false,
				dataType: "json",
				success: function(data){
					if(data.ok == 1){
						htmlCreate(data.data);
					}
				}
			});
		} else {
			$("#sugessUser").html("");
		}
	});
	
	$('.addPmUser').live("click",function(){
		var ID = $(this).attr("id").replace("addPmUser_","");
		var str = $("#msg_user_id").val();
		if(str == ""){
			$("#msg_user_id").val(ID);
		} else {
			$("#msg_user_id").val(str + "," + ID);
		}
		$("#user_list").append("<div class='addedPmUser' id='addedPmUser_" + ID + "'>" + $("#addPmUser_" + ID).html() + "<a class='removePmUser' id='removePmUser_" + ID + "' href='javascript:void(0)'><img src='/common/images/icon-delete.png' alt='削除する' width='13' height='13'></a></div>");
		$("#sugessUser").html("");
		$("#msg_user_search").val("");
	});
	
	$(".removePmUser").live("click", function(){
		var ID = $(this).attr("id").replace("removePmUser_","");
		$("#addedPmUser_" + ID).remove();
		var str = $("#msg_user_id").val();
		str = str.replace("," + ID, "");
		$("#msg_user_id").val(str);
	});
});

function htmlCreate(arr){
	var html = '';
	for(var i = 0; i < arr.length; i++){
		html += htmlCreateEntry(arr[i]);
	}
	// if(html != ""){
		// html = "<span style='float: right; margin-top: 6px;'><a class='removePmUser' href='javascript:void(0)'><img src='/common/images/icon-delete.png' alt='削除する' width='13' height='13'></a></span>" + html;
	// }
	$("#sugessUser").html(html);
}

function htmlCreateEntry(data){
	var html = "";
	var owner = $("#user_id").val() == CybozuLabs.MD5.calc(data.user_id);
	if(!owner){
		html += "<li class='addPmUser' id='addPmUser_" + data.user_id + "'><img src='" + data.user_avatar + "' width='25' height='25' />" + data.user_name + "</li>";
	}
	return html;
}
