<?php
class m_user extends CI_Model
{
	function __construct() {
		parent::__construct();
		$this->load->database(); 
	}
	function format($arr) {
		$this->load->helper("array");
		$arr = elements(
			array('email','password','username','location','website','profile','work','edu','proField','img'),$arr,NULL); 
		return $arr;
	}
	function getSimple($arr) {
		$this->db->select('ID,username,password');
		$this->db->from('user');
		$this->db->where($arr);
		
		return $this->db->get()->row_array();
	}
	function getAll($ID) {
		$this->db->select('*');
		$this->db->from('user');
		$this->db->where('ID',$ID);
		
		return $this->db->get()->row_array();
	}
	function insert($arr) {
		$arr = $this->format($arr);
		$this->db->insert('user',$arr);
	}
	function modify($ID,$arr) {
		$this->db->where('ID',$ID);
		$this->db->update('user',$arr);
	}
	function searchUser($arr) {
		$this->db->select('username,img,email');
		$this->db->from('user');
		$this->db->or_like($arr);
		
		return $this->db->get()->result_array();
	}
}	
?>