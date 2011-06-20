<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Morematrixrelations_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'moreMatrixRelations',
		'version'	=> '1.2'
	);
	
	
	// --------------------------------------------------------------------
	
	function __constructor()
	{
		$this->EE =& get_instance();
	}
	
	// =========================================
	// = Display the field in the publish form =
	// =========================================
	function display_field($data)
	{
		//Do nothing. Only matrix compatible.
	}
	
	
	
	
	// =========================================
	// = Display the field in the matrix field =
	// =========================================
	public function display_cell($data)
	{
		$this->EE->load->helper('form');
		
		$this->EE->db->where('channel_id', $this->settings['channel']);
		$this->EE->db->select('entry_id, title, status');
		$q = $this->EE->db->get('channel_titles');
		
		$entries = array();
		foreach($q->result() as $qr){
			$entries[$qr->entry_id] = $qr->status == "closed" ? $qr->title  . " [closed]" : $qr->title;
		}
		
		return form_dropdown($this->cell_name, $entries, $data);
	}

	
	
	
	// =========================
	// = Matrix Field Settings =
	// =========================
	public function display_cell_settings($data = array())
	{
		$this->EE->load->helper('form');
		
		$data['channel'] = isset($data['channel']) ? $data['channel'] : NULL;
		$channels = array();
		
		$this->EE->db->select('channel_id, channel_title');
		$q = $this->EE->db->get('channels');
		
		foreach($q->result() as $qr){
			$channels[$qr->channel_id] = $qr->channel_title;
		}
			
		return array(
			array("Channel", form_dropdown('channel', $channels, $data['channel']))
		);
	}
	

	// ===========================
	// = Render tag in front end =
	// ===========================
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		
		
		$this->EE->db->where('entry_id', $data);
		$this->EE->db->where('status !=', 'closed');
		$q = $this->EE->db->get('channel_titles');
	
		
		if($q->num_rows() > 0){
			$qa = $q->row();
			
			//Add the pages URI
			if(isset($this->EE->config->config['site_pages'][$this->EE->config->item('site_id')]['uris'][$data])){
				$qa->page_uri = $this->EE->config->config['site_pages'][$this->EE->config->item('site_id')]['uris'][$data];
			}
			
			
			if(isset($params['field'])){
				return isset($qa->$params['field']) ? $qa->$params['field'] : NULL;
			}else{
				return $qa->title;
			}
			
		}else{
			return;
		}
		
	}
	
}
// END Matrix_relations_ft class

/* End of file ft.morematrixrelations.php */
/* Location: ./system/expressionengine/third_party/morematrixrelations/ft.morematrixrelations.php */