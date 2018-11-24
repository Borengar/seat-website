<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once './vendor/autoload.php';
require_once './DiscordApi.php';
require_once './OsuApi.php';

$discordApi = new DiscordApi();
$osuApi = new OsuApi();

date_default_timezone_set('UTC');

$c = new \Slim\Container(['settings' => ['displayErrorDetails' => true]]);
$app = new \Slim\App($c);

$app->add(function ($request, $response, $next) {
	$request->registerMediaTypeParser('application/json', function($input) {
		return json_decode($input);
	});
	return $next($request, $response);
});

$app->add(function ($request, $response, $next) {
	if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		$mongoClient = new MongoDB\Client;
		$collection = $mongoClient->seat->users;
		$result = $collection->find([ 'token' => intval($_SERVER['HTTP_AUTHORIZATION']) ])->toArray();
		$request = $request->withAttribute('authenticated', count($result) > 0);
	}
	return $next($request, $response);
});

$app->get('/user', function($request, $response) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	$mongoClient = new MongoDB\Client;

	$collection = $mongoClient->seat->users;
	$result = $collection->findOne([ 'token' => intval($_SERVER['HTTP_AUTHORIZATION']) ]);
	return $response->withJson([
		'discord' => $result['discord']
	]);
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

$app->post('/mappools', function($request, $response) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	$mongoClient = new MongoDB\Client;
	$collection = $mongoClient->seat->mappools;
	$result = $collection->insertOne([
		'name' => 'New mappool',
		'slots' => []
	]);
	$mappool = $collection->findOne([ '_id' => $result->getInsertedId() ]);

	return $response->withJson([
		'mappool' => [
			'_id' => (string) $mappool['_id'],
			'name' => $mappool['name'],
			'slots' => $mappool['slots']
		]
	]);
});

$app->get('/mappools', function($request, $response) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	$mongoClient = new MongoDB\Client;
	$collection = $mongoClient->seat->mappools;
	$result = $collection->find()->toArray();

	foreach ($result as &$mappool) {
		$mappool['_id'] = (string) $mappool['_id'];
	}

	return $response->withJson($result);
});

$app->put('/mappools/{id}', function($request, $response, $args) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	$body = $request->getParsedBody();
	$mongoClient = new MongoDB\Client;
	$collection = $mongoClient->seat->mappools;
	$collection->updateOne([ '_id' => new MongoDB\BSON\ObjectID($args['id']) ], [
		'$set' => [
			'name' => $body->name,
			'slots' => $body->slots
		]
	]);
});

$app->get('/osubeatmap/{id}', function($request, $response, $args) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	global $osuApi;
	return $response->withJson($osuApi->getBeatmap($args['id']));
});

$app->get('/osubeatmapset/{id}', function($request, $response, $args) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	global $osuApi;
	return $response->withJson($osuApi->getBeatmapset($args['id']));
});

$app->get('/osubeatmapsetsearch/{query}', function($request, $response, $args) {
	if (!$request->getAttribute('authenticated')) {
		return $response->withStatus(401);
	}

	global $osuApi;
	return $response->withJson($osuApi->searchBeatmapsets($args['query']));
});

$app->run();

?>