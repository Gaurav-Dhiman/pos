<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('categories/save/'.$category_id, array('id'=>'category_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="category_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label('Name', 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'value'=>$selected_name)
						);?>
                            
				<?php echo form_hidden('category_id', $category_id);?>
			</div>
		</div>
            <div class="form-group form-group-sm">
            <?php echo form_label('Parent', 'parent_id', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-4'>
                            
                            <?php echo form_dropdown('parent_id', $categories_list, $selected_parent_id, array('id'=>'parent_id', 'class'=>'form-control')); ?>
			</div>
            </div>    
                
	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("input[name='name']").change(function() {
		if( ! $("input[name='name']").val() ) {
			$("input[name='category_id']").val('');
		}
	});
	
	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[category_id='category_id']").val(ui.item.value);
		$("input[name='name']").val(ui.item.label);
		$("input[parent_id='parent_id']").val(ui.item.label);
	};

//	var autocompleter = $("#person_name").autocomplete({
//		source: '<?php echo site_url("customers/suggest"); ?>',
//    	minChars: 0,
//    	delay: 15, 
//       	cacheLength: 1,
//		appendTo: '.modal-content',
//		select: fill_value,
//		focus: fill_value
//    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function() { 
		$(this).ajaxSubmit({
			success: function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				table_support.handle_submit('<?php echo site_url($controller_name); ?>', {message: errorThrown});
			},
			dataType:'json'
		});
	};
	
	$('#category_form').validate($.extend({
		submitHandler:function(form)
		{
			submit_form.call(form)
		},
		rules:
		{
			name:
			{
				required:true
			}
   		},
		messages:
		{
			giftcard_number:
			{
				required:"<?php echo $this->lang->line('giftcards_number_required'); ?>",
			}
		}
	}, form_support.error));
});
</script>