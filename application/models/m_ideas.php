<?php
class m_ideas extends CI_Model
{
	function __construct() {
		parent::__construct();
		$this->load->database(); 
	}
	function format($arr) {
		$this->load->helper("array");
		$arr = elements(
			array('title','projectBrief','partnerBrief','textUrl','projectTag','partnerTag','partner','score','date'),$arr); 
		return $arr;
	}
	function insert($arr) {
		$arr = $this->format($arr);
		$this->db->insert('ideas',$arr);
		$result = $this->db->query('SELECT LAST_INSERT_ID()');
		return $result->row_array();
	}
	function getByID($ID) {
		$this->db->select('*');
		$this->db->from('ideas');
		$this->db->where('ID',$ID);
		
		return $this->db->get()->row_array(); //返回结果集的第一条
	}
	function getByUserID($userID) {
		$this->db->select('ID,score,title,projectBrief,projectTag');
		$this->db->from('ideas');
		$this->db->like('partner',$userID);
		
		return $this->db->get()->result_array();
	}
	function getCount() {
		$this->db->select('count(*) as row_num');
		$this->db->from('ideas');
		
		return $this->db->get()->row();
	}
	function getByIndex($limit,$offset) {
		$this->db->select('ID,score,title,projectBrief,projectTag');
		$this->db->from('ideas');
		$this->db->order_by('score','DESC');
		$this->db->limit($limit,$offset);
		
		return $this->db->get()->result_array();
	}
	function getUser($ID) {
		$this->db->select('img,username');
		$this->db->from('user');
		$this->db->where('ID = ',$ID);
		
		return $this->db->get()->row_array();
	}
	function score($ID,$score) {
		$this->db->select('score');
		$this->db->from('ideas');
		$this->db->where('ID = '.$ID);
		$temp = $this->db->get()->row_array();
		
		$score += $temp['score'];
		$scoreArr = Array('score'=>$score);
		
		$this->db->update('ideas', $scoreArr, "ID =".$ID);
	}
	function search($text) {
		$this->db->select('ID,score,title,projectBrief,projectTag');
		$this->db->from('ideas');
		$this->db->or_like('title',$text);
		$this->db->or_like('projectBrief',$text);
		$this->db->or_like('projectTag',$text);
		
		return $this->db->get()->result_array();
	}
	function modify($ID,$arr) {
		$this->db->where('ID',$ID);
		$this->db->update('ideas',$arr);
	}
}
?>
