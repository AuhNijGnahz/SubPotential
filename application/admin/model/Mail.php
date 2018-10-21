<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/17
 * Time: 19:58
 */

namespace app\admin\model;

use think\exception\DbException;
use think\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mail extends Model
{
    protected $table = 'ca_email';

//    public function __construct()
//    {
//        try {
//            $email = Mail::get(1);
//            var_dump($email);
//        } catch (DbException $e) {
//        }
//        config('MAIL_FROMNAME', $email->fromName);
//        config('MAIL_FROMADDRESS', $email->fromAddress);
//        config('MAIL_HOST', $email->smtpHost);
//        config('MAIL_PORT', $email->smtpPort);
//        config('MAIL_REPLYTO', $email->replyTo);
//        config('MAIL_SMTPUSER', $email->smtpUser);
//        config('MAIL_SMTPPASS', $email->smtpPwd);
//        config('MAIL_ENCRIPTTYPE', $email->encriptType);
//    }

    static function sendEmail($to, $name = "", $title, $content)
    {
        vendor('phpmailer.PHPMailer');
        vendor('phpmailer.Exception');
        vendor('phpmailer.SMTP');
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = config('MAIL_HOST');
        $encripttype = config('MAIL_ENCRIPTTYPE');
        if (!empty($encripttype) && $encripttype != "no") {
            $mail->SMTPSecure = $encripttype;
        }
        $mail->Port = config('MAIL_PORT');
        $mail->CharSet = 'UTF-8';
        $mail->FromName = config('MAIL_FROMNAME');
        $mail->Username = config('MAIL_SMTPUSER');
        $mail->Password = config('MAIL_SMTPPASS');
        $mail->From = config('MAIL_FROMADDRESS');
        $mail->isHTML(true);
        $mail->addAddress($to, $name);
        $mail->Subject = $title;
        $mail->Body = $content;
        $status = $mail->send();
        if (!$status) {
            return array('status' => false, 'message' => $mail->ErrorInfo);
        }
        return array('status' => true);
    }
}