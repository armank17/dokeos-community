<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/user/UserModel.php';
class UserDao
{

    public static function create()
    {
        return new UserDao();
    }
    
    public function createUser(UserModel $user)
    {
        $response = NULL;
        $idUser = UserManager::create_user( $user->firstname, $user->lastname, $user->status, $user->email, $user->username, $user->password ,
                '', '', $user->phone, '', PLATFORM_AUTH_SOURCE, '0000-00-00 00:00:00' , 1,
                0,null,$user->country , $user->civility);
        
        if( $idUser !== FALSE )
        {       
            $response  = $idUser;
        }
        
        return $response;
    }
    
    public function updateUser(UserModel $user)
    {
        
    }

}
