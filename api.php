<?php
/**
 * Created by PhpStorm.
 * User: Tech4all
 * Date: 1/19/21
 * Time: 8:45 AM
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE');
require_once 'config/core.php';

$data = $error = $ch_data = $msg_data2 = $msg_data = array();
$post_data = $_POST;

if (isset($_POST['login'])){
    $parent_id = $post_data['parent_id'];
    $password = $post_data['password'];

    $sql = $db->query("SELECT * FROM ".DB_PREFIX."parents WHERE parent_id='$parent_id' and password='$password'");
    $parent_info = $sql->fetch(PDO::FETCH_ASSOC);

    $parent_id2 = $parent_info['id'];
    $children = $db->query("SELECT s.*, c.name as class_name FROM ".DB_PREFIX."students
     s LEFT JOIN ".DB_PREFIX."class c 
        ON s.class_id = c.id
     WHERE s.parent_id='$parent_id2'");

    $parent_info['image'] = image_url($parent_info['image']);

    if ($sql->rowCount() == 0){
        $data['error'] = 0;
        $data['msg'] = "Invalid login details";
    }else{
        $data['error'] = 1;
        while ($children_data = $children->fetch(PDO::FETCH_ASSOC)){
            $ch_data[] = array(
                'id'=>$children_data['id'],
                'application_id'=>$children_data['application_id'],
                'image'=>image_url($children_data['image']),
                'fname'=>ucwords($children_data['fname']),
                'class_name'=>ucwords($children_data['class_name']),
                'term'=>term($children_data['term']),
                'academic_session'=>$children_data['academic_session'],
                'gender'=>ucwords($children_data['gender']),
                'birth'=>$children_data['birth']
            );
        }

        $msg = $db->query("SELECT * FROM ".DB_PREFIX."notifications ORDER BY id DESC");
        while ($info_msg = $msg->fetch(PDO::FETCH_ASSOC)){
            $msg_data[] = array(
                'decode_parent' => json_decode($info_msg['parent_json'],1),
                'id'=>$info_msg['id'],
                'subject'=>$info_msg['subject'],
                'message'=>$info_msg['message'],
                'created_at'=>$info_msg['created_at']
            );
        }

        if (is_array($msg_data) or count($msg_data) > 0){
            for ($i = 0; $i < count($msg_data); $i++){
                $decode_parent = $msg_data[$i]['decode_parent'];

                if (in_array($parent_id,$decode_parent)){
                    $msg_data2[] = array(
                        'id'=>$msg_data[$i]['id'],
                        'image'=>image_url('bell.jpg'),
                        'subject'=>ucwords($msg_data[$i]['subject']),
                        'message'=>$msg_data[$i]['message'],
                        'created_at'=>$msg_data[$i]['created_at']
                    );
                }

            }
        }
    }

    $info = array(
        'status'=>$data,
        'total_msg'=>$msg->rowCount(),
        'parent_data'=>$parent_info,
        'total_children'=>$children->rowCount(),
        'children_data'=>$ch_data,
        'notification'=>$msg_data2
    );

    echo json_encode($info);
    exit();
}

if (isset($_POST['attendance'])){
    $student_id = $_POST['student_id'];

    $sql = $db->query("SELECT * FROM ".DB_PREFIX."attendance WHERE student_id='$student_id'");
    if ($sql->rowCount() == 0){
        $data['error'] = 0;
        $data['msg'] = "No available attendance";
    }else{
        $data['error'] = 1;
        while ($rs = $sql->fetch(PDO::FETCH_ASSOC)){
            $data[] = array(
                'id'=>$rs['id'],
                'image'=>image_url('successful-payment.png'),
                'attendance'=>$rs['attendance'],
                'name'=>$rs['name'],
                'phone'=>$rs['phone'],
                'date'=>$rs['attendance_date']
            );
        }
    }

    echo json_encode($data);
    exit();
}


if (isset($_POST['school_fee'])){
    $student_id = $_POST['student_id'];

    $sql = $db->query("SELECT p.*, c.name as class_name  FROM ".DB_PREFIX."payment p 
    LEFT JOIN ".DB_PREFIX."class c 
        ON p.class_id = c.id    
    WHERE p.student_id='$student_id' ORDER BY p.id DESC");

    if ($sql->rowCount() == 0){
        $data['error'] = 0;
        $data['msg'] = "No payment for school fee yet";
    }else{
        $data['error'] = 1;
        while ($rs = $sql->fetch(PDO::FETCH_ASSOC)){
            $data[] = array(
                'id'=>$rs['id'],
                'image'=>image_url('successful-payment.png'),
                'ref'=>$rs['ref'],
                'amount'=>$rs['amount'],
                'term'=>term($rs['term_id']),
                'class_name'=>$rs['class_name'],
                'status'=>$rs['status'],
                'payment_type'=>payment_type($rs['payment_type']),
                'academic_session'=>$rs['academic_session'],
                'created_at'=>$rs['created_at'],
                'paid_at'=>$rs['paid_at']
            );
        }
    }

    echo json_encode($data);
    exit();
}