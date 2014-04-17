<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class message extends CI_Controller
{	
	const NOT_READ = 0;
	const ALREADY_READ = 1;
	
	const HAVE_NOTHING = 0;
	const HAVE_NEWS = 1;
	
	const SLEEP_TIME = 1;
	const TRY_TIMES = 30;
	
	private $_userID;
	
	function __construct() {
		parent::__construct();
		$this->load->library('parser');
		$this->load->model('m_message');
		
		//获取发送者信息
		require_once("user.php");
		if (class_exists('user')) {
			$user = new user();
			$this->_userID = $user->getLoginUser();
		}
	}
	function sendMsg() {
		if (!$this->_userID) return;
		
		$_POST = $this->input->post();
		if (empty($_POST)) return;
		
		//写入发送者和接收者信息
		$result = $this->m_message->emailToID($_POST['revUserEmail']);
		$_POST['sendUserID'] = $this->_userID;
		$_POST['revUserID'] = $result['ID'];
		
		//写入服务器时间
		$this->load->helper('date');
		$time = unix_to_human(time(),True,'eu'); //显示秒数，采用欧洲格式(24小时制)
		$_POST['date'] = $time;
		
		//消息阅读状态
		$_POST['state'] = self::NOT_READ;
		
		$this->m_message->insert($_POST);
	}
	//长轮询状态检查
	function run() {
		if (!$this->_userID) return;
		
		$currentTime = time();
		for ($i = 0;$i < self::TRY_TIMES; $i ++) {
			$result = $this->m_message->getAll($this->_userID,self::NOT_READ);
			if (!empty($result)) {
				echo json_encode(True);
				return;
			}
			sleep(self::SLEEP_TIME);
		}
		echo json_encode(False);
	}
	function displayAll() {
		if (!$this->_userID) return;
		
		$data = Array();
		$data["notRead"] =  $this->m_message->getAll($this->_userID,0);
		$data["alreadyRead"] = $this->m_message->getAll($this->_userID,1);
		$this->parser->parse('message/displayAll.html',$data);
		
		$this->m_message->changeState($this->_userID);
	}
}
?>