<?php
//////////////////////////
//Specify default values//
//////////////////////////

if (isset($_POST['g-token'])) {

    $secret_key = "recaptcha_secret_key";
    $token = $_POST['g-token'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    $request = file_get_contents($url, false, $context);

    $res = json_decode($request, true);

    if ($res['success'] == false) {
        echo "NOT OK";
        return;
    }

} else {
    echo "There is some error. Please try agian Later!";
    return;

}

//Include PhpMailer
require_once "./php-mailer/PHPMailer.php";
require_once "./php-mailer/SMTP.php";
require_once "./php-mailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

//Your E-mail
$your_email = 'your@email.com';

//Message if 'name' field not specified
$name_not_specified = 'Please type a valid name';

//Message if e-mail sent successfully
$email_was_sent = 'Thanks, your message successfully sent';

//Message if e-mail not sent (server not configured)
$server_not_configured = 'Sorry, mail server not configured (function "mail()" disabled on your server?)';

///////////////////////////
//Contact Form Processing//
///////////////////////////
$errors = array();

//"name" field required by this PHP script even if
// there are no 'aria-required="true"' or 'required'
// attributes on this HTML input field
if (isset($_POST['name'])) {

    if (!empty($_POST['name'])) {
        $sender_name = stripslashes(strip_tags(trim($_POST['name'])));
    }

    if (!empty($_POST['message'])) {
        $message = stripslashes(strip_tags(trim($_POST['message'])));
    }

    if (!empty($_POST['email'])) {
        $sender_email = stripslashes(strip_tags(trim($_POST['email'])));
    }

    //Message if no sender name was specified
    if (empty($sender_name)) {
        $errors[] = $name_not_specified;
    }

    $from = (!empty($sender_email)) ? 'From: ' . $sender_email : '';

    $subject = "New Submission";

    //sending message if no errors
    if (empty($errors)) {

        //duplicating email meta (from and subject) to email message body
        $message_meta = '';
        //From name and email
        $message_meta .= 'From: ' . $sender_name . ' ' . $sender_email . "\r\n<br>";

        //adding another CUSTOM contact form fields that added by user to email message body
        foreach ($_POST as $key => $value) {
            //checking for standard fields
            if ($key == 'name' || $key == 'message' || $key == 'email') {
                continue;
            }
            //adding key-value pare to email message body
            $message_meta .= stripslashes(strip_tags(trim($key))) . ': ' . stripslashes(strip_tags(trim($value))) . "\r\n<br>";
        }

        $message = $message_meta . "\r\n" . 'Message:' . "\r\n" . $message;

        //echo $message;
        $message = wordwrap($message, 70);

        //PHPMailer Object
        $mail = new PHPMailer(true); //Argument true in constructor enables exceptions

        //Enable SMTP debugging.
        //$mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ),
        );

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "";
        $mail->Password = '';
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->From = $_POST['email'];
        $mail->FromName = $_POST['name'];

        $mail->addAddress("test@gmail.com", "Muhammad Awais");

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = $message;

        try {
            $mail->send();
            echo "Message has been sent successfully";
        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {
        echo '<span class="form-errors">' . implode('<br>', $errors) . '</span>';
    }
} else {
    echo '"name" variable were not received by server. Please check "name" attributes for your input fields';
}
