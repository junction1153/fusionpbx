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
	local xxx_blocked_number = session:getVariable("xxx_blocked_number");
	local domain_name = session:getVariable("domain_name");
	log.notice("xxx_blocked_number:HP:: "..xxx_blocked_number);	
	log.notice("domain_name:HP:: "..domain_name);	
	if(xxx_blocked_number ~= nil)then

	--get the domain_uuid
		if (domain_uuid == nil) then
			if (domain_name ~= nil) then
				local sql = "SELECT domain_uuid FROM v_domains ";
				sql = sql .. "WHERE domain_name = :domain_name";
				local params = {domain_name = domain_name};
				if (debug["sql"]) then
					freeswitch.consoleLog("notice", "[directory] SQL: " .. sql .. "; params: " .. json.encode(params) .. "\n");
				end
				dbh:query(sql, params, function(rows)
					domain_uuid = string.lower(rows["domain_uuid"]);
				end);
			end
		end
--get the extensions from the database
		local sql = "SELECT * FROM v_extensions WHERE domain_uuid = :domain_uuid AND extension=:xxx_blocked_number";
		local params = {domain_uuid = domain_uuid, xxx_blocked_number = xxx_blocked_number};
	--	freeswitch.consoleLog("notice", "[directory] SQL: " .. sql .. "; params: " .. json.encode(params) .. "\n");
		x = 1;
		directory = {}
		dbh:query(sql, params, function(row)
			effective_caller_id_name = row.effective_caller_id_name;
			if(effective_caller_id_name ~= nil)then
				log.notice("effective_caller_id_name:HP:: "..effective_caller_id_name);
				local first_three_digit = string.sub(effective_caller_id_name, 1, 3)
				log.notice("first_three_digit:HP:: "..first_three_digit);
				if(first_three_digit == 'XXX' or first_three_digit == 'xxx' )then
					log.notice("BLOCKED CALLER ID:HP:: "..effective_caller_id_name);
--					session:hangup();

--     session:answer();

        session:execute("playback","misc/suspended-in");
--        session:execute("transfer", row.extension.." XML "..row.context);
                                      session:hangup();

					return;
				end
			end
		end);
	end
	--return;
