<?php
class Category extends CI_Model
{
	/*
	Performs a search
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'category_id', $order = 'asc')
	{
		$this->db->select('item_categories.*, ic.name as parent_name')->from('item_categories');
                $this->db->join('item_categories as ic', 'ic.category_id = item_categories.parent_id', 'left');
		$this->db->group_start();
			$this->db->like('item_categories.name', $search);
			$this->db->or_like('ic.name', $search);
		$this->db->group_end();
		$this->db->where('item_categories.deleted', 0);
		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}
                
		return  $this->db->get();
                
	}
        
        public function get_categories($category_id)
	{
		$this->db->select('item_categories.category_id, item_categories.name')
                         ->from('item_categories');
                
                if($category_id!='include_deleted'){
                    $this->db->where('item_categories.deleted', 0);
                }
                
                $this->db->where('item_categories.category_id !=', $category_id);
                
                $results  = $this->db->get()->result();
                
                $arr = [];
                
                foreach($results as $result){
                    $arr = $arr + [$result->category_id=>$result->name];
                }

		return $arr;
	}
        
	public function get_found_rows($search)
	{
		$this->db->select('item_categories.*, ic.name as parent_name')->from('item_categories');
                $this->db->join('item_categories as ic', 'ic.category_id = item_categories.parent_id', 'left');
		$this->db->group_start();
			$this->db->like('item_categories.name', $search);
		$this->db->group_end();
		$this->db->where('item_categories.deleted', 0);

		return $this->db->get()->num_rows();
	}
        
       
        
	public function get_info($category_id)
	{
		$this->db->select('item_categories.*, ic.name as parent_name')->from('item_categories');
                $this->db->join('item_categories as ic', 'ic.category_id = item_categories.parent_id', 'left');
		$this->db->where('item_categories.category_id', $category_id);
		$this->db->where('item_categories.deleted', 0);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$category_obj = new stdClass();

			//Get all the fields from table
			foreach($this->db->list_fields('item_categories') as $field)
			{
				$category_obj->$field = '';
			}

			return $category_obj;
		}
	}
        
        /*
	Inserts or updates a category
	*/
	public function save(&$category_data, $category_id = -1)
	{
		if($category_id == -1 || !$this->exists($category_id))
		{
			if($this->db->insert('item_categories', $category_data))
			{
				$category_data['category_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('category_id', $category_id);

		return $this->db->update('item_categories', $category_data);
	}
        
        public function exists($category_id)
	{
		$this->db->from('item_categories');
		$this->db->where('category_id', $category_id);
		$this->db->where('deleted', 0);

		return ($this->db->get()->num_rows() == 1);
	}
        
        public function delete_list($category_ids)
	{
		$this->db->where_in('category_id', $category_ids);

		return $this->db->update('item_categories', array('deleted' => 1));
 	}
        
        public function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('item_categories');
		$this->db->like('name', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[]=array('label' => $row->name);
		}			

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}
}
?>
