<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Morematrixrelations_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'moreMatrixRelations',
		'version'	=> '1.5.2'
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
		$multiselect = "n";
		if(isset($this->settings['multiselect'])){
			$multiselect = $this->settings['multiselect'];
		}
        
        if(!is_array($data)){
            $data = explode("|", $data);
        }
		

		if($multiselect == "n"){
			$data = $data[0];
		}


		$this->EE->load->helper('form');

		$this->EE->db->select('channel_titles.entry_id, channel_titles.title, channel_titles.status, channels.channel_title');		
		$this->EE->db->where_in('channel_titles.channel_id', $this->settings['channel']);
		$this->EE->db->order_by("channels.channel_title", "asc");
		$this->EE->db->order_by("channel_titles.entry_date", "desc");
		$this->EE->db->from("channel_titles");
		$this->EE->db->join("channels", "channel_titles.channel_id = channels.channel_id");
		$q = $this->EE->db->get();
		
		$entries = array( NULL => '--');
		foreach($q->result() as $qr){
			$entries[$qr->channel_title][$qr->entry_id] = $qr->status == "closed" ? $qr->title  . " [closed]" : $qr->title;
		}
		
		if($multiselect == "y"){
			return form_multiselect($this->cell_name."[]", $entries, $data, "size='7' style='width:100%'");
		}else{
			return form_dropdown($this->cell_name."[]", $entries, $data);
		}
	}




	function save_cell( $data ){

		return implode("|", $data);

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

		if(!is_array($data['channel'])){
			$data['channel'] = array($data['channel']);
		}

		if(!isset($data['multiselect'])){
			$data['multiselect'] = "n";
		}


		$out = "";

		foreach($channels as $channel_id => $channel_title){
			$out  .= "<label>".form_checkbox("channel[]", $channel_id, in_array($channel_id, $data['channel']))." ".$channel_title."</label><br/>";
		}

			
		return array(
			array("Multiple Select", form_dropdown("multiselect", array("n" => "No", "y" => "Yes"), $data['multiselect'])),
			array("Channel", $out)
		);
	}



	

	// ====================
	// = Field validation =
	// ====================
	public function validate_cell( $data )
	{
	  if ($this->settings['col_required'] == 'y') {
		if (! $data) {
		  return lang('col_required');
		}
	  }
	  return TRUE;
	}
	

	// ===========================
	// = Render tag in front end =
	// ===========================
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$data = explode("|", $data);



		//Entry ID
		$this->EE->db->where_in('entry_id', $data);

		//Status
		if (isset($params['status'])) {
			$this->EE->db->where('status', $params['status']);
		}else{
			$this->EE->db->where('status !=', 'closed');
		}
		

		//Order
		if (isset($params['orderby'])) {
			$sort = isset($params['sort']) ? $params['sort'] : "asc";
			$this->EE->db->order_by($params['orderby'], $params['sort']);
		}


		$q = $this->EE->db->get('channel_titles');
	
		//No results, return nothing
		if($q->num_rows() == 0) return;


		$vars = $q->result_array();


		foreach($vars as $key => $row){
			
			//Add the pages URI
			$vars[$key]['page_uri'] = "";
			foreach($data as $entry_id){
				if(isset($this->EE->config->config['site_pages'][$this->EE->config->item('site_id')]['uris'][$entry_id]) && $entry_id == $row['entry_id']){
					$vars[$key]['page_uri'] = $this->EE->config->config['site_pages'][$this->EE->config->item('site_id')]['uris'][$entry_id];
				}
			}
		
		}


		return $this->EE->TMPL->parse_variables($tagdata, $vars);
	}

	
}
// END Matrix_relations_ft class

/* End of file ft.morematrixrelations.php */
/* Location: ./system/expressionengine/third_party/morematrixrelations/ft.morematrixrelations.php */