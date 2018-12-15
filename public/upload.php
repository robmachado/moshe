<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once '../bootstrap.php';

use Moshe\EncryptAndZip;

$enc = new EncryptAndZip();

$folder_name = 'storage/';

if(!empty($_FILES)) {
    $temp_file = $_FILES['file']['tmp_name'];
    $location = $folder_name . $_FILES['file']['name'];
    move_uploaded_file($temp_file, $location);
    $enc->process($location);
    unlink($location);
}

//remove o arquivo
if(isset($_POST["name"])) {
    $filename = $folder_name.$_POST["name"];
    unlink($filename);
}

//mostra arquivos no diretorio 
$result = array();
$files = scandir($folder_name);
$output = '<div class="row">';
if(false !== $files) {
    foreach($files as $file) {
        if('.' !=  $file && '..' != $file) {
            $path = $folder_name.$file;
            $output .= "<div class=\"row\">
                <div class=\"col-md-2\">
                <a href=\"$path\">$file</a>
                <button type=\"button\" class=\"btn btn-danger\" name=\"x\" id=\"$file\">Remover</button>
                </div></div>";
        }
    }
}
$output .= "";
echo $output;