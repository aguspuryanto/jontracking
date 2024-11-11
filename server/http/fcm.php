<?php
ob_start();
header("Connection: close");
header("Content-length: " . (string)ob_get_length());
ob_end_flush();

chdir('../');
include ('s_insert.php');

If (@$_GET["op"] == "addToken")
{
    $username = @$_GET["username"];
    $email = @$_GET["email"];
    $token = @$_GET["token"];
    fcm_token_add($username, $email, $token);
}

If (@$_GET["op"] == "deleteToken")
{
    $token = @$_GET["token"];
    fcm_token_delete($token);
}
