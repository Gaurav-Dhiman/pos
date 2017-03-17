<style type="text/css">
	
</style>
<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open_multipart('categories/do_excel_import/', array('id'=>'excel_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<div class="col-xs-12">
				<a href="<?php echo site_url('categories/excel'); ?>"><?php echo $this->lang->line('common_download_import_template'); ?></a>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<div class='col-xs-12'>
				<div class="fileinput fileinput-new input-group" data-provides="fileinput">
					<div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i><span class="fileinput-filename"></span></div>
					<span class="input-group-addon input-sm btn btn-default btn-file"><span class="fileinput-new"><?php echo $this->lang->line("common_import_select_file"); ?></span><span class="fileinput-exists"><?php echo $this->lang->line("common_import_change_file"); ?></span><input type="file" id="file_path" name="file_path" accept=".csv"></span>
					<a href="#" class="input-group-addon input-sm btn btn-default fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("common_import_remove_file"); ?></a>
				</div>
			</div>
		</div>

		

	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
function ajaxindicatorstart(text)
{
	if(jQuery('body').find('#resultLoading').attr('id') != 'resultLoading'){
	jQuery('body').append('<div id="resultLoading" style="display:none"><div><img src="images/ajax-loader.gif"><div>'+text+'</div></div><div class="bg"></div></div>');
	}

	jQuery('#resultLoading').css({
		'width':'100%',
		'height':'100%',
		'position':'fixed',
		'z-index':'10000000',
		'top':'0',
		'left':'0',
		'right':'0',
		'bottom':'0',
		'margin':'auto'
	});

	jQuery('#resultLoading .bg').css({
		'background':'#000000',
		'opacity':'0.7',
		'width':'100%',
		'height':'100%',
		'position':'absolute',
		'top':'0'
	});

	jQuery('#resultLoading>div:first').css({
		'width': '250px',
		'height':'75px',
		'text-align': 'center',
		'position': 'fixed',
		'top':'0',
		'left':'0',
		'right':'0',
		'bottom':'0',
		'margin':'auto',
		'font-size':'16px',
		'z-index':'10',
		'color':'#ffffff'

	});

    jQuery('#resultLoading .bg').height('100%');
       jQuery('#resultLoading').fadeIn(300);
    jQuery('body').css('cursor', 'wait');
}
function ajaxindicatorstop()
{
    jQuery('#resultLoading .bg').height('100%');
       jQuery('#resultLoading').fadeOut(300);
    jQuery('body').css('cursor', 'default');
}
jQuery.ajax({
   global: false,
   // ajax stuff
});


//validation and submit handling
$(document).ready(function()
{	
	$('#excel_form').validate($.extend({
		submitHandler:function(form) {
			jQuery(document).ajaxStart(function () {
	 		//show ajax indicator
			ajaxindicatorstart('Loading.. please wait..');
			}).ajaxStop(function () {
			//hide ajax indicator
			ajaxindicatorstop();
			});
			$(form).ajaxSubmit({
				success:function(response)
				{
					dialog_support.hide();
					if(response.success == false)
					{	
						$.notify(response.message, { timer:'',type: response.success ? 'success' : 'danger'} );
						$(".alert-danger").css({"max-height":'200px'});
						$(".alert-danger").css({"overflow-y":'scroll'});
					}
					else
					{	
						$.notify(response.message, { type: response.success ? 'success' : 'danger'} );
					}	
				},
				dataType: 'json'
			});
		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			file_path: "required"
   		},
		messages: 
		{
   			file_path: "<?php echo $this->lang->line('common_import_full_path'); ?>"
		}
	}, form_support.error));
});
</script>
