<?php

require_once __DIR__.'/BaseService.class.php';
require_once __DIR__.'/../dao/UserDao.class.php';

class UserService extends BaseService{
    public function __construct(){
        parent::__construct(new UserDao());
    }

    public function get_user_by_username($username){
        return $this->dao->get_user_by_username($username);
    }

    public function get_user_by_id($userId){
        return $this->dao->get_user_by_id($userId);
    }

    public function get_login_attempts($username){
        return $this->dao->get_login_attempts($username);
    }

    public function update_login_attempts($username,$count){
        return $this->dao->update_login_attempts($username,$count);
    }

    public function get_user_by_phone($phone){
        return $this->dao->get_user_by_phone($phone);
    }

    public function get_user_by_email($email){
        return $this->dao->get_user_by_email($email);
    }

    public function verify_email($username){
        return $this->dao->verify_email($username);
    }

    public function verify_2fa($username){
      return $this->dao->verify_2fa($username);
    }

    public function unverify_2fa($username){
      return $this->dao->unverify_2fa($username);
    }

    public function change_password($password,$userId){
        return $this->dao->change_password($password,$userId);
    }

    public function set_reset_password_dateTime($userId){
        return $this->dao->set_reset_password_dateTime($userId);
    }

}
?>
