<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once './vendor/autoload.php';
require_once './DiscordApi.php';

$discordApi = new DiscordApi();

date_default_timezone_set('UTC');

$c = new \Slim\Container(['settings' => ['displayErrorDetails' => true]]);
$app = new \Slim\App($c);

$app->add(function ($request, $response, $next) {
	$request->registerMediaTypeParser('application/json', function($input) {
		return json_decode($input);
	});
	return $next($request, $response);
});

$app->post('/discordlogin', function($request, $response) {
	global $discordApi;
	$mongoClient = new MongoDB\Client;

	$body = $request->getParsedBody();
	$user = $discordApi->getUser($body->token);
	$member = $discordApi->getGuildMember($user->id);
	
	$collection = $mongoClient->seat->privilegedroles;
	$result = $collection->findOne();
	$roles = $result['roles'];

	$accessAllowed = false;
	foreach ($roles as $role) {
		if (in_array($role, $member->roles)) {
			$accessAllowed = true;
		}
	}
	
	if (!$accessAllowed) {
		return $response->withStatus(401);
	}

	$collection = $mongoClient->seat->users;
	while (true) {
		$token = random_int(PHP_INT_MIN, PHP_INT_MAX);
		$result = $collection->find([ 'token' => $token ])->toArray();
		if (count($result) == 0) {
			break;
		}
	}

	$result = $collection->find([ 'discord.id' => $user->id ])->toArray();
	if (count($result) == 0) {
		$collection->insertOne([
			'discord' => [
				'id' => $user->id,
				'username' => $user->username,
				'discriminator' => $user->discriminator,
				'avatar' => $user->avatar
			],
			'token' => $token
		]);
	} else {
		$collection->updateOne([ 'discord.id' => $user->id ], [
			'$set' => [
				'discord' => [
					'id' => $user->id,
					'username' => $user->username,
					'discriminator' => $user->discriminator,
					'avatar' => $user->avatar
				],
				'token' => $token
			]
		]);
	}

	return $response->withJson([ 'token' => $token ]);

});

$app->run();

?>