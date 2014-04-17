<?php
class m_comment extends CI_Model
{
	function __construct() {
		parent::__construct();
		$this->load->database(); 
	}
	function format($arr) {
		$this->load->helper("array");
		$arr = elements(
			array('userID','projectID','date','content'),$arr); 
		return $arr;
	}
	function insert($arr) {
		$arr = $this->format($arr);
		$this->db->insert('comment',$arr);
	}
	function getByUserID($ID) {
		$this->db->select('username,img');
		$this->db->from('user');
		$this->db->where('ID',$ID);
		
		return $this->db->get()->row_array(); //返回结果集的第一条
	}
	function getByProjectID($ID) {
		$this->db->select('user.username,user.img,
						comment.date,comment.content');
		$this->db->from('comment');
		$this->db->join('user','user.id = comment.userID AND projectID = '.$ID);
		$this->db->order_by('date','ASC');
		
		return $this->db->get()->result_array();
	}
}
?>
