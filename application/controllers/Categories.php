<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Categories extends Secure_Controller{
    
    public function __construct()
	{
		parent::__construct('categories');
	}
        
    public function index(){
       
        $data['table_headers'] = $this->xss_clean(get_categories_manage_table_headers());
 
	$this->load->view('categories/manage', $data);        
    }
    
    /*
	Returns Giftcards table data rows. This will be called with AJAX.
	*/
    public function search()
    {
            $search = $this->input->get('search');
            $limit  = $this->input->get('limit');
            $offset = $this->input->get('offset');
            $sort   = $this->input->get('sort');
            $order  = $this->input->get('order');

            $categories = $this->Category->search($search, $limit, $offset, $sort, $order);
            
            $total_rows = $this->Category->get_found_rows($search);

            $data_rows = array();
            foreach($categories->result() as $category)
            {
                    $data_rows[] = get_category_data_row($category, $this);
            }

            $data_rows = $this->xss_clean($data_rows);

            echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
    }
    
     public function view($category_id = -1)
	{
		$category_info = $this->Category->get_info($category_id);

		$data['selected_name'] = ($category_id > 0 && isset($category_info->category_id)) ? $category_info->name : '';
                $data['selected_parent_id'] = ($category_id > 0 && isset($category_info->parent_id)) ? $category_info->parent_id : '';
                $data['selected_parent_name'] = ($category_id > 0 && isset($category_info->parent_name)) ? $category_info->parent_name : '';
                
                $data['selected_tags'] = ($category_id > 0 && isset($category_info->tags)) ? $category_info->tags : '';
                
		$data['category_id'] = $category_id;
                
             //   $data['categories_list'] = [''=>'None'] + $this->Category->get_categories($category_id);

		$data = $this->xss_clean($data);

		$this->load->view("categories/form", $data);
	}
        
    public function save($category_id = -1)
    {
            $category_data = array(
                    'name' => $this->input->post('name'),
                    'parent_id' => !empty($this->input->post('parent_id')) ? $this->input->post('parent_id') : null,
                    'tags' => !empty($this->input->post('tags')) ? $this->input->post('tags') : null,
            );

            if($this->Category->save($category_data, $category_id))
            {
                    $category_data = $this->xss_clean($category_data);

                    //New category
                    if($category_id == -1)
                    {
                            echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('categories_successful_adding'), 'id' => $category_data['category_id']));
                    }
                    else //Existing category
                    {
                            echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('categories_successful_updating'), 'id' => $category_id));
                    }
            }
            else //failure
            {
                    $category_data = $this->xss_clean($category_data);

                    echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('categories_error_adding_updating'), 'id' => -1));
            }
    }
    
    public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_category_data_row($this->Category->get_info($row_id), $this));

		echo json_encode($data_row);
	}
        
    public function delete()
	{
		$categories_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Category->delete_list($categories_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('categories_successful_deleted').' '.
							count($categories_to_delete).' '.$this->lang->line('categories_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('categories_cannot_be_deleted')));
		}
	}
        
        /*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_search($exclude=null)
	{
		$suggestions = $this->xss_clean($this->Category->get_category_suggestions($this->input->get('term'), $exclude));

		echo json_encode($suggestions);
	}
        
        /*
	Categories import from excel spreadsheet
	*/
	public function excel()
	{
		$name = 'import_categories.csv';
		$data = file_get_contents('../' . $name);
		force_download($name, $data);
	}
	
	public function excel_import()
	{
		$this->load->view('categories/form_excel_import', NULL);
	}

	public function do_excel_import(){
            
		if($_FILES['file_path']['error'] != UPLOAD_ERR_OK){
                    
		    echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('categories_excel_import_failed')));
		}else{
                    
                    $categories = $this->Category->get_categories('include_deleted');
                    $categoriesExits = array();
                        
                    $categoriesExits = $categoriesExits + array_keys($categories);

//                    if(count($categories)>0){
//                        $m= $this->lang->line('categories_already_imported');
//                        echo json_encode(array('success' => FALSE, 'message' => $m));
//                        exit;
//                    }
                    
                    if(($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== FALSE){
                        // Skip the first row as it's the table description
                        fgetcsv($handle);
                        $i = 2;

                        $failCodes = array();
                        $resdata = array();
                        $chk = array();
                        
                        while(($categorydata = fgetcsv($handle)) !== FALSE){

                            $resdata[] = $this->xss_clean($categorydata);
                           
                            if(empty($categorydata[0]) || $categorydata[0] < 0 || !whole_int($categorydata[0])){
                                $chk['category_id']['ids'][] = $i;
                                $chk['category_id']['msg']   = 'categories_categoryid_required';
                            }elseif(in_array($categorydata[0], $categoriesExits)){
                                $chk['category_id']['ids'][] = $i;
                                $chk['category_id']['msg'] = 'categories_categoryid_duplicate';	
                            }
                            
                            if(!empty($categorydata[1]) && (!whole_int((int)$categorydata[1]))){
                                $chk['parent_id']['ids'][] = $i;
                                $chk['parent_id']['msg']   = 'categories_parentid_integer_required';
                            }elseif(!empty($categorydata[1]) && !in_array($categorydata[1], $categoriesExits)){
                                $chk['parent_id']['ids'][] = $i;
                                $chk['parent_id']['msg']   = 'categories_parentid_not_exist';
                            }
                            
                            if(empty($categorydata[2])){
                                $chk['name']['ids'][] = $i;
                                $chk['name']['msg']   = 'categories_name_required';
                            }

                            array_push($categoriesExits, $categorydata[0]);
                            $i++;
                        }

                        if(!empty($chk)){
                            
                            $msg = array();
                            foreach ($chk as $key => $value) {
                                    if(!empty($value['ids'])){	
                                        $msg[] = wordwrap(($this->lang->line($value['msg'])." ".$this->lang->line('common_at_rows').implode(", ",$value['ids'])),55, "<br />\n");
                                    }
                            }
                            echo json_encode(array('success' => FALSE, 'message' => implode("<br>",$msg)));
                            exit;
                        }
                        
                        $j = 2;

                        foreach ($resdata as $data) {

                            // XSS file data sanity check
                            //$data = $this->xss_clean($data);

                            /* haven't touched this so old templates will work, or so I guess... */
                            if(sizeof($data) >= 4){
                                $category_data = array(
                                        'category_id' => $data[0],
                                        'parent_id'   => !empty($data[1]) ? $data[1] : null,
                                        'name'	      => $data[2],
                                        'tags'        => !empty($data[3]) ? $data[3] : null,
                                );
                                $invalidated = FALSE;
                            }else{
                                    $invalidated = TRUE;
                            }

                            if($invalidated || !$this->Category->save($category_data)){
                                $failCodes[] = $j;
                            }
                            $j++;
                        }
                            
                        if(count($failCodes) > 0){
                            $message = $this->lang->line('categories_excel_import_partially_failed') . ' (' . count($failCodes) . '): ' . implode(', ', $failCodes);

                            echo json_encode(array('success' => FALSE, 'message' => $message));
                        }else{
                            echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('categories_excel_import_success')));
                        }
                    }else{
                            echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('categories_excel_import_nodata_wrongformat')));
                    }
		}
	}
        
}

?>