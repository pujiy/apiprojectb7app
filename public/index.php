<?php
   //php 7.3.0
   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
   
   require '../vendor/autoload.php';
   require_once '../includes/DbOperation.php';

   $app = new \Slim\App([
     'settings' => [
     'displayErrorDetails' => true ]
     ]);
     
		 
     //registering new user
     $app->post('/register', function(Request $request, Response $response )
     {
       if (isTheseParameterAvailable(array('username', 'email', 'password', 'nik', 'isuser', 'isspv', 'isadmin'))) {
         $requestData = $request->getParsedBody();
         $username = $requestData['username'];
         $email = $requestData['email'];
         $password = $requestData['password'];
         $nik = $requestData['nik'];
         $isuser = $requestData['isuser'];
		 $isspv = $requestData['isspv'];
		 $isadmin = $requestData['isadmin'];
         $db = new DbOperation();
         $responseData = array();
         
         $result = $db->registerUser($username, $email, $password, $nik, $isuser, $isspv, $isadmin);
           if ($result == USER_CREATED) {
			 
			 $secret = "35onoi2=-7#%g03kl";
			 $hash = md5($nik.$secret);
			 $link = "http://example.com/confirmation/$nik/$hash";
			 
			 
				$mail = new PHPMailer();
			 
				$mail->SingleTo = true;
				$mail->isSMTP();
				$mail->Host = 'smtp.gmail.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'apayah90@gmail.com';
				$mail->Password = '*****';
				$mail->SMTPSecure = 'tls';
				$mail->Port = 587;  
				$mail->setFrom("verify@b7app.com", "noreply@b7appverify.com");
				$mail->addAddress($email);
				$mail->isHTML(true);

				$mail->Subject = "Test PHP MAILER";
				$mail->Body = "<p>To confirm the e-mail please click this link to activate your account $link</p>";
				$mail->AltBody = "PHPMailer GMail SMTP test altbody";
				$send = $mail->send();  
			 
			 
            $responseData['error'] = false;
            $responseData['message'] = "Please confirmation your email";
             
           }
           elseif ($result == USER_CREATION_FAILED) {
             
           
           $responseData['error'] = true;
           $responseData['message'] = "Some error occurred";
           
           }
           elseif ($result == USER_EXIST) {
             $responseData['error'] = true;
             $responseData['message'] = "This email already registered";
           }
           
           $response->getBody()->write(json_encode($responseData));
      
      
      
      
      
       }
     });
	 
	 

	 
	 //send email
	 $app->post('/sendemail', function(Request $request, Response $response) {
		 if(isTheseParameterAvailable(array('email_to', 'subject_mail'))) {
			 $requestData = $request->getParsedBody();
			 $emailTo = $requestData['email_to'];
			 $subjectMail = $requestData['subject_mail'];
			$mail = new PHPMailer();
				 
			$mail->SingleTo = true;
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->Username = 'apayah90@gmail.com';
			$mail->Password = 'terserah96';
			$mail->SMTPSecure = 'tls';
			$mail->Port = 587;  
			$mail->setFrom("verify@b7app.com", "First Last");
			$mail->addAddress($emailTo);
			$mail->isHTML(true);

			$mail->Subject = $subjectMail;
			$mail->Body = "<p>PHPMailer GMail SMTP test body</p>";
			$mail->AltBody = "PHPMailer GMail SMTP test altbody";
			$send = $mail->send();
		 
		 $responseData = array();
		 
		 
		 if (!$send) {
		$responseData['error'] = true;
           $responseData['message'] = "Error";
			 
		 }
		 
		 else {
		   $responseData['error'] = false;
             $responseData['message'] = "Send mail success";
		 }
		 
		  $response->getBody()->write(json_encode($responseData));
	 }
	 
	 });
	 
	 //registering new white form
     $app->post('/newwhiteform', function(Request $request, Response $response )
     {
       if (isTheseParameterAvailable(array('nomor_kontrol', 'bagian_mesin', 'dipasang_oleh', 'tgl_pasang', 'deskripsi', 'photo', 'due_date', 'cara_penanggulangan'))) {
         $requestData = $request->getParsedBody();
         $nomorkontrol = $requestData['nomor_kontrol'];
         $bagianmesin = $requestData['bagian_mesin'];
         $dipasangoleh = $requestData['dipasang_oleh'];
         $tglpasang = $requestData['tgl_pasang'];
         $deskripsi = $requestData['deskripsi'];
		 $photo = $requestData['photo'];
		 $duedate = $requestData['due_date'];
		 $carapenanggulangan = $requestData['cara_penanggulangan'];
         $db = new DbOperation();
         $responseData = array();
         
         $result = $db->createWhiteForm($nomorkontrol, $bagianmesin, $dipasangoleh, $tglpasang, $deskripsi, $photo, $duedate, $carapenanggulangan);
           
           if ($result == WHITEFORM_CREATED) {
             $responseData['error'] = false;
             $responseData['message'] = "Register whiteform successfully";
             
           }
           elseif ($result == WHITEFORM_CREATION_FAILED) {
             
           
           $responseData['error'] = true;
           $responseData['message'] = "Some error occurred";
           
           }
           elseif ($result == WHITEFORM_EXIST) {
             $responseData['error'] = true;
             $responseData['message'] = "This whiteform already registered";
           }
           
           $response->getBody()->write(json_encode($responseData));
      
      
      
      
      
       }
     });
     
     //User login route
     $app->post('/login', function ( Request $request, Response $response) {
         if(isTheseParameterAvailable(array('email', 'password'))) {
             $requestData = $request->getParsedBody();
             $email = $requestData['email'];
             $password = $requestData['password'];

             $db = new DbOperation;

            
             $responseData = array();

             if($db->userLogin($email, $password)) {
                 
                 $responseData['error'] = false;
                 $responseData['user'] = $db->getUserByEmail($email);
             }

             else {
                 $responseData['error'] = true;
                 $responseData['message'] = "Email atau Password Salah";
             }
			 

             $response->getBody()->write(json_encode($responseData));
         }
     });
	 
	 
	 	 

     //getting all user
     $app->get('/users', function (Request $request, Response $response) {
         $db = new DbOperation;
         $users = $db->getAllUsers();
         $response->getBody()->write(json_encode(array("users" => $users)));
     });

     // getting all users leaderboard
     $app->get('/usersleaderboard', function (Request $request, Response $response) {
	
	 $db = new DbOperation;
	 $users = $db->getAllUsersLeaderboard();
	
	 $response->getBody()->write(json_encode(array("error" => "false", "users" => $users)));
	     
     });
     
     //getting message for a user
     $app->get('/messages/{id}', function (Request $request, Response $response) {
         $userid = $request->getAttribute('id');
         $db = new DbOperation();
         $messages = $db->getMessage($userid);
         $response->getBody()->write(json_encode(array("messages" => $messages)));
     });


	$app->get('/hello/{name}/{level}', function($request, $response) {
    $name = $request->getAttribute('name');
	$level = $request->getAttribute('level');
    $response->getBody()->write("Hello, $name, $level");

    return $response;
});


//send confirmation
$app->get('/confirmation/{nik}/{hash}', function(Request $request, Response $response) {
	
	$id = $request->getAttribute('nik');
	$hash = $request->getAttribute('hash');
	$secret = '35onoi2=-7#%g03kl';
	$isverified = '1';

             $db = new DbOperation();

			 
		if(md5($id.$secret) == $hash) {
            $db->updateProfile($id, $isverified);
                 echo "Email confirmation successfully";
                 
		}
             
             else {
                 echo "Email confirmation failed";
             }

             
         
});


     //updating a user
     $app->post('/update/{id}', function (Request $request, Response $response){
         if (isTheseParameterAvailable(array('name', 'email', 'password', 'gender'))) {
             $id = $request->getAttribute('id');

             $requestData = $request->getParsedBody();

             $name = $requestData['name'];
             $email = $requestData['email'];
             $password = $requestData['password'];
             $gender = $requestData['gender'];

             $db = new DbOperation();

             $responseData = array();

             if($db->updateProfile($id, $name, $email, $password, $gender)) {
                 $responseData['error'] = false;
                 $responseData['message'] = 'Update successfully';
                 $responseData['user'] = $db->getUserByEmail($email);
             }
             else {
                 $responseData['error'] = true;
                 $responseData['message'] = 'Not update';
             }

             $response->getBody()->write(json_encode($responseData));
         }
     });

     //sending message to user
     $app->post('/sendmessage', function(Request $request, Response $response){
         if(isTheseParameterAvailable(array('from', 'to', 'title', 'message'))) {
             $requestData = $request->getParsedBody();
             $from = $requestData['from'];
             $to = $requestData['to'];
             $title = $requestData['title'];
             $message = $requestData['message'];

             $db = new DbOperation();

             $responseData = array();

             if($db->sendmessage($from, $to, $title, $message)) {
                 $responseData['error'] = false;
                 $responseData['message'] = 'Message sent successfully';
             } else {
                 $responseData['error'] = true;
                 $responseData['message'] = 'Could not send message';
             }

             $response->getBody()->write(json_encode($responseData));

             
         }
     });

     //function to check parameters
     function isTheseParameterAvailable($required_fields)
     {
         $error = false;
         $error_fields = "";
         $request_params = $_REQUEST;

         foreach($required_fields as $field) {
             if(!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
                 $error = true;
                 $error_fields .= $field . ',';
             }
         }

         if($error) {
             $response = array();
             $response["error"] = true;
             $response["message"] = 'Required field(s)' . substr($error_fields, 0, -2) . ' is missing or empty';
             echo json_encode($response);
             return false;
            }

            return true;
     }
     
     
     
$app->run();
