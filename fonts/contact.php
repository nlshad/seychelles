<?php
       // from the form
       $firstname = trim(strip_tags($_POST['firstname']));
       $lastname = trim(strip_tags($_POST['lastname']));
       $email = trim(strip_tags($_POST['email']));
       $message = trim(strip_tags($_POST['message']));

       // set here
       $subject = "Contact Us";
       $to = 'jafer@seychellescargo.com';

       $body = <<<HTML
    First Name :    $firstname <br/>
    Last Name :    $lastname <br/>
    Message : $message
HTML;

       $headers = "From: $email\r\n";
       $headers .= "Content-type: text/html\r\n";

       // send the email
       mail($to, $subject, $body, $headers);

       // redirect afterwords, if needed
       header('Location: index.html');
?>