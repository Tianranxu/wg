		//自定义弹框
		(function () {
			/*var handler = function (event) {
				event.preventDefault();
			};
			//拖动弹框背景不会跟着动
			$(document).on("touchmove","#mask",function(event){
				console.log('mask');
				document.addEventListener('touchmove', handler, false);
			});
			$(document).on("touchmove","#mb_mask",function(event){
				console.log('mb_mask');
				document.addEventListener('touchmove', handler, false);
			});
			$(document).on("touchmove","#content-1",function(){
				console.log('content');
				document.addEventListener('touchmove', handler, false);
			});
			//点击弹框以外，隐藏弹框
			$(document).on("click","#mask",function(event){
				console.log('show');
				$(".ad_form").hide();
				$("#mask").hide();
				document.removeEventListener("touchmove", handler, false);
			});
			$(document).on("click","#mb_mask",function(event){
				console.log('show');
				$(".ad_form").hide();
				$("#mask").hide();
				$("#mb_mask,#mb_con").remove();
				document.removeEventListener("touchmove", handler, false);
			});*/
		    $.MsgBox = {
		        Alert: function (title, msg, callback) {
					//按钮
		            GenerateHtml("alert", title, msg);
		            btnOk(callback);  //alert只是弹出消息，因此没必要用到回调函数callback
		            btnNo();
		        },
		        Confirm: function (title, msg, callback, callback1) {
		            GenerateHtml("confirm", title, msg);
		            btnOk(callback);
		            btnNo(callback1);
		        },
		        Waiting: function(title, msg, callback){
		        	//等待滚轮
		        	GenerateHtml("waiting", title, msg);
		        	btnOk();
		            btnNo();
		        }
		    }

		    //生成Html
		    var GenerateHtml = function (type, title, msg) {

		        var _html = "";

		        _html += '<div id="mb_mask"></div><div id="mb_con">';
		        _html += '<div id="mb_msg">' + msg + '</div><div id="mb_btnbox">';

		        if (type == "alert") {
		            _html += '<input id="mb_btn_ok" type="button" value="确定" />';
		        }
		        if (type == "confirm") {
		            _html += '<input id="mb_btn_ok" type="button" value="确定" />';
		            _html += '<input id="mb_btn_no" type="button" value="取消" />';
		        }
		        if (type == "waiting") {
		            _html += '<div class="wrap"><div class="loader" id="lrd1"></div></div>';
		        }
		        _html += '</div></div>';

		        //必须先将_html添加到body，再设置Css样式
		        $("body").append(_html); GenerateCss();
		    }

		    //生成Css
		    var GenerateCss = function () {
		    	$("#mb_mask").css({ width: '100%', height: '100%', zIndex: '9999', position: 'fixed',
      				filter: 'Alpha(opacity=60)', backgroundColor: 'black', top: '0', left: '0', opacity: '0.6'
      			});

		        $("#mb_con").css({ width: '230px',zIndex: '99999', position: 'fixed',webkitBorderRadius: '20px',borderRadius: '20px',padding:'20px',textAlign:'center',
		            backgroundColor: '#333', top: '50%', left: '50%', boxSizing: 'border-box'
		        });

		        $("#mb_tit").css({ display: 'block', fontSize: '14px', color: '#444', padding: '10px 15px',
		            backgroundColor: '#DDD', borderRadius: '15px 15px 0 0',
		            borderBottom: '3px solid #009BFE', fontWeight: 'bold'
		        });

		        $("#mb_msg").css({ padding: '20px', lineHeight: '25px',color:'#fff',
		             fontSize: '1.3em'
		        });

		        $("#mb_ico").css({ display: 'block', position: 'absolute', right: '10px', top: '9px',
		            border: '1px solid Gray', width: '18px', height: '18px', textAlign: 'center',
		            lineHeight: '16px', cursor: 'pointer', borderRadius: '12px', fontFamily: '微软雅黑'
		        });

		        $("#mb_btnbox").css({ margin: '15px 0 10px 0', textAlign: 'center' });
		        $("#mb_btn_ok").css({ width: '80px', height: '30px', color: '#fff', border: 'none',background:'#70b7fb', marginTop:'20px',cursor:'pointer',lineHeight:'30px'});
				$("#mb_btn_no").css({  width: '80px', height: '30px', color: '#fff', border: 'none',background:'#ccc', marginTop:'20px',cursor:'pointer',lineHeight:'30px',marginLeft:'30px',});



		        //右上角关闭按钮hover样式
		        $("#mb_ico").hover(function () {
		            $(this).css({ backgroundColor: 'Red', color: 'White' });
		        }, function () {
		            $(this).css({ backgroundColor: '#ddd', color: 'black' });
		        });

		        var divclass=$('#mb_con');
		        if (!navigator.userAgent.match(/mobile/i)) {
					var a=($(window).width()-divclass.width())/2+$(window).scrollLeft() - 20;
				}
				else{
					var a=($(window).width()-divclass.width())/2+$(window).scrollLeft();
				}
				var b=($(window).height()-divclass.height())/2 - 60;
				divclass.css('left',a);
				divclass.css('top',b); 
		    }


		    //确定按钮事件
		    var btnOk = function (callback) {
		        $("#mb_btn_ok").click(function () {
		            $("#mb_mask,#mb_con").remove();
					//document.removeEventListener("touchmove", handler, false);
		            if (typeof (callback) == 'function') {
		                callback();
		            }
		        });
		    }

		    //取消按钮事件
		    var btnNo = function (callback) {
		        $("#mb_btn_no,#mb_ico").click(function () {
		            $("#mb_mask,#mb_con").remove();
					//document.removeEventListener("touchmove", handler, false);
		            if (typeof (callback) == 'function') {
		                callback();
		            }
		        });
		    }
		})();