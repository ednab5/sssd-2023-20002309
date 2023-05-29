
<?php

require_once __DIR__.'/../config_default.php';
require_once __DIR__.'/../../phpMailer/src/Exception.php';
require_once __DIR__.'/../../phpMailer/src/PHPMailer.php';
require_once __DIR__.'/../../phpMailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PragmaRX\Google2FA\Google2FA;


/**
 * @OA\Post(
 *     path="/register",
 *     summary="Adds a new user - with oneOf examples",
 *     description="Adds a new user",
 *     operationId="addUser",
 *     tags={"user"},
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="full name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="username",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="email",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="phone",
 *                     oneOf={
 *                     	   @OA\Schema(type="string"),
 *                     	   @OA\Schema(type="integer"),
 *                     }
 *                 ),
 *                 example={"username": "edna113", "password": "11351135", "email": "bogdanic.edna@gmail.com", "phone": ""}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User created"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Error"
 *     )
 * )
 */
Flight::route('POST /register', function(){
  $registerUser = Flight::request()->data->getData();
  $userExists = Flight::userService()->get_user_by_username($registerUser['username']);
  $phoneExists = Flight::userService()->get_user_by_phone($registerUser['phone']);
  $pwned = new \MFlor\Pwned\Pwned($apiKey = null);
  $pwnedoccurences = $pwned->passwords()->occurrences($registerUser['password']);

  if(isset($userExists['userId'])) {
    return Flight::json(["message"=>"User exists!"], 404);
  }
  if(isset($phoneExists['userId'])) {
    return Flight::json(["message"=>"Phone exists!"], 404);
  }

  if(strlen($registerUser['username'])<3) {
      return Flight::json(["message"=>"Username must be longer than 3 characters!"], 404);
  }
  if(preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $registerUser['username']) || preg_match('/^[^ ].* .*[^ ]$/', $registerUser['username'])) {
      return Flight::json(["message"=>"Username cannot contain any special characters or spaces!"], 404);
  }
  if(strlen($registerUser['password'])<9) {
    return Flight::json(["message"=>"Password must be longer than 8 characters!"], 404);
  }
  if($pwnedoccurences>0) {
    return Flight::json(["message"=> "This password has been seen " .$pwnedoccurences. " times before!"], 404);
  }
  if(!filter_var($registerUser['email'],FILTER_VALIDATE_EMAIL)) {
    return Flight::json(["message"=>"Email must have a valid format!"], 404);
  } 

  $google2fa = new Google2FA();
  $userSecret = $google2fa->generateSecretKey();
  $registerUser['secret'] = $userSecret;
  Flight::userService()->add_element($registerUser);
  return Flight::json(["message"=>$registerUser]);
    try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host='smtp.mailgun.org';
    $mail->SMTPAuth=true;
    $mail->Username='postmaster@sandbox5ea8037b67cc430c927766e635d264a5.mailgun.org';
    $mail->Password=Env::GMAILPASS();
    $mail->Port=587;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
   ));

      $mail->setFrom('postmaster@sandbox5ea8037b67cc430c927766e635d264a5.mailgun.org');
      $mail->addAddress($registerUser['email']);
      $mail->isHTML(true);
      $mail->Subject = 'Email Verification';
      $mail->Body = "<p>Dear user,</p><p>Please click on the link below to verify your email address:</p><p><a href='http://localhost/sssd-2023-20002309/verification.html?username=" . urlencode($registerUser['username']) . "'>http://localhost/sssd-2023-20002309/verification.html</a></p><p>With regards,</p>";
      $mail->send();
    }catch (Exception $e) {
     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }  
    });

/**
 * @OA\Post(
 *     path="/login",
 *     summary="Login with example user",
 *     description="User login",
 *     operationId="loginUser",
 *     tags={"user"},
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="username",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     type="string"
 *                 ),
 *                 example={"username": "edna113", "password": "11351135"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="JWT Token on successful response"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Wrong Password | User doesn't exist"
 *     )
 * )
 */
 Flight::route('POST /login', function(){
 $login = Flight::request()->data->getData();
 $user = Flight::userService()->get_user_by_username($login['username']);
 $attempts = Flight::userService()->get_login_attempts($login['username']);

 if(isset($user['userId'])){
   if($user['verification_status']==0) {
     return Flight::json(["message"=>"Unverified user"]);
   }
   else {
   if($user['password'] == $login['password']){

     if($attempts['loginAttempts'] > 4) {

       if(($login['recaptchaToken']) !== NULL){

         $ch = curl_init();
         $url = "https://www.google.com/recaptcha/api/siteverify";
         $data_array = array(
         'secret' => '6Lfg4ygmAAAAABg5FTVc50j9RWg8gDJYQy3txz_X',
       
           
           'response' => $login['recaptchaToken'],
         );

         $data = http_build_query($data_array);

         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

         $response = curl_exec($ch);
         $decoded  = json_decode($response);
         $array = get_object_vars($decoded);

         if($array['success'] === false){
           Flight::userService()->update_login_attempts($user['username'],$attempts['loginAttempts']+1);
           return Flight::json(["message"=>"Recaptcha is not valid!", "attempts"=>$attempts]);
         }

         curl_close($ch);
         Flight::userService()->update_login_attempts($user['username'],0);
         if($user['twofactorAuthStatus'] == 0) {
           Flight::json(array('username' => $login['username'],'password' => $login['password'], 'secret' => $user['secret'],"attempts"=>$attempts));
         }
         else {
           unset($user['password']);
           $jwt = JWT::encode($user, Env::JWTSECRET(), 'HS256');
           Flight::json(['token' => $jwt]);
         }
       } else {
       Flight::userService()->update_login_attempts($user['username'],$attempts['loginAttempts']+1);
       return Flight::json(["message"=>"Recaptcha is not valid!", "attempts"=>$attempts]);
     }
   } else {
     Flight::userService()->update_login_attempts($user['username'],0);
     if($user['twofactorAuthStatus'] == 0) {
       return Flight::json(array('username' => $login['username'],'password' => $login['password'], 'secret' => $user['secret'], 'message'=>true,"attempts"=>$attempts));
     }
     else {
       unset($user['password']);
       $jwt = JWT::encode($user, Env::JWTSECRET(), 'HS256');
      return Flight::json(['token' => $jwt,'message'=>true,'username' => $login['username'],'password' => $login['password'], 'secret' => $user['secret']]);
     }
   }
 }
 else {
   Flight::userService()->update_login_attempts($user['username'],$attempts['loginAttempts']+1);
   return Flight::json(["message"=>"Password invalid!", "attempts"=>$attempts]);
 }
 }
 }
 else{
   Flight::json(["message"=>"User with that username does not exist"]);
 }
 });

Flight::route('PUT /verifyemail', function(){
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_username($data['username']);
if(isset($user['userId'])){
  Flight::userService()->verify_email($user['username']);
}else{
  Flight::json(["message"=>"User with that username does not exist"]);
}
});

Flight::route('PUT /verify2fa', function(){
$google2fa = new Google2FA();
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_username($data['username']);
if(isset($user['userId'])){
  if(!$google2fa->verifyKey($user['secret'], $data['code'])){
    return Flight::json(["message" => "2FA Code is invalid"]);
  }
  Flight::userService()->verify_2fa($user['username']);
}else{
  Flight::json(["message"=>"User with that username does not exist"]);
}
});

Flight::route('PUT /verify2faSMS', function(){
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_username($data['username']);
if(isset($user['userId'])){
  if($data['inputCode']!=$data['verificationCode']){
    return Flight::json(["message" => "2FA Code is invalid"]);
  }
  Flight::userService()->verify_2fa($user['username']);
}else{
  Flight::json(["message"=>"User with that username does not exist"]);
}
});

Flight::route('PUT /unverify2fa', function(){
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_username($data['username']);
if(isset($user['userId'])){
  Flight::userService()->unverify_2fa($user['username']);
}else{
  Flight::json(["message"=>"User with that username does not exist"]);
}
});


Flight::route('POST /qr', function(){
$user = Flight::request()->data->getData();
$google2fa = new Google2FA();
$url = $google2fa->getQRCodeUrl(
  'localhost',
  $user['username'],
  $user['secret']
 );
 Flight::json(['url' => $url]);
});

Flight::route('GET /sms', function(){
$verificationCode = rand(100000, 999999);
$basic  = new \Vonage\Client\Credentials\Basic(Env::NEXMOID(), Env::NEXMOAPIKEY());
$client = new \Vonage\Client($basic);

$response = $client->sms()->send(
    new \Vonage\SMS\Message\SMS("38762077370", "SSSD",  'Your activation code is : ' . $verificationCode)
);
$message = $response->current();

if ($message->getStatus() == 0) {
    echo "The message was sent successfully\n";
    Flight::json(['code' => $verificationCode]);
} else {
  echo "The message failed with status: " . $message->getStatus() . "\n";
}
});

Flight::route('POST /forgotPasswordEmail', function(){
  $data = Flight::request()->data->getData();
  $user =  Flight::userService()->get_user_by_email($data['email']);
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host='smtp.mailgun.org';
    $mail->SMTPAuth=true;
    $mail->Username='postmaster@sandbox5ea8037b67cc430c927766e635d264a5.mailgun.org';
    $mail->Password=Env::GMAILPASS();
    $mail->Port=587;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
   ));

   $mail->setFrom('postmaster@sandbox5ea8037b67cc430c927766e635d264a5.mailgun.org');
   $mail->addAddress($data['email']);
   $mail->isHTML(true);
   $mail->Subject = 'Password Change Request';
   $mail->Body = "<p>Dear user,</p><p>Please click on the link below to change your password:</p><p><a href='http://localhost/sssd-2023-20002309/forgotPassword.html?id=" . urlencode($user['userId']) . "'>http://localhost/sssd-2023-20002309/forgotPassword.html</a></p><p>With regards,</p>";
   $mail->send();
      }catch (Exception $e) {
       echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return Flight::json(['message' =>"Message could not be sent. Mailer Error:" . $mail->ErrorInfo ]);
    }
    Flight::userService()->set_reset_password_dateTime($user['userId']);
    return Flight::json(['message' =>"Successfully sent an email!"]);
});

Flight::route('PUT /forgotPassword', function(){
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_id($data['userId']);
if(isset($user['userId'])){
  if($data['oldPass']!=$user['password']){
    return Flight::json(["message" => "Incorrect old password!"]);
  }
  if($data['newPass']!=$data['confirmNewPass']){
    return Flight::json(["message" => "New password and confirm password are not the same!"]);
  }

  date_default_timezone_set('Europe/Berlin');
  $currentDateTime = date('Y-m-d H:i:s');
  $currentDateTimeObject = strtotime($currentDateTime);
  $userDateTimeObject = strtotime($user['reset_password_dateTime']);
  $time = $currentDateTimeObject - $userDateTimeObject;

  if($time > 300){
    return Flight::json(["message"=>"5 minutes limit has passed, please resend email!"]);
  }
  Flight::userService()->change_password($data['newPass'],$user['userId']);

  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host='smtp.gmail.com';
    $mail->SMTPAuth=true;
    $mail->Username='sssdtestingemail@gmail.com';
    $mail->Password=Env::GMAILPASS();
    $mail->Port=465;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
   ));

   $mail->setFrom('sssdtestingemail@gmail.com');
   $mail->addAddress($user['email']);
   $mail->isHTML(true);
   $mail->Subject = 'Password Change Confirmation';
   $mail->Body = "<p>Dear user,</p><p>you have successfully changed your password!";
   $mail->send();
      }catch (Exception $e) {
       echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return Flight::json(['message' =>"Message could not be sent. Mailer Error:" . $mail->ErrorInfo ]);
    }
    return Flight::json(['message' =>"Successfully changed password!"]);
  } else {
    Flight::json(["message"=>"User with that username does not exist"], 404);
  }
});

Flight::route('PUT /changePassword', function(){
$data = Flight::request()->data->getData();
$user = Flight::userService()->get_user_by_username($data['username']);
if(isset($user['userId'])){
  if($data['oldPass']!=$user['password']){
    return Flight::json(["message" => "Incorrect old password!"]);
  }
  if($data['newPass']!=$data['confirmNewPass']){
    return Flight::json(["message" => "New password and confirm password are not the same!"]);
  }

  Flight::userService()->change_password($data['newPass'],$user['userId']);

    return Flight::json(['message' =>"Successfully changed your password!"]);
  } else {
    Flight::json(["message"=>"User with that username does not exist"]);
  }
});

?>
