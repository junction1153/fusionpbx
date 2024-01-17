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
	local caller_id_number = session:getVariable("caller_id_number_cust");
	local cid_name = "UNKNOWN";
		log.notice("caller_id_number:HP:: "..caller_id_number);	
	if(caller_id_number ~= nil)then
		local sql = "select cid_lookup_custom_uuid ";
		sql = sql .. "from cid_lookup_custom ";
		sql = sql .. "where number = :cid ";
		sql = sql .. " AND created_at > now() - interval '90 days' ";

		local params = {cid=caller_id_number};
		log.notice("cid_lookup_SQL: "..sql);
		local row = dbh:first_row(sql, params)
		if not row then 
			cidlookup_exists = api:executeString("module_exists mod_cidlookup");
			if (cidlookup_exists == "true") then
			    cid_name = api:executeString("cidlookup " .. caller_id_number);
			end
			log.notice("cid_name_HHHHHHHHHHHHHHH_SQL: "..cid_name);
--			if(cid_name ~= 'UNKNOWN')then
				cid_lookup_custom_uuid = api:executeString("create_uuid")

	                        local insert_sql = "insert into cid_lookup_custom ( "
				insert_sql = insert_sql .. "name "
				insert_sql = insert_sql .. ",number "
				insert_sql = insert_sql .. ",cid_lookup_custom_uuid "
		                insert_sql = insert_sql .. ") values ( "
		                insert_sql = insert_sql .. ":cid_name "
		                insert_sql = insert_sql .. ",:caller_id_number "
		                insert_sql = insert_sql .. ",:cid_lookup_custom_uuid "
		                insert_sql = insert_sql .. ") "

		                local insert_params = {
		                        cid_name  = cid_name;
		                        caller_id_number  = caller_id_number;
		                        cid_lookup_custom_uuid  = cid_lookup_custom_uuid;
		                }
		                dbh:query(insert_sql, insert_params)
--			end
			log.notice("cid_name in ODBC: "..cid_name);
		else
			local cid_lookup_custom_uuid = row.cid_lookup_custom_uuid;
			log.notice("cid_lookup_custom_uuid: "..cid_lookup_custom_uuid);
			local phonebook_sql = "select name ";
			phonebook_sql = phonebook_sql .. "from cid_lookup_custom ";
			phonebook_sql = phonebook_sql .. "where cid_lookup_custom_uuid = :phn_id ";

			local phonebook_params = {phn_id=cid_lookup_custom_uuid};
			log.notice("cid_lookup_SQLphonebook_: "..phonebook_sql);
			local phonebook_row = dbh:first_row(phonebook_sql, phonebook_params)
			if not phonebook_row then 

			else
				cid_name = phonebook_row.name;
				log.notice("cid_name in DB: "..cid_name);
			end
		end
	end
	log.notice("cid_name Final:HP"..cid_name);
	session:execute("set","cidlookup_caller_id_name="..cid_name);
	session:execute("export", "cidlookup_caller_id_name="..cid_name);


