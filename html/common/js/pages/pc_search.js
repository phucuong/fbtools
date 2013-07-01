$(document).ready(function(){
	getPmList();
	searchUser(1);
	
	$(".read_more").click(function(){
		searchUser($("#page").val());
	});
});

function searchUser(page){
	var search_user_id = $.trim($("#kw").val());
	if(search_user_id != ""){
		var params = {
			"name" 		: search_user_id,
			"page" 		: page
		};
		$.ajax({
			type: "POST",
			url: "/ajax/searchuser/",
			data: params,
			cache: false,
			dataType: "json",
			success: function(data){
				if(data.ok == 1){
					htmlCreateSearch(data.data, page);
				}
			}
		});
	} else {
		$("#searchResult").html("");
	}
}

function htmlCreateSearch(arr, page){
	var html = '';
	for(var i = 0; i < arr.length; i++){
		html += htmlCreateSearchEntry(arr[i]);
	}
	if(html != ""){
		if(page == 1){
			$("#searchResult").html(html);
		} else {
			$("#searchResult").append(html);
		}
		$("#page").val(Number(page) + 1);
	}
}

function htmlCreateSearchEntry(data){
	var html = "";
	html += "<div class='user'><a href='/user/" + data.user_id + "'><img src='" + data.user_avatar + "' width='108px' height='101px' /></a>";
	html += "<div class='user_detail'>";
	html += "<p><a class='userName' href='/user/" + data.user_id + "'>" + data.user_name + "</a></p>";
	if(data.age != ""){
		html += "<p>" + data.age + "歳</p>";
	}
	if(data.user_school != ""){
		html += "<p>" + data.user_school + "</p>";
	}
	html += "</div></div>";
	return html;
}
