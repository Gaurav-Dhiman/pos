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
                
		$data['category_id'] = $category_id;
                
                $data['categories_list'] = [''=>'None'] + $this->Category->get_categories($category_id);

		$data = $this->xss_clean($data);

		$this->load->view("categories/form", $data);
	}
        
    public function save($category_id = -1)
    {
            $category_data = array(
                    'name' => $this->input->post('name'),
                    'parent_id' => !empty($this->input->post('parent_id')) ? $this->input->post('parent_id') : null,
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
	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Category->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}
        
}

?>