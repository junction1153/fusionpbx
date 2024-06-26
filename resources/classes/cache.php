<?php

/**
 * cache class provides an abstracted cache
 *
 * @method string set
 * @method string get
 * @method string delete
 * @method string flush
 */
class cache {

	/**
	 * Called when the object is created
	 */
	public function __construct() {
		//place holder
	}

	/**
	 * Add a specific item in the cache
	 * @var string $key		the cache id
	 * @var string $value	string to be cached
	 */
	public function set($key, $value) {

		//change the delimiter
			$key = str_replace(":", ".", $key);

		//save to memcache
			if ($_SESSION['cache']['method']['text'] == "memcache") {
				//connect to event socket
					$esl = event_socket::create();
					if ($esl === false) {
						return false;
					}

				//run the memcache
					$command = "memcache set ".$key." ".$value;
					$result = event_socket::api($command);

			}

		//save to the file cache
			if ($_SESSION['cache']['method']['text'] == "file") {
				$result = file_put_contents($_SESSION['cache']['location']['text'] . "/" . $key, $value);
			}

		//return result
			return $result;
	}

	/**
	 * Get a specific item from the cache
	 * @var string $key		cache id
	 */
	public function get($key) {

		//change the delimiter
			$key = str_replace(":", ".", $key);

		//cache method memcache 
			if ($_SESSION['cache']['method']['text'] == "memcache") {
				// connect to event socket
					$esl = event_socket::create();
					if (!$esl->is_connected()) {
						return false;
					}

				//send a custom event

				//run the memcache
					$command = "memcache get ".$key;
					$result = event_socket::api($command);

			}

		//get the file cache
			if ($_SESSION['cache']['method']['text'] == "file") {
				if (file_exists($_SESSION['cache']['location']['text'] . "/" . $key)) {
					$result = file_get_contents($_SESSION['cache']['location']['text'] . "/" . $key);
				}
			}

		//return result
			return $result ?? null;
	}

	/**
	 * Delete a specific item from the cache
	 * @var string $key		cache id
	 */
	public function delete($key) {

		//debug information
			if (isset($_SESSION['cache']['syslog']['boolean']) && $_SESSION['cache']['syslog']['boolean'] == "true") {
				openlog("fusionpbx", LOG_PID | LOG_PERROR, LOG_USER);
				syslog(LOG_WARNING, "debug: cache: [key: ".$key.", script: ".$_SERVER['SCRIPT_NAME'].", line: ".__line__."]");
				closelog();
			}

		//cache method memcache 
			if (!empty($_SESSION['cache']['method']['text']) && $_SESSION['cache']['method']['text'] == "memcache") {
				//connect to event socket
					$esl = event_socket::create();
					if ($esl === false) {
						return false;
					}

				//send a custom event
					$event = "sendevent CUSTOM\n";
					$event .= "Event-Name: CUSTOM\n";
					$event .= "Event-Subclass: fusion::memcache\n";
					$event .= "API-Command: memcache\n";
					$event .= "API-Command-Argument: delete ".$key."\n";
					event_socket::command($event);

				//run the memcache
					$command = "memcache delete ".$key;
					$result = event_socket::api($command);

			}

		//cache method file
			if (!empty($_SESSION['cache']['method']['text']) && $_SESSION['cache']['method']['text'] == "file") {
				//change the delimiter
					$key = str_replace(":", ".", $key);

				//connect to event socket
					$esl = event_socket::create();
					if ($esl === false) {
						return false;
					}

				//send a custom event
					$event = "sendevent CUSTOM\n";
					$event .= "Event-Name: CUSTOM\n";
					$event .= "Event-Subclass: fusion::file\n";
					$event .= "API-Command: cache\n";
					$event .= "API-Command-Argument: delete ".$key."\n";
					event_socket::command($event);

				//remove the local files
					foreach (glob($_SESSION['cache']['location']['text'] . "/" . $key) as $file) {
						if (file_exists($file)) {
							unlink($file);
						}
						if (file_exists($file)) {
							unlink($file . ".tmp");
						}
					}
			}

	}

	/**
	 * Delete the entire cache
	 */
	public function flush() {

		//debug information
			if (isset($_SESSION['cache']['syslog']['boolean']) && $_SESSION['cache']['syslog']['boolean'] == "true") {
				openlog("fusionpbx", LOG_PID | LOG_PERROR, LOG_USER);
				syslog(LOG_WARNING, "debug: cache: [flush: all, script: ".$_SERVER['SCRIPT_NAME'].", line: ".__line__."]");
				closelog();
			}

		//cache method memcache 
			if ($_SESSION['cache']['method']['text'] == "memcache") {
				// connect to event socket
					$esl = event_socket::create();
					if ($esl === false) {
						return false;
					}

				//send a custom event
					$event = "sendevent CUSTOM\n";
					$event .= "Event-Name: CUSTOM\n";
					$event .= "Event-Subclass: fusion::memcache\n";
					$event .= "API-Command: memcache\n";
					$event .= "API-Command-Argument: flush\n";
					event_socket::command($event);

				//run the memcache
					$command = "memcache flush";
					$result = event_socket::api($command);

			}

		//cache method file 
			if ($_SESSION['cache']['method']['text'] == "file") {
				// connect to event socket
					$esl = event_socket::create();
					if ($esl === false) {
						return false;
					}

				//send a custom event
					$event = "sendevent CUSTOM\n";
					$event .= "Event-Name: CUSTOM\n";
					$event .= "Event-Subclass: fusion::file\n";
					$event .= "API-Command: cache\n";
					$event .= "API-Command-Argument: flush\n";
					event_socket::command($event);

				//remove the cache
					recursive_delete($_SESSION['cache']['location']['text']);

				//set message
					$result = '+OK cache flushed';
			}

		//return result
			return $result;
	}
}

?>
