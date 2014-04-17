<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class comment extends CI_Controller
{
	function __construct() {
		parent::__construct();
		$this->load->model('m_comment');
		$this->load->library('parser'); //模板解析器类
	}
	function add() {		
		//从user类获取登录信息
		require_once("user.php");
		if (class_exists('user')) {
			$user = new user();
			$userID = $user->getLoginUser();
			if (!$userID) return;
		}
		
		//写入服务器时间
		$this->load->helper('date');
		$time = unix_to_human(time(),FALSE,'eu'); //不显示秒数，采用欧洲格式(24小时制)
		$_POST = $this->input->post();
		$_POST['date'] = $time;
		
		$_POST["userID"] = $userID;
		$this->m_comment->insert($_POST);
		
		//利用模板生成信息
		$arrUser = $this->m_comment->getByUserID($_POST['userID']);
		$data = array('comment'=>array($arrUser + $_POST));
		$this->parser->parse('ideas/comment.html',$data);
	}
	function displayAll($ID) {
		$arrComment = $this->m_comment->getByProjectID($ID);
		$data = array('comment'=>$arrComment);
		$this->parser->parse('ideas/comment.html',$data);
	}
}