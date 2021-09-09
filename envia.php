<?php

$errorMSG = "";

$servico = $_POST["servico"];
$ip= $_POST["ip"];
$message = $_POST["message"];

// NAME
if (empty($servico)) {
    $errorMSG = "O Serviço não foi afetado";
}

// EMAIL
if (empty($ip)) {
    $errorMSG .= "É necessário informar o Endereço de IP ou URL do serviço afetado ";
}

// MESSAGE
if (empty($message)) {
    $errorMSG .= "é Necessário informar a descrição do problema identificado. ";
}

// change email with your email
$EmailTo = "monitoracao@yopmail.com";
$Subject = "Monitoração:: Alerta de Incidente";

// prepare email body text
$Body = "";
$Body .= "Serviço Afetado: ";
$Body .= $servico;
$Body .= "\n";
$Body .= "Endereço de IP | URL do serviço: ";
$Body .= $ip;
$Body .= "\n";
$Body .= "Descrição do problema identificado: ";
$Body .= $message;
$Body .= "\n\n\n";
$Body .="Enviado via XPTO Monitor";

// send email
$success = mail($EmailTo, $Subject, $Body, "De:".$email);

// redirect to success page
if ($success && $errorMSG == ""){
   echo "Enviado com sucesso";
   header( "refresh:5;url=index.php" );
}else{
    if($errorMSG == ""){
        echo "Por favor, preencha todos os campos :(";
        header( "refresh:5;url=index.php" );
    } else {
        echo $errorMSG;
    }
}
