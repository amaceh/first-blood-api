<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
/////////////////////////////////////// public function
$app->get('/', function (Request $request, Response $response) {
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' route");
    // Render index view

    return $this->renderer->render($response, 'index.phtml');
});

$app->get("/users/login/", function(Request $request, Response $response){
    $user = $request->getQueryParam("user");
    $pass = $request->getQueryParam("pass");

    $sql = "SELECT * FROM fsbld_users WHERE username=:username OR email=:email";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":username"=>$user,":email"=>$user]);
    $result = $stmt->fetchAll();
    if ($stmt->rowCount() > 0) {
        if (password_verify($pass, $result[0]["password"])){
            $sql = "UPDATE fsbld_users SET api_key=:api_key WHERE username=:username OR email=:email";
            $stmt = $this->db->prepare($sql);
            $new_api_key = bin2hex(random_bytes(32));
            $data = [
                ":username"=>$user,
                ":email"=>$user,
                ":api_key" => $new_api_key
            ];
            if($stmt->execute($data)){
                $result[0]['api_key']=$new_api_key;
                return $response->withJson(["status" => "success", "data" => $result[0]], 200);
            }else
                return $response->withJson(["status" => "failed", "data" => NULL], 200);
        }else
            return $response->withJson(["status" => "wrong password", "data" => NULL], 200);
    }
    else
        return $response->withJson(["status" => "failed", "data" => NULL], 200);
});

$app->post("/users/register/", function (Request $request, Response $response){
    $new_usr = $request->getParsedBody();

    $sql = "INSERT INTO fsbld_users (username, email, password, nama, goldar, rhesus, no_hp, foto_profil) ";
    $sql .= "VALUE (:username, :email, :password, :nama, :goldar, :rhesus, :no_hp, :foto_profil)";
    $stmt = $this->db->prepare($sql);
    $data = [
        ":username"     => $new_usr["username"],
        ":email"        => $new_usr["email"],
        ":password"     => password_hash($new_usr["password"], PASSWORD_BCRYPT),
        ":nama"         => $new_usr["nama"],
        ":goldar"       => $new_usr["goldar"],
        ":rhesus"       => $new_usr["rhesus"],
        ":no_hp"        => $new_usr["no_hp"],
        ":foto_profil"  => $new_usr["foto_profil"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

/////////////////////////////////////////////////////////perlu api key
$app->put("/users/update/", function (Request $request, Response $response){
    //$email = $request->getQueryParam("email");
    $new_usr = $request->getParsedBody();
    $sql  = "UPDATE fsbld_users SET email=:email, password=:password, nama=:nama, goldar=:goldar,";
    $sql .= "rhesus=:rhesus, no_hp=:no_hp, foto_profil=:foto_profil WHERE username=:username";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":username"     => $new_usr["username"],
        ":email"        => $new_usr["email"],
        ":password"         => password_hash($new_usr["password"], PASSWORD_BCRYPT),
        ":nama"         => $new_usr["nama"],
        ":goldar"       => $new_usr["goldar"],
        ":rhesus"       => $new_usr["rhesus"],
        ":no_hp"        => $new_usr["no_hp"],
        ":foto_profil"  => $new_usr["foto_profil"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

$app->get("/users/getUsers/", function(Request $request, Response $response){
    $sql = "SELECT (username, email, nama, goldar, rhesus, no_hp, foto_profil) FROM fsbld_users";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    if ($stmt->rowCount() > 0) {    
        if($stmt->execute($data)){
            $result[0]['api_key']=$new_api_key;
            return $response->withJson(["status" => "success", "data" => $result[0]], 200);
        }else
            return $response->withJson(["status" => "failed", "data" => NULL], 200);
    }
    else
        return $response->withJson(["status" => "failed", "data" => NULL], 200);
});

///////////////////////////////////////////////////////////////////////////////

$app->get("/posting/", function (Request $request, Response $response){
    //$sql = "SELECT * FROM fsbld_posts";
    $sql = "SELECT B.*, A.nama, A.foto_profil FROM fsbld_users A INNER JOIN fsbld_posts B ON A.username=B.username";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

$app->get("/posting/latest/", function (Request $request, Response $response){
    // for syncing purpose
    //SELECT * FROM fsbld_post WHERE inserted_at > '2018-04-28 08:00:00'
    $waktu = $request->getQueryParam("time");
    $sql = "SELECT B.*, A.nama, A.foto_profil FROM fsbld_users A INNER JOIN fsbld_posts B ON A.username=B.username WHERE inserted_at > :waktu OR updated_at > :waktu";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":waktu"=>$waktu]);
    //$stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

$app->get("/posting/get/{id}/", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $sql = "SELECT B.*, A.nama, A.foto_profil FROM fsbld_users A INNER JOIN fsbld_posts B ON A.username=B.username WHERE id_post=:id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

$app->get("/posting/search/", function (Request $request, Response $response, $args){
    $keyword = $request->getQueryParam("keyword");
    $sql = "SELECT * FROM fsbld_posts WHERE id_post LIKE '%$keyword%' OR username LIKE '%$keyword%' OR goldar LIKE '%$keyword%' OR rhesus LIKE '%$keyword%' OR descrip LIKE '%$keyword%' OR rumah_sakit LIKE '%$keyword%' OR status LIKE '%$keyword%' OR inserted_at LIKE '%$keyword%' OR updated_at LIKE '%$keyword%'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
    //var_dump($sql);
});

$app->post("/posting/", function (Request $request, Response $response){

    $new_mk = $request->getParsedBody();

    $sql = "INSERT INTO fsbld_posts (username, goldar, rhesus, descrip, rumah_sakit, status, inserted_at, updated_at)"; 
    $sql .= "VALUE (:username, :goldar, :rhesus, :descrip, :rumah_sakit, :status, :inserted_at, :updated_at)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":username" => $new_mk["username"], 
        ":goldar" => $new_mk["goldar"], 
        ":rhesus" => $new_mk["rhesus"], 
        ":descrip" => $new_mk["descrip"], 
        ":rumah_sakit" => $new_mk["rumah_sakit"], 
        ":status" => $new_mk["status"], 
        ":inserted_at" => $new_mk["inserted_at"], 
        ":updated_at" => $new_mk["updated_at"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


$app->put("/posting/{id}/", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $new_mk = $request->getParsedBody();
    $sql = "UPDATE fsbld_posts SET goldar=:goldar, rhesus=:rhesus, descrip=:descrip, rumah_sakit=:rumah_sakit, status=:status, updated_at=:updated_at WHERE id_post=:id";
    $stmt = $this->db->prepare($sql);
    
    //tidak semua attribute perlu diupdate
    $data = [
        ":id" => $id,
        ":goldar" => $new_mk["goldar"], 
        ":rhesus" => $new_mk["rhesus"], 
        ":descrip" => $new_mk["descrip"], 
        ":rumah_sakit" => $new_mk["rumah_sakit"], 
        ":status" => $new_mk["status"], 
        ":updated_at" => $new_mk["updated_at"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


$app->delete("/posting/{id}/", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $sql = "DELETE FROM fsbld_posts WHERE id_post=:id";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id" => $id
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

