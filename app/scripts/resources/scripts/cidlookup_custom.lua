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
		local sql = "select phonebook_id ";
		sql = sql .. "from p_numbers ";
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
			if(cid_name ~= 'UNKNOWN')then
				local sql = "select MAX(id) as id ";
				sql = sql .. "from phonebook ";
				local params = {};
				log.notice("p_numbers_id_max: "..sql);
				local p_numbers_id_max_row = dbh:first_row(sql, params)
				if not p_numbers_id_max_row then 
					phone_inc_id = 1
				else
					log.notice("p_numbers_id_max_row: "..p_numbers_id_max_row.id);
					phone_inc_id = tonumber(p_numbers_id_max_row.id)+1
				end
                                        log.notice("phonebook INC ID HARSH: "..phone_inc_id);
                               local insert_sql = "insert into phonebook ( "
                                insert_sql = insert_sql .. "id, "
                                insert_sql = insert_sql .. "name "
                                insert_sql = insert_sql .. ") values ( "
                                insert_sql = insert_sql .. ":id , "
                                insert_sql = insert_sql .. ":cid_name "
                                insert_sql = insert_sql .. ") "

		                local insert_params = {
		                        id  = phone_inc_id;
		                        cid_name  = cid_name;
		                }
		                dbh:query(insert_sql, insert_params)
				local phonebookid_sql = "select id ";
				phonebookid_sql = phonebookid_sql .. "from phonebook ";
				phonebookid_sql = phonebookid_sql .. "where name = :cid_name ";

				local phonebookid_params = {cid_name=cid_name};
				log.notice("cid_lookup_SQLphonebookid_sql_: "..phonebookid_sql);
				local phonebookid_row = dbh:first_row(phonebookid_sql, phonebookid_params)
				if not phonebookid_row then 

                                else
                                        local db_ph_id = phonebookid_row.id;
				local sql = "select MAX(id) as id ";
				sql = sql .. "from p_numbers ";
				local params = {};
				log.notice("p_numbers_id_max: "..sql);
				local p_numbers_id_max_row = dbh:first_row(sql, params)
				if not p_numbers_id_max_row then 
					phone_inc_id = 1
				else
					log.notice("p_numbers_id_max_row: "..p_numbers_id_max_row.id);
					phone_inc_id = tonumber(p_numbers_id_max_row.id)+1
				end
                                        log.notice("p_numbers INC ID HARSH: "..phone_inc_id);
                                        log.notice("db_ph_id in ODBC123: "..db_ph_id);
                                        local insertp_sql = "insert into p_numbers ( "
                                        insertp_sql = insertp_sql .. "id, "
                                        insertp_sql = insertp_sql .. "phonebook_id, "
                                        insertp_sql = insertp_sql .. "number "
                                        insertp_sql = insertp_sql .. ") values ( "
                                        insertp_sql = insertp_sql .. ":id, "
                                        insertp_sql = insertp_sql .. ":db_ph_id, "
                                        insertp_sql = insertp_sql .. ":cid_name "
                                        insertp_sql = insertp_sql .. ") "

                                        local insertp_params = {
                                                id = phone_inc_id;
                                                db_ph_id = db_ph_id;
                                                cid_name  = caller_id_number;
                                        }
                                        dbh:query(insertp_sql, insertp_params)
                                end
                        end
                        log.notice("cid_name in ODBC: "..cid_name);
		else
			local phonebook_id = row.phonebook_id;
			log.notice("phonebook_id: "..phonebook_id);
			local phonebook_sql = "select name ";
			phonebook_sql = phonebook_sql .. "from phonebook ";
			phonebook_sql = phonebook_sql .. "where id = :phn_id ";

			local phonebook_params = {phn_id=phonebook_id};
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


