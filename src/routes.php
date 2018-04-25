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