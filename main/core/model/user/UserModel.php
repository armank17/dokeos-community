<?php
require_once api_get_path( SYS_PATH ) . 'main/core/dao/user/UserDao.php';
class UserModel
{
    
    public static function create()
    {
        return new UserModel();
    } 
    
    public function createNewUser($request, &$session)
    {
        $sessionUserContainer = (isset($session['user_info'])) ? $session['user_info'] : (isset($session['student_info'])) ? $session['student_info'] : array();
        $this->lastname = $sessionUserContainer['lastname'];
        $this->firstname = $sessionUserContainer['firstname'];
        $this->country = $sessionUserContainer['country'];
        $this->civility  = $sessionUserContainer['civility'];
        $this->status = 5;
        $this->hash = api_generate_password( 3 );
        $this->password = $this->hash;
        $this->part1 = $this->firstname[0];
        $this->exp_lname = explode( ' ', $this->lastname );
        $this->part2 = (is_array( $this->exp_lname ) && count( $this->exp_lname ) > 1) ? $this->exp_lname[0] : $this->lastname;
        $this->genera_uname = strtolower( $this->part1 . $this->part2 . $this->hash );
        $this->genera_uname = replace_accents( $this->genera_uname );
        $this->username = $this->genera_uname;
        $this->email = $sessionUserContainer['email'];
        
        $this->user_id = api_get_user_id();
        
        // create new user if it doesn't exist
        if ( $this->_userId < 1 )
        {
            $this->user_id = UserDao::create()->createUser($this);
        }
        else
        {
            $this->user_id = UserDao::create()->updateUser($this);
        } 
            
        
        return $this;
    }
}