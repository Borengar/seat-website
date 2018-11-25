<?php

class OsuApi {

	private $apiKey;
	private $username;
	private $password;
	private $refreshTime;

	function __construct() {
		$config = parse_ini_file('config.ini');
		$this->username = $config['osuUsername'];
		$this->password = $config['osuPassword'];
		$this->clientSecret = $config['osuClientSecret'];
	}

	function connect() {
		$mongoClient = new MongoDB\Client;
		$collection = $mongoClient->seat->osuapi;
		$result = $collection->findOne();
		$this->refreshTime = new DateTime($result['expiresIn']);
		$compareTime = new DateTime();
		if ($compareTime > $this->refreshTime) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => 'https://osu.ppy.sh/oauth/token',
				CURLOPT_POSTFIELDS => http_build_query(array(
					'username' => $this->username,
					'password' => $this->password,
					'grant_type' => 'password',
					'client_id' => '5',
					'client_secret' => $this->clientSecret
				))
			));
			$response = json_decode(curl_exec($curl));
			curl_close($curl);
			$this->apiKey = $response->access_token;
			$this->refreshTime = new DateTime();
			$this->refreshTime = $this->refreshTime->add(new DateInterval('P1D'));

			$mongoClient = new MongoDB\Client;
			$collection = $mongoClient->seat->osuapi;
			$collection->updateOne([], [
				'$set' => [
					'apiKey' => $this->apiKey,
					'expiresIn' => $this->refreshTime->format('Y-m-d H:i:s')
				]
			]);
		} else {
			$this->apiKey = $result['apiKey'];
		}
	}

	function getBeatmap($beatmapId) {
		$this->connect();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://osu.ppy.sh/api/v2/beatmaps/' . $beatmapId,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->apiKey
			)
		));
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	function getBeatmapset($beatmapsetId) {
		$this->connect();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://osu.ppy.sh/api/v2/beatmapsets/' . $beatmapsetId,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->apiKey
			)
		));
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	function searchBeatmapsets($query) {
		$this->connect();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://osu.ppy.sh/api/v2/beatmapsets/search/?q=' . str_replace(' ', '+', $query),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->apiKey
			)
		));
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response->beatmapsets;
	}

	function getMatch($matchId) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://osu.ppy.sh/community/matches/' . $matchId . '/history'
		));
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

}

?>