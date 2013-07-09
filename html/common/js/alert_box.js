var AlertBox = function(){
	this.initialize();
};

AlertBox.prototype = {
	BOX_WIDTH : 500,
	alertBox : null,
	alertContent: null,
	btnClose	: null,
	dvWrap: null,
	
	initialize : function(){
		this.alertBox = $("#alert_box");
		this.alertContent = $("#alert_content");
		this.dvWrap = $("#dv_wrap");
		this.dvWrap.css('height',screen.height);
		this.btnClose = $("#close_box");
		this.setListener();
	},
	
	setListener : function(){
		var self = this;
		$(document).live('click',function(){
			self.alertBox.hide();
			self.dvWrap.hide();
		});
		this.alertBox.live('click',function(event){
			event.stopPropagation();
		});
		this.btnClose.live('click',function(){
			self.alertBox.hide();
			self.dvWrap.hide();
		});
	},
	
	show : function(msg){
		this.setContent(msg);
		this.setPosition();
		this.dvWrap.css('display','block');
		this.alertBox.css('display','block');
	},
	
	setContent: function(content){
		this.alertContent.html(content);
	},
	
	setPosition : function(){
		var objectDim = this.calcDimmention();
		this.alertBox.css({'left':objectDim.width, 'top': objectDim.height});
	},
	
	calcDimmention : function(){
		var clientHeight = screen.height;
		var clientWidth = screen.width;
		var boxHeight = this.alertBox.height();
		var w = clientWidth/2 - this.BOX_WIDTH/2;
		var h = clientHeight/2 -boxHeight/2 -100;
		var result = {
			width : w,
			height: h
		};
		return result;
	}
	
};