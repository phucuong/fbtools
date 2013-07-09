function getPmList(){
	$.ajax({
		type: "POST",
		url: "/ajax/getpmlist/",
		data: {},
		cache: false,
		dataType: "json",
		success: function(data){
			if(data.ok == 1){
				htmlCreateLeftMenu(data.data);
			}
		}
	});
}

function htmlCreateLeftMenu(arr){
	var html = '';
	var MENU_LEFT_MAX_ITEM = 10;
	for(var i = 0; i < arr.length; i++){
		html += '<a ' + (i >= MENU_LEFT_MAX_ITEM ? 'class="left_menu_hidden" style="display: none"' : '') + ' href="/message/' + arr[i].pm_id + '"><li ' + (arr[i].pm_id == $("#pm_id").val() ? 'class="leftMenuFocus"' : '') + '>' + arr[i].pm_name + (arr[i].unread > 0 && arr[i].pm_id != $("#pm_id").val() ? ('<span class="unread" id="unread_' + arr[i].pm_id + '">' + arr[i].unread + '</span>') : "") + '</li></a>';
	}
	if(arr.length > MENU_LEFT_MAX_ITEM){
		html += '<a href="javascript:void(0)" id="show_all_left_menu"><li>すべてを表示</li></a>';
	}
	$("#pm_list").html(html);
}

$("#show_all_left_menu").live("click", function(){
	$(".left_menu_hidden").show();
	$("#show_all_left_menu").remove();
});

$(".who_like").live("click", function(){
	$.colorbox({inline:true, width:"auto", href:"#popup_who_like", rel : "popup", transition:"none", scrolling: false, arrowKey: false});
	return false;
});

$(".cbox").live("click", function(){
	$.colorbox({href:$(this).attr("href").replace('.', '_org.'), width:'700px', height:'500px', transition:"none", rel:"nofollow", arrowKey:"false"});
	return false;
});

