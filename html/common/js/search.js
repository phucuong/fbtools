$(document).ready(function(){
	$("#search_user_id").keyup(function(event){
		var search_user_id = $.trim($("#search_user_id").val());
		if(search_user_id != ""){
			var params = {
				"name" 		: search_user_id,
				// "page" 		: 1,
				// "limit" 	: 5,
			};
			$.ajax({
				type: "POST",
				url: "/ajax/searchuser/",
				data: params,
				cache: false,
				dataType: "json",
				success: function(data){
					if(data.ok == 1){
						if(search_user_id == $.trim($("#search_user_id").val())){
							htmlSearch(data.data);
						}
					}
				}
			});
		} else {
			$("#search_result").html("");
		}
	});
	
	$(':not(#search_result)').click(function() {
		$('#search_result').html("");
	});
});

function htmlSearch(arr){
	var html = '';
	for(var i = 0; i < arr.length; i++){
		html += htmlSearchEntry(arr[i]);
	}

	// html += "<a href='/search/" + $.trim($("#search_user_id").val()) + "'><li class='addPmUser'><center>show all result</center></li></a>";
	if(html == ""){
		html += "<center>no result</center>";
	}
	$("#search_result").html(html);
}

function htmlSearchEntry(data){
	var html = "";
	html += "<a href='/user/" + data.user_id + "'><li class='addPmUser'><img src='" + data.user_avatar + "' width='25' height='25' />" + data.user_name + "</li></a>";
	return html;
}
