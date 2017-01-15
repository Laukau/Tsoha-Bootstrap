<?php

class UserController extends BaseController{
    
    public static function index(){
        self::check_logged_in();
        $users = User::all();
        View::make('user/index.html', array('users' => $users));
    }
    
    public static function show($id){
        self::check_logged_in();
        $user = User::find($id);
        $courses = Course::all();
        View::make('user/show.html', array('user' => $user, 'courses' => $courses));
    }
    
    public static function register(){
        View::make('user/registration.html');
    }
    
    public static function store(){
        $params = $_POST;
        
        $attributes = array(
           'student_name' => $params['student_name'],
            'username' => $params['username'],
            'password' => $params['password']
        );
        
        $user = new User($attributes);
        $errors = $user->errors();
        
        if(count($errors) == 0){
            $user->save();
            $user = User::authenticate($params['username'], $params['password']);
            $_SESSION['user'] = $user->id;
            Redirect::to('/user/' . $user->id, array('message' => 'Olet rekisteröitynyt palveluun!'));
        } else{
            View::make('user/registration.html', array('errors' => $errors, 'attributes' => $attributes));
        }
    }
    
    public static function edit($id){
        self::check_logged_in();
        
        $user_logged_in = self::get_user_logged_in();
        $user = User::find($id);
        
        if($user->id === $user_logged_in->id){
            View::make('user/edit.html', array('attributes' => $user));
        }else{
            Redirect::to('/user', array('error' => 'Et voi muokata toisen käyttäjän tietoja'));
        }
    }
    
    public static function update($id){
        self::check_logged_in();
        
        $user_logged_in = self::get_user_logged_in();
        $params = $_POST;
        
        $attributes = array(
            'id' => $id,
            'student_name' => $params['student_name'],
            'username' => $params['username'],
            'password' => $params['password']
        );
        
        $user = new User($attributes);
        $errors = $user->errors();
        
        if($user->id !== $user_logged_in->id){
            Redirect::to('/user', array('message' => 'Et voi muokata toisen käyttäjän tietoja!'));
        }
        if(count($errors) == 0){
            $user->update();
            Redirect::to('/user/' . $user->id, array('message' => 'Tietoja on muokattu onnistuneesti!'));
        }else{
            View::make('/user/edit.html', array('errors' => $errors, 'attributes' => $attributes));
        }
    }
    
    public static function destroy($id){
        self::check_logged_in();
        
        $user_logged_in = self::get_user_logged_in();
        $user = new User(array('id' => $id));
        
        if($user === $user_logged_in){
            $user->destroy();
            Redirect::to('/registration', array('message' => 'Tietosi on poistettu palvelusta!'));
        }else{
            Redirect::to('/user', array('message' => 'Et voi poistaa toisen käyttäjän tietoja'));
        }
    }
    
    public static function login(){
        View::make('user/login.html');
    }
    
    public static function handle_login(){
        $params = $_POST;
        
        $user = User::authenticate($params['username'], $params['password']);
        
        if(!$user){
            View::make('user/login.html', array('error' => 'Väärä käyttäjätunnus tai salasana', 'username' => $params['username']));
        }else{
            $_SESSION['user'] = $user->id;
            
            Redirect::to('/', array('message' => 'Tervetuloa takaisin ' . $user->student_name . '!' ));
        }
    }
    
    public static function logout(){
        $_SESSION['user'] = null;
        Redirect::to('/login', array('message' => 'Olet kirjautunut ulos!'));
    }
}