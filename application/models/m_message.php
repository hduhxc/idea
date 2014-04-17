<?php
class m_message extends CI_Model
{
	const NOT_READ = 0;
	const ALREADY_READ = 1;
	
	function __construct() {
		parent::__construct();
		$this->load->database(); 
	}
	function emailToID($email) {
		$this->db->select('ID');
		$this->db->from('user');
		$this->db->where('email',$email);
		
		return $this->db->get()->row_array();
	}
	function format($arr) {
		$this->load->helper("array");
		
		$arr = elements(
			array('ID','revUserID','sendUserID','date','content','state'),$arr); 
		return $arr;
	}
	function insert($arr) {
		$arr = $this->format($arr);
		$this->db->insert('message',$arr);
	}
	function changeState($userID) {
		$this->db->where('revUserID',$userID);
		$this->db->where('state',self::NOT_READ);
		$this->db->set('state',self::ALREADY_READ);
		$this->db->update('message');
	}
	function getAll($userID,$state) {
		$this->db->select('user.username,user.img,
						   message.content,message.date');
		$this->db->from('message');
		$this->db->join('user','message.sendUserID = user.ID AND message.revUserID = '.$userID.' AND message.state = '.$state);
		$this->db->order_by('message.date','ASC');
		
		return $this->db->get()->result_array();
	}
}
?>