<?php
class DbOperation{
	
	private $con;
	
	function __construct () 
	{
		require_once dirname(__FILE__) . '/DbConnect.php';
		$db = new DbConnect();
		$this->con = $db->connect();
	}
	
	//Method to create a new user 
	function registerUser ($username, $email, $pass, $nik, $isuser, $isspv, $isadmin)
	{
		if (!$this->isUserExist($email)) {
			
			
			$password = md5($pass);
			$apikey = $this->generateApiKey();
			$stmt = $this->con->prepare("INSERT INTO users (username, email, password, nik, apikey, isuser, isspv, isadmin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssiii", $username, $email, $password, $nik, $apikey, $isuser, $isspv, $isadmin);
			if ($stmt->execute())
			return USER_CREATED;
			return USER_CREATION_FAILED;
		}
		return USER_EXIST;
	
		
	}
	

		
	
	//Method to create a new white form
	public function createWhiteForm($nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $duedate, $carapenanggulangan){
		
		if (!$this->isWhiteFormExist($nomorkontrol)) {
        $stmt = $this->con->prepare("INSERT INTO white_form (nomor_kontrol, bagian_mesin, dipasang_oleh, tgl_pasang, deskripsi, photo, due_date, cara_penanggulangan) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss",$nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $duedate, $carapenanggulangan);
        if ($stmt->execute())
		return WHITEFORM_CREATED;
		return WHITEFORM_CREATION_FAILED;
        
        }
	
	return WHITEFORM_EXIST;
	}
	
	//Method for user login
	
	function userLogin($email, $pass)
	{
		$password = md5($pass);
		$stmt = $this->con->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
		$stmt->bind_param("ss", $email, $password);
		$stmt->execute();
		$stmt->store_result();
		return $stmt->num_rows > 0;
	}
	
	
	//Method to send a message to another user
    function sendMessage($from, $to, $title, $message)
    {
        $stmt = $this->con->prepare("INSERT INTO messages (from_users_id, to_users_id, title, message) VALUES (?, ?, ?, ?);");
        $stmt->bind_param("iiss", $from, $to, $title, $message);
        if ($stmt->execute())
            return true;
        return false;
    }
	

	
	//Method to update profile of user
    function updateProfile($nik, $isverified)
    {
        
        $stmt = $this->con->prepare("UPDATE users SET isverified = ? WHERE nik = ?");
        $stmt->bind_param("ii", $isverified, $nik);
        if ($stmt->execute())
            return true;
            return false;
    }
	
	//Method to get user by email
	function getUserByEmail($email)
	{
	    
		$stmt = $this->con->prepare("SELECT id, username, email, nik, apikey, isuser, isspv, isadmin FROM users WHERE email = ?");
		
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($id, $username, $email, $nik, $apikey, $isuser, $isspv, $isadmin);
		$stmt->fetch();
		
		
		$user = array();
		$user["id"] = $id;
		$user["username"] = $username;
		$user["email"] = $email;
		$user["nik"] = $nik;
		$user["apikey"] = $apikey;
		$user["isuser"] = $isuser;
		$user["isspv"] = $isspv;
		$user["isadmin"] = $isadmin;
		return $user;
		
	}
	
	
	
	//Method to check a user is valid or not using apikey
	public function isValidUser($apikey) {
		$stmt = $this->con->prepare("SELECT id from users WHERE apikey = ?");
		$stmt->bind_para("s", $apikey);
		$stmt->execute();
		$stmt->store_result();
		$num_rows = $stmt->num_rows;
		$stmt->close();
		
		return $num_rows > 0;
	}

	
	//Method check email if exist
	function isUserExist($email)
	{
		$stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();
		return $stmt->num_rows > 0;
	}
	
	function isWhiteFormExist($nomorkontrol) {
		$stmt = $this->con->prepare("SELECT form_id FROM white_form WHERE nomor_kontrol = ?");
		$stmt->bind_param("s", $nomorkontrol);
		$stmt->execute();
		$stmt->store_result();
		return $stmt->num_rows > 0;
	}
	
	private function generateApiKey() {
		return md5(uniqid(rand(), true));
	}
	
	//Method to get all white form with status open, on process, close	
	
	function getAllStatusWhiteForm($open, $onprocess, $close) 
	{
		$stmt = $this->con->prepare("SELECT form_id, nomor_kontrol, bagian_mesin, dipasang_oleh, tgl_pasang, deskripsi, photo, due_date, cara_penanggulangan, status FROM white_form WHERE status = ? OR  status = ? OR status = ?");
		$stmt->bind_param('sss', $open, $onprocess, $close);
		$stmt->execute();
		$stmt->bind_result($formid, $nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $duedate, $carapenanggulangan, $status);
		$users = array();
		while ($stmt->fetch()) {
			$temp = array();
			$temp["formid"] = $formid;
			$temp["nomor_kontrol"] = $nomorkontrol;
			$temp["bagian_mesin"] = $bagianmesin;
			$temp["dipasang_oleh"] = $dipasangoleh;
			$temp["tgl_pasang"] = $tglpasang;
			$temp["deskripsi"] = $deskripsi;
			$temp["photo"] = $photo;
			$temp["due_date"] = $duedate;
			$temp["cara_penanggulangan"] = $carapenanggulangan;
			$temp["status"] = $status;
			
			array_push($users, $temp);
			
		}
		return $users;
	}
	
	
	//Method to get all red form with status open, on process, close	
	
	function getAllStatusRedForm($open, $onprocess, $close) 
	{
		$stmt = $this->con->prepare("SELECT form_id, nomor_kontrol, bagian_mesin, dipasang_oleh, tgl_pasang, deskripsi, photo, nomor_work_request, pic_follow_up, due_date, cara_penaggulangan, status FROM red_form WHERE status = ? OR  status = ? OR status = ?");
		$stmt->bind_param('sss', $open, $onprocess, $close);
		$stmt->execute();
		$stmt->bind_result($formid, $nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $nomorworkrequest, $picfollowup, $duedate, $carapenanggulangan, $status);
		$users = array();
		while ($stmt->fetch()) {
			$temp = array();
			$temp["form_id"] = $formid;
			$temp["nomor_kontrol"] = $nomorkontrol;
			$temp["bagian_mesin"] = $bagianmesin;
			$temp["dipasang_oleh"] = $dipasangoleh;
			$temp["tgl_pasang"] = $tglpasang;
			$temp["deskripsi"] = $deskripsi;
			$temp["photo"] = $photo;
			$temp["nomor_work_request"] = $nomorworkrequest;
			$temp["pic_follow_up"] = $picfollowup;
			$temp["due_date"] = $duedate;
			$temp["cara_penanggulangan"] = $carapenanggulangan;
			$temp["status"] = $status;
			
			array_push($users, $temp);
			
		}
		return $users;
	}
	
	
	//Method to get all white form with status close	
	
	function getAllCloseWhiteForm($close) 
	{
		$stmt = $this->con->prepare("SELECT form_id, nomor_kontrol, bagian_mesin, dipasang_oleh, tgl_pasang, deskripsi, photo, due_date, cara_penanggulangan, status FROM white_form WHERE status = ?");
		$stmt->bind_param('s', $close);
		$stmt->execute();
		$stmt->bind_result($formid, $nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $duedate, $carapenanggulangan, $status);
		$users = array();
		while ($stmt->fetch()) {
			$temp = array();
			$temp["formid"] = $formid;
			$temp["nomor_kontrol"] = $nomorkontrol;
			$temp["bagian_mesin"] = $bagianmesin;
			$temp["dipasang_oleh"] = $dipasangoleh;
			$temp["tgl_pasang"] = $tglpasang;
			$temp["deskripsi"] = $deskripsi;
			$temp["photo"] = $photo;
			$temp["due_date"] = $duedate;
			$temp["cara_penanggulangan"] = $carapenanggulangan;
			$temp["status"] = $status;
			
			array_push($users, $temp);
			
		}
		return $users;
	}
	
	//Method to get all white form with status close	
	
	function getAllCloseRedForm($close) 
	{
		$stmt = $this->con->prepare("SELECT form_id, nomor_kontrol, bagian_mesin, dipasang_oleh, tgl_pasang, deskripsi, photo, nomor_work_request, pic_follow_up due_date, cara_penanggulangan, status FROM red_form WHERE status = ?");
		$stmt->bind_param('s', $close);
		$stmt->execute();
		$stmt->bind_result($formid, $nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $nomorworkrequest, $picfollowup, $duedate, $carapenanggulangan, $status);
		$users = array();
		while ($stmt->fetch()) {
			$temp = array();
			$temp["formid"] = $formid;
			$temp["nomor_kontrol"] = $nomorkontrol;
			$temp["bagian_mesin"] = $bagianmesin;
			$temp["dipasang_oleh"] = $dipasangoleh;
			$temp["tgl_pasang"] = $tglpasang;
			$temp["deskripsi"] = $deskripsi;
			$temp["photo"] = $photo;
			$temp["nomor_work_request"] = $nomorworkrequest;
			$temp["pic_follow_up"] = $picfollowup;
			$temp["due_date"] = $duedate;
			$temp["cara_penanggulangan"] = $carapenanggulangan;
			$temp["status"] = $status;
			
			array_push($users, $temp);
			
		}
		return $users;
	}
	
	
	//Method to get all user	
	
	function getAllUsers() 
	{
		$stmt = $this->con->prepare("SELECT id, username FROM users");
		$stmt->execute();
		$stmt->bind_result($id, $username);
		$users = array();
		while ($stmt->fetch()) {
			$temp = array();
			$temp["id"] = $id;
			$temp["username"] = $username;
			array_push($users, $temp);
			
		}
		return $users;
	}
	
	
}


?>