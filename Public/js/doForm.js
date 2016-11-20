/**
 * 
 */
$(function() {
	$( "#sortable" ).sortable();
	//$( "#sortable" ).disableSelection();
	
	/**表单控件点击事件**/
	$('.mlist_pick>ul>li>input').click(function(){
		var input_id = $(this).attr('id');
		var length = $("#sortable li[id='"+input_id+"']").length;
		var widgetHtml=$(this).next('div').text();
		widgetHtml = widgetHtml.replace(/name="([^"]*)"/g, "name='$1_"+(length+1)+"'");

		$('#sortable').append(widgetHtml);
		$(".formDate").dateDropper({
			color:'#94cefa',
			textColor:'#94cefa',
			borderColor:'#94cefa',
			borderRadius:'10',
			boxShadow:'0 0px 0px 3px rgba(0,0,0,0.1)',
			format:'Y-m-d',
			lang:'cn',
			animation:'dropdown',
			minYear:'1949',
			maxYear:'2100',
			years_multiple:'10',
		});
	});
	
	/**单选框选择事件**/
	$(':input[type="radio"]').click(function(){
		$(this).prop('checked',true);
		$(this).parent('label').removeClass('check_bg');
		$(this).parent('label').siblings('label').find(':input[type="radio"]').prop('checked',false);
		$(this).parent('label').siblings('label').addClass('check_bg');
	});
	
});

/**表单控件删除按钮点击事件**/
function removeWidget(id){
	$('#sortable').children('li').eq($(id).parent().index()).remove();
}