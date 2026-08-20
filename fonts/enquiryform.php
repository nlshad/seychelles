<?php
       // from the form
       $firstname = trim(strip_tags($_POST['firstname']));
       $lastname = trim(strip_tags($_POST['lastname']));
       $email = trim(strip_tags($_POST['email']));
       $phone = trim(strip_tags($_POST['phone']));
       $place = trim(strip_tags($_POST['place']));
       $message = trim(strip_tags($_POST['message']));

       // set here
       $subject = "Enquiry";
       $to = 'sales@seychellescargo.com';

       $body = <<<HTML
    First Name : $firstname <br/>
    Last Name : $lastname <br/>
    Phone : $phone <br/>
    Place : $place <br/>
    Message : $message <br/>
HTML;

        $headers = "From: $email\r\n";
       $headers .= "Content-type: text/html\r\n";

       // send the email
       mail($to, $subject, $body, $headers);

       // redirect afterwords, if needed
       header('Location: index.html');
?>