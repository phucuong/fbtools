$(document).ready(function(){
	getPmList();
	getTimelineMessage(1, 2, 0, 0);
	
	$(".btn_comment").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("btn_comment_","");
		var params = {
			"message_id" 	: id,
			"comment" 		: $("#message_com_" + id).val(),
			"user_id"		: $("#user_id").val()
		};
		$.ajax({
			type: "POST",
			url: "/ajax/puttimelinecomment/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					$("#message_com_" + id).val("");
					getTimelineCommentMessage(id, "2", $("#top_" + id).val(), $("#bottom_" + id).val());
				}
			}
		});
	});
	
	$(".show_comment").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("show_comment_","");
		$(".hidden_comment_" + id).show();
		$("#show_comment_" + id).remove();
	});
	
	$(".message_like").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("message_like_","");
		var params = {
			"message_id" 	: id
		};
		$.ajax({
			type: "POST",
			url: "/ajax/changelikemessage/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					if(data.like == 1){
						$("#message_like_" + id).html("いいねを取り消す");
					} else {
						$("#message_like_" + id).html("いいね！");
					}
					if(data.count > 0){
						$("#who_like_" + id).html(data.count);
						$("#who_like_span_" + id).show();
					} else {
						$("#who_like_span_" + id).hide();
					}
				}
			}
		});
	});
	
	$(".comment_like").live("click", function(){
		var ids = $(this).attr("id");
		ids = ids.replace("comment_like_","");
		var arr = ids.split("_");
		var params = {
			"message_id" 	: arr[0],
			"message_comment_id" 	: arr[1]
		};
		$.ajax({
			type: "POST",
			url: "/ajax/changelikecomment/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					if(data.like == 1){
						$("#comment_like_" + arr[0] + "_" + arr[1]).html("いいねを取り消す");
					} else {
						$("#comment_like_" + arr[0] + "_" + arr[1]).html("いいね！");
					}
					if(data.count > 0){
						$("#who_like_" + arr[0] + "_" + arr[1]).html(data.count);
						$("#who_like_span_" + arr[0] + "_" + arr[1]).show();
					} else {
						$("#who_like_span_" + arr[0] + "_" + arr[1]).hide();
					}
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
	
	$(".dlParent2").live({
		mouseenter:function(){
			$(this).find('span.delete2').show();
		},
		mouseleave:function(){
			$(this).find('span.delete2').hide();
		}
	});
	
	$(".delete").live("click", function(){
		var id = $(this).attr("id");
		id = id.replace("deletemsg_","");
		var params = {
			"message_id" 	: id
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
	
	$(".delete2").live("click", function(){
		var ids = $(this).attr("id");
		ids = ids.replace("deletecmt_","");
		var arr = ids.split("_");
		var params = {
			"message_id" 			: arr[0],
			"message_comment_id" 	: arr[1]
		};
		$.ajax({
			type: "POST",
			url: "/ajax/deletetimelinecomment/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					$("#comment_" + arr[0] + "_" + arr[1]).remove();
				}
			}
		});
	});
	
	$(".read_more").click(function(){
		getTimelineMessage(3, 2, $("#top").val(), $("#bottom").val());
	});
	
	$(".who_like").live("click", function(){
		var ids = $(this).attr("id");
		ids = ids.replace("who_like_","");
		var arr = ids.split("_");
		var params = {
			"message_id" 			: arr[0],
			"message_comment_id" 	: arr[1]
		};
		$.ajax({
			type: "POST",
			url: "/ajax/wholike/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					var html = htmlCreateLikeUsers(data.data);
					$("#likebox").html(html);
				}
			}
		});
	});
});

function getTimelineMessage(load, msg, topId, bottomId){
	var params = {
		"load_flg" 			: load,
		"msg_flg" 			: msg,
		"top_id" 			: topId,
		"bottom_id" 		: bottomId,
		"target_user_id" 	: $("#target_user_id").val()
	};
	$.ajax({
		type: "POST",
		url: "/ajax/gettimelinemessage/",
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

function getTimelineCommentMessage(messageId, load, topId, bottomId){
	var params = {
		"message_id"		: messageId,
		"load_flg" 			: load,
		"top_id" 			: topId,
		"bottom_id" 		: bottomId
	};
	$.ajax({
		type: "POST",
		url: "/ajax/gettimelinecommentmessage/",
		data: params,
		cache: false,
		dataType: "json",
		success: function(data){
			if(data.ok == 1){
				htmlCreateCommentList(messageId, load, data.data);
			}
		}
	});
}

function htmlCreateLikeUsers(arr){
	var html = '';
	for(var i = 0; i < arr.length; i++){
		var owner = CybozuLabs.MD5.calc(arr[i].user_id) == $("#user_id").val();
		html += '<div class="col1of2">';
		html += '<p';
		if(owner){
			html += ' class="mine"';
		}
		html += '>';
		html += '<a href="/user/' + arr[i].user_id + '"><img class="icon" src="' + arr[i].user_avatar + '" alt="ユーザーアイコン" width="35" height="35">' + arr[i].user_name + '</a>';
		// if(owner){
			// html += '<span class="delete" style="display: none;">';
			// html += '<a href="javascript:void(0);" class="delete like_user_del" id="like_user_del_' + arr[i].like_message_id + '">';
			// html += '<img src="/common/images/icon-delete.png" alt="削除する" width="13" height="13">';
			// html +=	'</a></span>';
		// }
		html += '</p></div>';
		
	}
	return html;
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
		$("#timeline").html(html);
		$("#top").val(arr[0]["message_id"]);
		$("#bottom").val(arr[arr.length - 1]["message_id"]);
		$(".read_more").show();
	} else if(load == 2){
		$("#timeline").prepend(html);
		$("#top").val(arr[0]["message_id"]);
	} else {
		$("#timeline").append(html);
		$("#bottom").val(arr[arr.length - 1]["message_id"]);
	}
}

function htmlCreateCommentList(messageId, load, arr){
	var html = '';
	for(var i = 0; i < arr.length; i++){
		html += htmlCreateComment(arr[i], (i < arr.length - 3));
	}
	if(load == 1){
		$("#comment_list_" + messageId).html(html);
		$("#top_" + messageId).val(arr[arr.length - 1]["message_comment_id"]);
		$("#bottom_" + messageId).val(arr[0]["message_comment_id"]);
	} else if(load == 2){
		$("#comment_list_" + messageId).append(html);
		$("#top_" + messageId).val(arr[arr.length - 1]["message_comment_id"]);
	} else {
		$("#comment_list_" + messageId).prepend(html);
		$("#bottom_" + messageId).val(arr[0]["message_comment_id"]);
	}
}

function htmlCreateEntry(data){
	var html = "";
	var owner = $("#user_id").val() == CybozuLabs.MD5.calc(data.user_id);
	var messageUtilities = new MessageUtilities();
	var wk_msg = messageUtilities.makeMessage(data.message, "");
	html += '<div id="timeline_list_' + data.message_id + '" class="timeline_list';
	if(owner){
		html += ' dlParent"><span class="delete" id="deletemsg_' + data.message_id + '" style="display: none;"><a href="javascript:void(0);" class="modal5"><img src="/common/images/icon-delete.png" alt="削除する" width="13" height="13"></a></span>';
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
		// html += '<div class="gallery">';
		html += '<ul class="img_list" id="img_list_' + data.message_id + '">';
		for(var i = 0; i < data.message_image_array.length; i++){
			html += '<li><a class="cbox" href="' + data.message_image_array[i].img_url + '"><img src="' + data.message_image_array[i].img_url + '" /></a></li>';
		}
		html += '</ul>';
		// html += '</div>';
	}
	html += '<p class="iine">';
	html += '<span class="posttime">' + data.regist_date + '</span>';
	if(!owner){
		html += '<a href="javascript:void(0)" id="message_like_' + data.message_id + '" class="message_like">';
		if(data.message_id == data.like_message_id){
			html += 'いいねを取り消す';
		} else {
			html += 'いいね！';
		}
		html += '</a>';
	}
	html += '<span id="who_like_span_' + data.message_id + '" ' + (data.like_count <= 0 ? 'style="display: none;"' : 'class="aaa"') + '><a href="javascript:void(0)" class="who_like" id="who_like_' + data.message_id + '" >' + data.like_count + '</a>人がいいねと言っています</span>';
	html += '</p>';
	html += '</div>';
	html += '<div class="toukou_comment_tomodati">';
	html += '<input type="hidden" id="top_' + data.message_id + '" value="0">';
	html += '<input type="hidden" id="bottom_' + data.message_id + '" value="0">';
	if(data.comment_count > 3){
		html += '<div class="show_hidden_comments"><a href="javascript:void(0);" id="show_comment_' + data.message_id + '" class="show_comment">すべてを表示</a></div>';
	}
	html += '<div id="comment_list_' + data.message_id + '"></div>';
	if(data.comment_count > 0){
		getTimelineCommentMessage(data.message_id, 1, 0, 0);
	}
	html += '<p class="toukou_comment_tomodati_end"><img src="/common/images/spacer.gif" alt="投稿コメントの終わりです" width="1" height="1"></p>';
	html += '</div>';
	html += '<div class="toukou_comment_zibun">';
	html += '<div class="toukou_comment_form">';
	html += '<img src="' + $("#user_avatar").val() + '" width="62px" height="55px" >';
	html += '<textarea type="text" class="message_com" id="message_com_' + data.message_id + '"  placeholder="コメント" value="" ></textarea>';
	html += '<input class="btn_comment" id="btn_comment_' + data.message_id + '" type="image" src="/common/images/toukousuru01.gif" width="78" height="38" alt="投稿する" >';
	html += '<p class="toukou_comment_form_end"><img src="/common/images/spacer.gif" alt="コメント欄の終わりです" width="1" height="1"></p>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '<p class="timeline_list_end"><img src="/common/images/spacer.gif" alt="ひとつ投稿の終わりです" width="1" height="1"></p>';
	html += '</div>';
	return html;
}

function htmlCreateComment(data, isShow){
	var html = "";
	var owner = $("#user_id").val() == CybozuLabs.MD5.calc(data.user_id);
	var messageUtilities = new MessageUtilities();
	var wk_msg = messageUtilities.makeMessage(data.message_com, "");
	html += '<div id="comment_' + data.message_id + '_' + data.message_comment_id + '" class="toukou_comment_tomodati_list';
	if(isShow){
		html += ' hidden_comment hidden_comment_' + data.message_id;
	}
	if(owner){
		html += ' dlParent2"><span class="delete2" id="deletecmt_' + data.message_id + '_' + data.message_comment_id + '" style="display: none;"><a href="javascript:void(0);" class="modal5"><img src="/common/images/icon-delete.png" alt="削除する" width="13" height="13"></a></span>';
	} else {
		html += '">';
	}
	// html += '">';
	html += '<div class="toukou_comment_tomodati_info"><a href="/user/' + data.user_id + '"><img src="' + data.user_avatar + '" width="62px" height="55px" /></a>';
	html += '<p><a class="userName" href="/user/' + data.user_id + '">' + data.user_name + '</a></p></div>';
	html += '<div class="toukou_comment_tomodati_toukou">';
	html += wk_msg;
	html += '<p class="iine">';
	html += '<span class="posttime">' + data.regist_date + '</span>';
	if(!owner){
		html += '<a href="javascript:void(0);" id="comment_like_' + data.message_id + '_' + data.message_comment_id + '" class="comment_like">';
		if(data.message_comment_id == data.like_message_comment_id){
			html += 'いいねを取り消す';
		} else {
			html += 'いいね！';
		}
		html += '</a>';
	}
	// if(data.like_count > 0){
		// html += '<a href="javascript:void(0)" class="who_like" id="who_like_' + data.message_id + '_' + data.message_comment_id + '" >' + data.like_count + '</a>人がいいねと言っています';
	// }
	html += '<span id="who_like_span_' + data.message_id + '_' + data.message_comment_id + '" ' + (data.like_count <= 0 ? 'style="display: none;"' : '') + '><a href="javascript:void(0)" class="who_like" id="who_like_' + data.message_id + '_' + data.message_comment_id + '" >' + data.like_count + '</a>人がいいねと言っています</span>';
	html += '</p>';
	html += '</div>';
	html += '<p class="toukou_comment_tomodati_list_end"><img src="/common/images/spacer.gif" alt="ひとつの投稿コメントの終わりです" width="1" height="1"></p>';
	html += '</div>';
	return html;
}
