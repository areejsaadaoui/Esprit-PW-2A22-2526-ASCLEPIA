<?php

header('Content-Type: application/json');

require_once '../../config.php';

$data=json_decode(file_get_contents("php://input"),true);

if(!$data){

echo json_encode([
"success"=>false,
"message"=>"Aucune donnée"
]);

exit();

}

$user=$data["user"];

try{

$pdo=config::getConnexion();

$check=$pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email=?");

$check->execute([$user["email"]]);

if($check->fetchColumn()>0){

echo json_encode([
"success"=>false,
"message"=>"Email existe déjà"
]);

exit();

}

$password=password_hash($user["mot_de_passe"],PASSWORD_DEFAULT);

$sql="INSERT INTO utilisateur
(nom,email,mot_de_passe,adresse,role,date_naissance,telephone,description,date_creation)

VALUES (?,?,?,?,?,?,?,?,NOW())";

$stmt=$pdo->prepare($sql);

$stmt->execute([

$user["nom"],
$user["email"],
$password,
$user["adresse"],
$user["role"],
$user["date_naissance"],
$user["telephone"],
$user["description"]

]);

echo json_encode([
"success"=>true,
"message"=>"Utilisateur ajouté avec succès"
]);

}

catch(Exception $e){

echo json_encode([
"success"=>false,
"message"=>$e->getMessage()
]);

}

?>