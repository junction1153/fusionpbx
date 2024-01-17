--include config.lua
	require "resources.functions.config";

--create the api object
	api = freeswitch.API();

	require "resources.functions.channel_utils";
	local log = require "resources.functions.log".follow_me
	local cache = require "resources.functions.cache"
	local Database = require "resources.functions.database"
	local json
	if (debug["sql"]) then
		json = require "resources.functions.lunajson"
	end

--check if the session is ready
	if not session:ready() then return end

	local dbh = Database.new('system');
--answer the call
--	session:answer();
	local caller_id_number = session:getVariable("caller_id_number");
	local call_direction = session:getVariable("call_direction");
	local caller_id_name = session:getVariable("caller_id_name");
	local caller_destination = session:getVariable("caller_destination");
	local domain_name = session:getVariable("domain_name");
	log.notice("CallerID caller_id_number:HP:: "..caller_id_number);	
	caller_id_name = caller_id_name:gsub(" ", "--")
	log.notice(" CallerID caller_id_name:HP:: "..caller_id_name);	
	log.notice("domain_name:HP:: "..domain_name);	

	if(call_direction=='inbound')then
		log.notice("CallerID command:HP:::cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_mail_send.php "..caller_id_name.." "..caller_id_number);	
		os.execute("cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_mail_send.php "..caller_id_name.." "..caller_id_number)
	else
		log.notice("CallerID command:HP:::cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_mail_send.php "..caller_destination.." "..caller_id_number.." 1");	
		os.execute("cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_mail_send.php "..caller_id_number.." "..caller_destination.." 1")

	end

