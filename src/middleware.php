<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// middleware untuk validasi api key
$app->add(function ($request, $response, $next) {
    $publicRoutesArray = array(
        '/',
        'users/login/',
        'users/register/',
    );

    $routeName = $request->getUri()->getPath();

    $key = $request->getQueryParam("key");
    $is_public = in_array($routeName, $publicRoutesArray);
    if(!isset($key) && !$is_public){
        return $response->withJson(["status" => "API Key required"], 401);
    }
    
    $sql = "SELECT * FROM fsbld_users WHERE api_key=:api_key";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":api_key" => $key]);
    
    if($stmt->rowCount() > 0 || $is_public){
        $result = $stmt->fetch();
        if($key == $result["api_key"]){
        
            // update hit, kalau ada rencana mau membatasi akses mungkin hit berguna
            // $sql = "UPDATE users SET hit=hit+1 WHERE api_key=:api_key";
            // $stmt = $this->db->prepare($sql);
            // $stmt->execute([":api_key" => $key]);
            
            return $response = $next($request, $response);
        }
    }

    return $response->withJson(["status" => "Unauthorized"], 401);

});