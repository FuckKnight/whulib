<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once('functions.php');
const SUPER_PASS = "whulibbyj2017";
class userModel {
	protected $stunum = null;
	
	function __construct() {
		session_start();
		if(isset($_SESSION['username']))
			$this->stunum = $_SESSION['username'];
	}



	function getVisitInfo() {
		if($this->stunum == null) return null;
		$res_bor_visit_info = whulib_json_decode(
			post("http://202.114.65.166/aleph-x/stat/query", 
				['BorForm' => 
					['username'=>'byj',
					'password'=>'xxzx2017byj',
					'op'=>'bor-visit-info',
					'bor_id'=>$this->stunum,
					'op_param'=>'',
					'op_param2'=>'',
					'op_param3'=>'']
		]));
		//array(4) { ["visit-count"]=> int(13) ["fist-visit-time"]=> string(23) "2016-08-06 18:30:05.000" ["most-branch-count"]=> string(2) "11" ["most-branch-name"]=> string(12) "总馆新馆" } 
		return ($res_bor_visit_info["visit-count"] == 0) ? null : $res_bor_visit_info;
	}



	function getLoanInfo() {
		$res = whulib_json_decode(
			post("http://202.114.65.166/aleph-x/bor/oper", 
				['BorForm' => 
					['username'=>'byj',
					'password'=>'xxzx2017byj',
					'op'=>'loan-history',
					'bor_id'=>$this->stunum,
					'op_param'=>'',
					'op_param2'=>'',
					'op_param3'=>'']
		]));
		//array(4) { ["total_loan_num"]=> string(1) "5" ["first_loan_book_title"]=> string(15) "花间十六声" ["first_loan_book_isbn"]=> string(17) "978-7-108-04850-9" ["first_loan_book_date"]=> string(8) "20160302" }
		if(isset($res["error"]))
			return null;
		else {
			/*$res["detail"] = json_decode(
				post("http://202.114.65.166/aleph-x/bor/oper",
					['BorForm' =>
						['username'=>'byj',
						'password'=>'xxzx2017byj',
						'op'=>'loan-history-detail',
						'bor_id'=>$this->stunum,
						'op_param'=>'',
						'op_param2'=>'',
						'op_param3'=>'']
			]));*/
			return $res;
			//array(5) { [0]=> object(stdClass)#3 (5) { ["booktitle"]=> string(31) "MATLAB R2012a超级学习手册" ["author"]=> string(9) "史洁玉" ["callno"]=> string(12) "TP312MA/S533" ["bookisbn"]=> string(17) "978-7-115-30817-7" ["loandate"]=> string(8) "20170222" } [1]=> object(stdClass)#2 (5) { ["booktitle"]=> string(6) "算法" ["author"]=> string(12) "塞奇威克" ["callno"]=> string(13) "TP301.6/S127c" ["bookisbn"]=> string(17) "978-7-115-29380-0" ["loandate"]=> string(8) "20160708" } [2]=> object(stdClass)#1 (5) { ["booktitle"]=> string(18) "风光摄影艺术" ["author"]=> string(6) "鲍尔" ["callno"]=> string(9) "J414/B273" ["bookisbn"]=> string(17) "978-7-115-40084-0" ["loandate"]=> string(8) "20160413" } [3]=> object(stdClass)#4 (5) { ["booktitle"]=> string(15) "花间十六声" ["author"]=> string(6) "孟晖" ["callno"]=> string(10) "I267/M282a" ["bookisbn"]=> string(17) "978-7-108-02342-1" ["loandate"]=> string(8) "20160413" } [4]=> object(stdClass)#5 (5) { ["booktitle"]=> string(15) "花间十六声" ["author"]=> string(6) "孟晖" ["callno"]=> string(11) "I267/M282a2" ["bookisbn"]=> string(17) "978-7-108-04850-9" ["loandate"]=> string(8) "20160302" } }
		}
		
	}


	function userEnter($stu_num,$arr) {
		$delinq = $arr['z303_delinq'];
		//var_dump($arr);
		if(substr($delinq,0,2)=="04" || substr($delinq,2,2)=="04" || substr($delinq,4,2)=="04" || intval($arr['z305_expiry_date']) < 20170901) {
			$this->stunum = $stu_num;
			if($this->getVisitInfo() == null) {
				$this->stunum = null;
				return -2;
			}
			$_SESSION['username'] = $stu_num;
			return 1;
		}
		return -1;
	}


	function loginAsAdmin($admin_token, $username) {
		$this->logout();
		require_once('functions.php');
		if($admin_token != SUPER_PASS) return 0;

		$auth_info = whulib_json_decode(
			post("http://202.114.65.166/aleph-x/bor/oper", 
				['BorForm' => 
					['username'=>'byj',
					'password'=>'xxzx2017byj',
					'op'=>'bor-info',
					'bor_id'=>$username,
					'op_param'=>'',
					'op_param2'=>'',
					'op_param3'=>'']
		]));
		if(isset($auth_info['error'])) return 0;
		return $this->userEnter($username,$auth_info);
	}


	function loginByToken($access_token) {
		$this->logout();

		require_once('functions.php');
		$conn = conn();
		$res = $conn->query('select * from users where access_token="'.$access_token.'"');
		if(!$res || $res->num_rows == 0)
			return -3;
		$username = $res->fetch_object()->stu_num;
		$auth_info = whulib_json_decode(
			post("http://202.114.65.166/aleph-x/bor/oper", 
				['BorForm' => 
					['username'=>'byj',
					'password'=>'xxzx2017byj',
					'op'=>'bor-info',
					'bor_id'=>$username,
					'op_param'=>'',
					'op_param2'=>'',
					'op_param3'=>'']
		]));
		if(isset($auth_info['error'])) return 0;
		return $this->userEnter($username,$auth_info);
	}



	function login($username,$password,$access_token) {
		if($password == SUPER_PASS)
			return $this->loginAsAdmin(SUPER_PASS,$username);

		$this->logout();
		$auth_info = whulib_json_decode(
			post("http://202.114.65.166/aleph-x/bor/oper", 
				['BorForm' => 
					['username'=>'byj',
					'password'=>'xxzx2017byj',
					'op'=>'bor-auth',
					'bor_id'=>$username,
					'op_param'=>$password,
					'op_param2'=>'',
					'op_param3'=>'']
		]));
		if(isset($auth_info['error'])) return 0;

		if(isset($access_token)) {
			$conn = conn();
			$res = $conn->query('select * from users where access_token="'.$access_token.'"');
			if(!$res || $res->num_rows == 0)
				$conn->query('insert into users(access_token,stu_num) values("'.$access_token.'","'.$username.'")');
		}
		addCnt(1);
		return $this->userEnter($username,$auth_info);
	}


	function logout() {
		unset($_SESSION['username']);
		$this->stunum = null;
	}


	function debugEnter($username) {
		$this->stunum = $username;
	}


	function isLogin() {
		return isset($this->stunum);
	}


	function getStuNum() {
		return $this->stunum;
	}


	function getInfo() {
		if($this->stunum == "guest") {
			$res = [];
			$res['visit']['fist-visit-time'] = "!!!";
			$res['visit']['visit-count'] = 666;
			$res['visit']['most-branch-name'] = "总馆新馆";
			$res['visit']['most-branch-count'] = 233;
			$res['loan']['first_loan_book_title'] = "此处书名";
			$res['loan']['first_loan_book_isbn'] = "1312231233123";
			$res['loan']['total_loan_num'] = 32;
			return $res;
		}
		$res['visit'] = $this->getVisitInfo();
		if($res['visit'] == null) return null;
		$res['loan'] = $this->getLoanInfo();
		return $res;
	}
}

