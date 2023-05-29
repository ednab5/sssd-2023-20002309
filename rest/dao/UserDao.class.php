<?php

require_once __DIR__.'/BaseDao.class.php';

class UserDao extends BaseDao{

    public function __construct(){
      parent::__construct("user");
    }

    public function get_user_by_username($username){
      return $this->query_unique("SELECT * FROM user WHERE username = :username", ['username' => $username]);
    }

    public function get_login_attempts($username){
      return $this->query_unique("SELECT loginAttempts FROM user WHERE username = :username", ['username' => $username]);
    }

    public function update_login_attempts($username,$count){
      return $this->query_unique("UPDATE user SET  loginAttempts = $count WHERE username = :username", ['username' => $username]);
    }

    public function get_user_by_phone($phone){
      return $this->query_unique("SELECT * FROM user WHERE phone = :phone", ['phone' => $phone]);
    }

    public function get_user_by_email($email){
      return $this->query_unique("SELECT * FROM user WHERE email = :email", ['email' => $email]);
    }

    public function get_user_by_id($userId){
      return $this->query_unique("SELECT * FROM user WHERE userId = :userId", ['userId' => $userId]);
    }


    public function verify_email($username){
      return $this->query_unique("UPDATE user SET verification_status = 1 WHERE username = :username", ['username' => $username]);
    }

    public function verify_2fa($username){
      return $this->query_unique("UPDATE user SET twofactorAuthStatus = 1 WHERE username = :username", ['username' => $username]);
    }

    public function unverify_2fa($username){
      return $this->query_unique("UPDATE user SET twofactorAuthStatus = 0 WHERE username = :username", ['username' => $username]);
    }

    public function set_reset_password_dateTime($userId){
      return $this->query_unique("UPDATE user SET reset_password_dateTime = CURRENT_TIMESTAMP WHERE userId = :userId", ['userId' => $userId]);
    }

    public function change_password($password,$userId){
      return $this->query_unique("UPDATE user SET password = '$password' WHERE userId = :userId", ['userId' => $userId]);
    }
    
   /*  public function add_element($registerUser){
    
      return $this->query_unique("INSERT INTO user (username, password, phone, secret, email) VALUES ('$username', '$password', '$phone', '$secret', '$email')");
    } */
}
?>
