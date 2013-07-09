var MessageUtilities = function(kw){
	this.initialize();
};
MessageUtilities.prototype = {
		
	REG_HTTP 	: /(http:\/\/[\x21-\x7e]+)/gi,
	REG_HTTPS	: /(https:\/\/[\x21-\x7e]+)/gi,
	K1			: "¿",
	K2			: "£",
	K3			: "µ",
	K_TMP		: "¡",
	kw 			: "",
	
	initialize : function(kw){
		this.kw = kw;
	},
	
	replaceMessage : function(str,kw,reg,ktemp){
		var matches = str.match(reg);
		var data = 0;
		if(matches !== null){
			var length = matches.length;
			var key = "";
			var key_tmp = "";
			var k = ktemp;
			if(length > 0){
				data = Array();
			}
			for(var i=0; i<length; i++){
				key += k;
				key_tmp = key;
				if(kw === key){
					k = this.K_TMP;
					key = k;
					key_tmp = key;
				}
				data[key_tmp] = matches[i];
				str = str.replace(matches[i],key_tmp);
			}
		}
		var ret = {
				'str' 		: str,
				'arrLink'	: data
		};
		return ret;
	},
	
	replaceHTTP : function(str,kw){
		return this.replaceMessage(str, kw, this.REG_HTTP, this.K1);
	},
	
	replaceHTTPS : function(str,kw){
		return this.replaceMessage(str, kw, this.REG_HTTPS, this.K2);
	},
	
	revertLINK : function(str, arr){
		if(arr === 0){
			return str;
		}
		for(var key in arr){
			str = str.replace(key,arr[key]);
		}
		return str;
	},
	
	makeBoldKeyWord : function(str, kw){
		return str.replace(eval("/" + kw + "/g"), "<span style='background:#FFFF00'>" + kw + "</span>");
	},
	
	setTLMessage : function(str){
		str = str.replace(this.REG_HTTP, "<a href='$1' target='_blank'>$1</a>");
		str = str.replace(this.REG_HTTPS, "<a href='$1' target='_blank'>$1</a>");
		if( !str.match( /([\/]+)(#[^\/^\!^'^<^\s^\n]+)/gi ) ) {

	    	var mtch = str.match( /\s(#[^\/^\!^'^<^\s^\n^　]+)/gmi );
	        if( mtch ) {
	            for( var idx=0; idx<mtch.length; idx++ ) {
	                var str2 = mtch[idx];
				    var enc = encodeURI(str2);
		            enc = enc.replace(/#/g, "%23");
		            str = str.replace( str2, "<a href='/" + $("#network_code").val() + "/search/" + enc + "/#/allfeed/'>" + str2 +"</a>");
	            }
	        }
	    }
		str = str.replace(/(%23#)/g, "%23");
		str = str.replace(/\r\n/g, "<br />");
		str = str.replace(/(\n|\r)/g, "<br />");
		return str;
	},
	
	escapeHtmlTag : function(str) {
		  return str.replace(/<|>|&|"/g, function(s){
		    var map = {"<":"&lt;", ">":"&gt;", "&":"&amp;", "\"":"&quot;"};
		    return map[s];
		  });
	},
	
	setUserName : function( str_sei, str_mei ){
		var str = str_sei + ' ' + str_mei;
		str = this.escapeHtmlTag(str);
		return str;
	},
	
	setYakushoku : function( str_unit, str_position ){
		var str = str_unit + ' ' + str_position;
		str = this.escapeHtmlTag(str);
		return str;
	},
	
	makeMessage : function(str, kw){
		
		str = this.escapeHtmlTag(str);
		
		var arr1 = this.replaceHTTP(str, kw);
		str = arr1.str;
		
		var arr2 = this.replaceHTTPS(str, kw);
		str = arr2.str;
		
		if(kw !== "" && kw !== undefined){
			str = this.makeBoldKeyWord(str, kw);
		}
		
		str = this.revertLINK(str, arr1.arrLink);
		str = this.revertLINK(str, arr2.arrLink);
		str = this.setTLMessage(str);
		return str;
	}
};
