<?php
       // from the form
       $name = trim(strip_tags($_POST['name']));
       $contact = trim(strip_tags($_POST['contact']));
       $poster = trim(strip_tags($_POST['poster']));
       $recipient = trim(strip_tags($_POST['recipient']));
       $departure = trim(strip_tags($_POST['departure']));

       // set here
       $subject = "Request";
       $to = 'sales@seychellescargo.com';
       $email = 'noreplay@everbrightgroup.in';

       $body = <<<HTML
    Name :    $name <br/>
    Contact :    $contact <br/>
    City Poster : $poster <br/>
    City Recipient : $recipient <br/>
    Departure : $departure <br/>
   
HTML;

       $headers = "From: $email\r\n";
       $headers .= "Content-type: text/html\r\n";

       // send the email
       mail($to, $subject, $body, $headers);

       // redirect afterwords, if needed
       header('Location: index.html');
?>