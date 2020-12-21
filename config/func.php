<?php
/**
 * Created by PhpStorm.
 * User: Tech4all
 * Date: 12/21/20
 * Time: 1:18 PM
 */

function base_url($url = ""){
    if (empty($url)){
        return HOME_DIR;
    }else{
        return HOME_DIR.$url;
    }
}

function page_title($page_title = ""){
    if (empty($page_title)){
        return WEB_TITLE;
    }else{
        return $page_title." &dash; ".WEB_TITLE;
    }
}

function image_url($src){
    return base_url('templates/images/'.$src);
}

function set_flash ($msg,$type){
    $_SESSION['flash'] = '<div class="alert alert-'.$type.' alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>'.$msg.'</div>';
}

function flash(){
    if (isset($_SESSION['flash'])) {
        echo $_SESSION['flash'];
        unset($_SESSION['flash']);
    }
}

function redirect($url){
    header("location:$url");
    exit();
}

function is_login(){
    if (!isset($_SESSION['loggedin'])){
        return 0;
    }else{
        return 1;
    }
}

function admin_details($value){
    global $db;
    $username = $_SESSION[USER_SESSION_HOLDER]['username'];
    $sql = $db->query("SELECT * FROM ".DB_PREFIX."admin WHERE username='$username'");
    $rs = $sql->fetch(PDO::FETCH_ASSOC);
    return $rs[$value];
}