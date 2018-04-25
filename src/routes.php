<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
/////////////////////////////////////// public function
$app->get('/', function (Request $request, Response $response) {
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    $sql = "SELECT * FROM fsbld_users";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result['users'] = $stmt->fetchAll();
    $sql = "SELECT * FROM kuliah";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result['matkul'] = $stmt->fetchAll();
    return $this->renderer->render($response, 'index.phtml', $result);
});

$app->get("/users/login/", function(Request $request, Response $response){
    $email = $request->getQueryParam("email");
    $pass = $request->getQueryParam("pass");

	$sql = "SELECT * FROM fsbld_users WHERE email=:email";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":email"=>$email]);
    $result = $stmt->fetchAll();
    if ($stmt->rowCount() > 0) {
    	if (password_verify($pass, $result[0]["password"])){
    		$sql = "UPDATE fsbld_users SET api_key=:api_key WHERE email=:email";
		    $stmt = $this->db->prepare($sql);
		    $new_api_key = bin2hex(random_bytes(32));
		    $data = [
		        ":email" => $email,
		        ":api_key" => $new_api_key
		    ];
   			if($stmt->execute($data))
    			return $response->withJson(["status" => "success", "data" => array("api_key"=>$new_api_key)], 200);
			else
		    	return $response->withJson(["status" => "failed", "data" => NULL], 200);
    	}else
	    	return $response->withJson(["status" => "wrong password", "data" => NULL], 200);
    }
    else
    	return $response->withJson(["status" => "failed", "data" => NULL], 200);
});

$app->post("/users/register/", function (Request $request, Response $response){
    $new_usr = $request->getParsedBody();

    $sql = "INSERT INTO fsbld_users (email, password) VALUE (:email, :pass)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":email" => $new_usr["email"],
        ":pass" => password_hash($new_usr["pass"], PASSWORD_BCRYPT)
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

/////////////////////////////////////////////////////////perlu api key
$app->put("/users/update/", function (Request $request, Response $response){
    //$email = $request->getQueryParam("email");
    $new_usr = $request->getParsedBody();
    $sql = "UPDATE fsbld_users SET password=:pass WHERE email=:email";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":email" => $new_usr["email"],
        ":pass" => password_hash($new_usr["pass"], PASSWORD_BCRYPT)
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});