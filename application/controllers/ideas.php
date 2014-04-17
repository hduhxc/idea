<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ideas extends CI_Controller
{	
	const LIMIT_NUM = 10;
	const PER_PAGE = 30;
	
	private $_userID;
	private $_isJoin;
	private $_user;
	
	function __construct() {
		parent::__construct();
		$this->load->model('m_ideas');
		$this->load->library('parser');
		$this->load->library('pagination');

		require_once("user.php");
		if (class_exists('user')) {
			$this->_user = new user();
			$this->_userID = $this->_user->getLoginUser();
		}
	}
	function publish() {
		if (!$this->_userID) {
			show_error('十有八九是因为你还没登录',500,'玩脱了');
			return;
		}
		
		$this->load->helper('file');
		$_POST = $this->input->post();
		
		if ($_POST) {
			$textUrl = 'uploads/ideas/'.rand();
			write_file($textUrl,$_POST['text']);
			$_POST['textUrl'] = $textUrl;	
			
			//注意这里需要修改
			$_POST['partner'] = json_encode(Array($this->_userID));
			
			//写入服务器时间
			$this->load->helper('date');
			$dateString = "%Y-%m-%d";
			$_POST["date"] = mdate($dateString);
			
			$result = $this->m_ideas->insert($_POST);
			echo $result["LAST_INSERT_ID()"];
		} else
			$this->load->view('ideas/publish.html');
	}
	//将JSON字符串转换为便于阅读的形式
	function tagDecode($arr) {
		$result = "";
		foreach ($arr as $item) {
			$result .= $item .' | ';
		}
		return $result;
	}
	function detail($ID) {
		$data = $this->m_ideas->getByID($ID);
		$data["id"] = $ID;
		$data["inDetail"] = $this->load->file($data["textUrl"],true);
		
		//Idea标签和合伙人标签
		$data["projectTag"] = $this->tagDecode(json_decode($data["projectTag"]));
		$data["partnerTag"] = $this->tagDecode(json_decode($data["partnerTag"]));
		
		//Idea参与者信息
		$partner = json_decode($data["partner"]);
		$userData = Array();
		foreach ($partner as $index) {
			$result = $this->m_ideas->getUser($index);
			array_push($userData,Array("img" => $result["img"],"username" => $result["username"]));
		}
		$data["user"] = $userData;
		
		$this->parser->parse('ideas/detail.html',$data);
	}
	function submitScore() {
		$_POST = $this->input->post();
		$this->m_ideas->score($_POST['ID'],$_POST['score']);
	}
	function search() {
		$_GET = $this->input->get();
		
		$result = $this->m_ideas->search($_GET["text"]);
		for ($i = 0; $i < count($result); $i++)
			$result[$i]['projectTag'] = $this->tagDecode(json_decode($result[$i]['projectTag']));
		$data = Array("ideas"=>$result);
		$data["rowNum"] = count($result);
		
		$this->load->library('parser');
		$this->parser->parse('ideas/search.html',$data);
	}
	function index() {
		$result = $this->m_ideas->getByIndex(self::LIMIT_NUM,0);
		for ($i = 0; $i < count($result); $i++)
			$result[$i]['projectTag'] = $this->tagDecode(json_decode($result[$i]['projectTag']));
		$data = Array('ideas'=>$result);
		
		$this->parser->parse('index.html',$data);
	}
	function isJoin($ID) {
		if (!$this->_userID) {
			echo json_encode(true);
			return;
		}
		$result = $this->m_ideas->getByID($ID);
		$partner = json_decode($result["partner"]);
		foreach ($partner as $index) {
			if ($index == $this->_userID) {
				echo json_encode(True);
				$this->_isJoin = True;
				return;
			}
		}
		echo json_encode(False);
		$this->_isJoin = False;
	}
	function addNewPartner($ID) {
		if (!$this->_userID) return;
		if ($this->_isJoin) return;
		
		$result = $this->m_ideas->getByID($ID);
		$partner = json_decode($result["partner"]);
		array_push($partner,$this->_userID);
		$partner = json_encode($partner);
		
		$arr = Array("partner"=>$partner);
		$this->m_ideas->modify($ID,$arr);
		$this->_isJoin = True;
		
		$userInfo = $this->m_ideas->getUser($this->_userID);
		echo json_encode($userInfo);
	}
	function myIdea() {
		if (!$this->_userID) {
			show_error('十有八九是因为你还没登录',500,'玩脱了');
			return;
		}
		
		$result = $this->m_ideas->getByUserID($this->_userID);
		for ($i = 0; $i < count($result); $i++)
			$result[$i]['projectTag'] = $this->tagDecode(json_decode($result[$i]['projectTag']));
		$data = Array('ideas'=>$result);
		
		$this->parser->parse('ideas/myIdea.html',$data);
	}
	function displayAll($offset = 0) {
		$config['base_url'] = '/index.php/ideas/displayAll';
		$config['total_rows'] = (int)$this->m_ideas->getCount()->row_num;
		$config['per_page'] = self::PER_PAGE;
		
		//自定义样式
		$config['first_link'] = False;
		$config['last_link'] = False;
		$config['prev_link'] = False;
		$config['next_link'] = False;
		$config['cur_tag_open'] = '<li class="active"><span>';
		$config['cur_tag_close'] = '</span></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		
		$this->pagination->initialize($config);
		$pageList = $this->pagination->create_links();
	
		$result = $this->m_ideas->getByIndex(self::PER_PAGE,$offset);
		for ($i = 0; $i < count($result); $i++)
			$result[$i]['projectTag'] = $this->tagDecode(json_decode($result[$i]['projectTag']));
		
		$data = Array("ideas" => $result,
					  "pageList" => $pageList);
		$this->parser->parse('ideas/displayAll.html',$data);
	}
	function userList() {
		$_GET = $this->input->get();
		$arr = Array('username'=>$_GET['queryStr'],'email'=>$_GET['queryStr']);
		$result = $this->_user->searchUser($arr);
		
		$this->parser->parse('ideas/userlist.html',Array('user'=>$result));
	}
}
?>
