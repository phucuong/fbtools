$(document).ready(function(){
	getPmList();
	getPmMessage(1, 0, 0);
	
	$("#toukou_btn").click(function(){
		var params = {
			"message" 	: $("#toukou_filed_id").val(),
			"pm_id" 	: $("#pm_id").val(),
			"attach" 	: $("#uploadValues").val(),
		};
		$.ajax({
			type: "POST",
			url: "/ajax/putpmmessage/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					$("#uploadValues").val("");
					$("#attach_files").html("");
					$("#toukou_filed_id").val("");
					getPmMessage(2, $("#top").val(), $("#bottom").val());
				}
			}
		});
	});
	
	$(".dlParent").live({
		mouseenter:function(){
			$(this).find('span.delete').show();
		},
		mouseleave:function(){
			$(this).find('span.delete').hide();
		}
	});
	
	$(".delete").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("deletemsg_","");
		var params = {
			"pm_message_id" 	: id,
			"pm_id" 			: $("#pm_id").val()
		};
		$.ajax({
			type: "POST",
			url: "/ajax/deletepmmessage/",
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
	
	$(".read_more").click(function(){
		getPmMessage(3, $("#top").val(), $("#bottom").val());
	});
	
	$("#upload_btn").live('change',function(){
		$(".error").hide();
		$("#upload_btn").upload(
			'/ajax/uploadimage',
			{"from" : "2"},
			function(data){
				if(data.ok == 1){
					var html = '<span id="removeSpan_' + data.data.img_id + '" class="attach_file"><img src="' + data.data.upfile + '" width="42px" height="42px" /> <a id="removeAtt_' + data.data.img_id + '" class="removeAtt" href="javascript:void(0)"><img src="/common/images/icon-delete.png" /></a></span>';
					$("#attach_files").append(html);
					document.getElementById('upload_btn').parentNode.innerHTML = document.getElementById('upload_btn').parentNode.innerHTML;
					var uploadValues = $("#uploadValues").val();
					uploadValues += (uploadValues == "" ? "" : ",") + data.data.img_id;
					$("#uploadValues").val(uploadValues);
				} else {
					$("#error_" + data.ok).show();
				}
			},
			'json'
		);
	});
	
	$(".removeAtt").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("removeAtt_","");
		var str = $("#uploadValues").val();
		str = str.replace(id + ",", "");
		str = str.replace("," + id, "");
		str = str.replace(id, "");
		$("#uploadValues").val(str);
		$("#removeSpan_" + id).remove();
		// var params = {
			// "message_id" 	: id,
		// };
		// $.ajax({
			// type: "POST",
			// url: "/ajax/deletetimelinemessage/",
			// data: params,
			// cache: false,
			// dataType: "json",
			// success: function(data){
				// if(data.ok == 1){
					// $("#timeline_list_" + id).remove();
				// }
			// }
		// });
	});
});

function getPmMessage(load, topId, bottomId){
	var params = {
		"load_flg" 			: load,
		"top_id" 			: topId,
		"bottom_id" 		: bottomId,
		"pm_id" 			: $("#pm_id").val(),
	};
	$.ajax({
		type: "POST",
		url: "/ajax/getpmmessage/",
		data: params,
		cache: false,
		dataType: "json",
		success: function(data){
			if(data.ok == 1){
				htmlCreate(data.data, load);
			}
		}
	});
}

function htmlCreate(arr, load){
	if(arr.length == 0) return;
	var html = '';
	for(var i = 0; i < arr.length; i++){
		html += htmlCreateEntry(arr[i]);
	}
	if(html == ''){
		return;
	}
	if(load == 1){
		$("#message").html(html);
		$("#top").val(arr[0]["pm_message_id"]);
		$("#bottom").val(arr[arr.length - 1]["pm_message_id"]);
		$(".read_more").show();
	} else if(load == 2){
		$("#message").prepend(html);
		$("#top").val(arr[0]["pm_message_id"]);
	} else {
		$("#message").append(html);
		$("#bottom").val(arr[arr.length - 1]["pm_message_id"]);
	}
}

function htmlCreateEntry(data){
	var html = "";
	var owner = $("#user_id").val() == CybozuLabs.MD5.calc(data.user_id);
	var messageUtilities = new MessageUtilities();
	var wk_msg = messageUtilities.makeMessage(data.message, "");
	html += '<div id="timeline_list_' + data.pm_message_id + '" class="timeline_list';
	if(owner){
		html += ' dlParent"><span class="delete" id="deletemsg_' + data.pm_message_id + '" style="display: none;"><a href="javascript:void(0);" class="modal5"><img src="/common/images/icon-delete.png" alt="削除する" width="13" height="13"></a></span>';
	} else {
		html += '">';
	}
	html += '<div class="toukou_user"><a href="/user/' + data.user_id + '"><img src="' + data.user_avatar + '" width="108px" height="101px" /></a>';
	html += '<p class="toukou_user_name"><a class="userName" href="/user/' + data.user_id + '">' + data.user_name + '</a></p>';
	if(data.user_birthday_openflag == "1" && data.user_age != ""){
		html += '<p>' + data.age + '歳</p>';
	}
	if(data.user_school_openflag == "1" && data.user_school != ""){
		html += '<p>' + data.user_school + '</p>';
	}
	html += '</div>';
	html += '<div class="toukou_naiyou_area">';
	html += '<div class="toukou_naiyou">';
	html += wk_msg;
	if(data.img_count > 0){
		html += '<ul class="img_list" id="img_list_' + data.pm_message_id + '">';
		for(var i = 0; i < data.message_image_array.length; i++){
			html += '<li><a class="cbox" href="' + data.message_image_array[i].img_url + '"><img src="' + data.message_image_array[i].img_url + '" /></a></li>';
		}
		html += '</ul>';
	}
	html += '<p class="iine">';
	html += '<span class="posttime">' + data.regist_date + '</span>';
	html += '</p>';
	html += '</div>';
	html += '</div>';
	html += '<p class="timeline_list_end"><img src="/common/images/spacer.gif" alt="ひとつ投稿の終わりです" width="1" height="1"></p>';
	html += '</div>';
	return html;
}
