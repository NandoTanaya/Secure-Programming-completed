<?php 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $email = htmlspecialchars($_POST["email"]);

    $token = bin2hex(random_bytes(16));

    $hashed_token = hash("sha256", $token);

    $expiry = date("Y-m-d H:i:s", time() + 60 * 60);

    $sql = require("../database/database2.php");

    $newsql = "UPDATE drivers
               SET reset_token_hash = ?, 
               reset_token_expires_at = ?
               WHERE email = ?";

    $stmt = $sql->prepare($newsql);

    $stmt->bind_param("sss", $hashed_token, $expiry, $email);

    $stmt->execute();

    if ($sql->affected_rows){
        $mail = require("configure-smtp.php");

        $mail->setFrom("noreply@example.com");
        $mail->addAddress($email);

        $mail->Subject = "Password Reset";
        $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Secure-Programming-completed/php";
        $url = $base_url . "/reset-driver.php?token=$token";
        $mail->Body = <<<END

        Click <a href = "$url"> here </a> to reset your password. 
        END;

        try{
            $mail->send();
        } catch (Exception $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    else {
        echo("Email not found!");
        die;
    }

    echo "Message sent, please check your inbox.";
?>
