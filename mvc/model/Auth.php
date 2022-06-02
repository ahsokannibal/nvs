<?php
require_once("Model.php");

class Auth extends Model
{
	/**
     * contrôle si les indentifiants sont corrects
     *
     * @return bool 
     */
	public function checkCredentials(array $credentials){
		$db = $this->dbConnectPDO();
		
		$pseudo = $credentials[0];
		$password = $credentials[1];
		
		$query = 'SELECT id,password FROM users WHERE pseudo=:pseudo';
		
		$request = $db->prepare($query);
		$request->bindParam('pseudo', $pseudo, PDO::PARAM_STR);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_ASSOC);
		$hash = $request->fetch();
		
		if($hash){
			$check = password_verify($password,$hash['password']);
			
			if($check){
				$_SESSION['Auth'] = [
					'authenticated' => true,
					'user'=>$pseudo,
					'id'=>$hash['id']
				];
			}
		}else{
			$check = false;
		}
		
		return $check;
	}

	/**
     * ajoute le nombre d'essai d'authentification / Non refactorisé !!
     *
     * @return bool 
     */
	public function addFailedLoginAttempt($user_id, $ip_address){
	
	$timestamp = date('Y-m-d H:i:s');

	$stmt = $mysqli->query('INSERT INTO user_failed_logins SET user_id = '.$user_id.', ip_address = INET_ATON("'.$ip_address.'"), attempted_at = NOW()');
	return true;

	}
	
	/**
     * Bonne question ? / Non refactorisé !!
     *
     * @return ? 
     */
	public function getLoginStatus($mysqli, $options){
		
		//setup response array
		$response_array = array(
			'status' => 'safe',
			'message' => null
		);
		
		//attempt to retrieve latest failed login attempts
		$stmt = null;
		$latest_failed_logins = null;
		$row = null;
		$latest_failed_attempt_datetime = null;
		
		$time_frame_minutes = 10;
		
		$stmt = $mysqli->query('SELECT MAX(attempted_at) AS attempted_at FROM user_failed_logins');
		$latest_failed_logins = $stmt->num_rows;
		$row = $stmt->fetch_array();
		//get latest attempt's timestamp
		$latest_failed_attempt_datetime = (int) date('U', strtotime($row['attempted_at']));
		
		$throttle_settings = $options;

		//grab first throttle limit from key
		reset($throttle_settings);
		$first_throttle_limit = key($throttle_settings);

		//get all failed attempst within time frame
		$get_number = $mysqli->query('SELECT * FROM user_failed_logins WHERE attempted_at > DATE_SUB(NOW(), INTERVAL '.$time_frame_minutes.' MINUTE)');
		$number_recent_failed = $get_number->num_rows;
		//reverse order of settings, for iteration
		krsort($throttle_settings);
		
		//if number of failed attempts is >= the minimum threshold in throttle_settings, react
		if($number_recent_failed >= $first_throttle_limit ){				
			//it's been decided the # of failed logins is troublesome. time to react accordingly, by checking throttle_settings
			foreach ($throttle_settings as $attempts => $delay) {
				if ($number_recent_failed > $attempts) {
					// we need to throttle based on delay
					if (is_numeric($delay)) {
						//find the time of the next allowed login
						$next_login_minimum_time = $latest_failed_attempt_datetime + $delay;
						
						//if the next allowed login time is in the future, calculate the remaining delay
						if(time() < $next_login_minimum_time){
							$remaining_delay = $next_login_minimum_time - time();
							// add status to response array
							$response_array['status'] = 'delay';
							$response_array['message'] = $remaining_delay;
						}else{
							// delay has been passed, safe to login
							$response_array['status'] = 'safe';
						}						
					} else {
						// add status to response array
						$response_array['status'] = 'block';
					}
					break;
				}
			}  
		}
		
		//return the response array containing status and message 
		return $response_array;
	}
}