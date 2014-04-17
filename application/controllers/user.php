<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class user extends CI_Controller
{
	private $loginUser;
	
	function __construct() {
		parent::__construct();
		$this->load->library('session');
		$this->load->model('m_user');
		$this->loginUser = $this->session->userdata('ID');
	}
	function getLoginUser() {
		return $this->loginUser;
	}
	function isLogin() {
		if ($this->loginUser) {
			$result = $this->m_user->getSimple(Array('ID'=>$this->loginUser));
			echo json_encode(Array('state'=>True,'username'=>$result['username']));
		} else
			echo json_encode(Array('state'=>False));
	}
	function login() {
		if ($this->loginUser) return;
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		
		$result = $this->m_user->getSimple(Array('email'=>$email));
		if ($password != $result['password']) {
			echo json_encode(Array('state'=>False)); 
		} else {
			echo json_encode(Array('state'=>True,'username'=>$result['username']));
			$this->session->set_userdata(Array('ID' => $result['ID'])); 
			$this->loginUser = $result['ID'];
		}
	}
	function logout() {
		//$username = $this->session->userdata('email');
		//if ($username) echo $username; else echo "Not Exist.";
		$this->session->sess_destroy();
	}
	//以下两函数供AJAX验证调用
	function isExist() {		
		$_POST = $this->input->post();
		
		$result = $this->m_user->getSimple(Array('email'=>$_POST['fieldValue']));
		$bool = True;
		if ($result) {
			$bool = False;
		}
		
		echo json_encode(Array($_POST['fieldId'],$bool));
	}
	function isPwdCorrect() {
		$_POST = $this->input->post();
		
		$result = $this->m_user->getSimple(Array('ID'=>$this->loginUser));
		$bool = True;
		if ($result['password'] != $_POST['fieldValue']) {
			$bool = False;
		}
		
		echo json_encode(Array($_POST['fieldId'],$bool));
	}	
	function register() {		
		$_POST = $this->input->post();
		
		if ($_POST) {
			if ($this->m_user->getSimple(Array('email'=>$_POST['email']))) return;
			
			$_POST['username'] = $_POST['email'];
			$_POST['img'] = '/uploads/portrait/default-user.jpg';
			$_POST['work'] = '[]';
			$_POST['edu'] = '[]';
			$_POST['proField'] = '[]';
		
			$this->m_user->insert($_POST);
			echo json_encode(True);
		} else
			$this->load->view('user/register.html');
	}
	function modify() {
		if (!$this->loginUser) return;
		$_POST = $this->input->post();
		
		if ($_POST) {
			//这里需要补充验证数据合法性
			$this->m_user->modify($this->loginUser,$_POST);
			echo json_encode(True);
		} else {
			$result = $this->m_user->getAll($this->loginUser);
			$this->load->library('parser');
			$this->parser->parse('user/info.html',$result);
		}
	}
	function upload() {
		//图像上传
		$configUpload['upload_path'] = 'uploads/portrait';
		$configUpload['allowed_types'] = 'gif|jpg|png|jpeg';
		$configUpload['file_name'] = (string)$this->loginUser;
		$configUpload['overwrite'] = True;
		//$configUpload['max_size'] = '100';
		
		$this->load->library('upload',$configUpload);
		$this->upload->do_upload('file'); //前端的文件域为file
		$uploadData = $this->upload->data();
		
		//图像裁剪
		$configImg['source_image'] = 'uploads/portrait/'.$uploadData['orig_name'];
		$configImg['maintain_ratio'] = TRUE; //保持图像横纵比例
		$configImg['width'] = 40;
		$configImg['height'] = 40;
		
		$this->load->library('image_lib', $configImg);
		$this->image_lib->resize();
		
		echo json_encode('/uploads/portrait/'.$uploadData['orig_name']);
	}
	function searchUser($arr) {
		$result = $this->m_user->searchUser($arr);
		return $result;
	}
}
?>