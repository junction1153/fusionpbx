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
                                local user_sql = "select user_uuid from v_users where username=:username";
                                local params = {username ='admin'};
			log.notice("cid_name_HHHHHHHHHHHHHHH_SQL: "..user_sql);
                                local user_row = dbh:first_row(user_sql, params)
                                local user_id = user_row.user_uuid
	                        local insert_sql = "insert into cid_lookup_custom ( "
				insert_sql = insert_sql .. "name "
				insert_sql = insert_sql .. ",number "
				insert_sql = insert_sql .. ",cid_lookup_custom_uuid "
				insert_sql = insert_sql .. ",insert_date "
				insert_sql = insert_sql .. ",insert_user "
		                insert_sql = insert_sql .. ") values ( "
		                insert_sql = insert_sql .. ":cid_name "
		                insert_sql = insert_sql .. ",:caller_id_number "
		                insert_sql = insert_sql .. ",:cid_lookup_custom_uuid "
		                insert_sql = insert_sql .. ",:insert_date "
		                insert_sql = insert_sql .. ",:insert_user "
		                insert_sql = insert_sql .. ") "

		                local insert_params = {
		                        cid_name  = cid_name;
		                        caller_id_number  = caller_id_number;
		                        cid_lookup_custom_uuid  = cid_lookup_custom_uuid;
		                        insert_date  = os.date("%Y-%m-%d %H:%M:00");
		                        --insert_date  = os.time();
		                        insert_user  = user_id;
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
--JOSHEPH-13/05/2022 START
		local spam_sql = "select spam_score,created_at ";
		spam_sql = spam_sql .. "from spam_caller_id_lookup ";
		spam_sql = spam_sql .. "where callerid = :cid ";
--		spam_sql = spam_sql .. " AND created_at > now() - interval '90 days' ";

		local params = {cid=caller_id_number};
		log.notice("spam_sql: "..spam_sql);
		log.notice("caller_id_number: "..caller_id_number);
		local spam_row = dbh:first_row(spam_sql, params)
		local score_flag = 0
		local score_value = 0
		local score_exist = 0
		if spam_row then
			score_exist = 1
			local db_time = spam_row.created_at
			log.notice("db_time: "..db_time);
			local seconds_since_xxx = os.time()
			seconds_since_xxx = seconds_since_xxx - (50 * 50 * 8)
--			seconds_since_xxx = seconds_since_xxx - (60)
			local before_time = os.date("%Y-%m-%d %H:%M:00", seconds_since_xxx)
			if(db_time > before_time)then
				score_flag = 1
				score_value = spam_row.spam_score
			end
		end
		if(score_flag == 1)then
			if(tonumber(score_value) > 79)then
				local caller_id_name = session:getVariable("caller_id_name");
--                              local caller_id_name = session:getVariable("effective_caller_id_name");
--				session:execute("set","spam_caller_id_name=?SPAM?"..caller_id_name);
--				session:execute("set","caller_id_name=?SPAM?"..caller_id_name);
--				session:execute("set","effective_caller_id_name=?SPAM?"..caller_id_name);
--				session:execute("set","effective_caller_id_name=?SPAM?"..caller_id_name);
--session:execute("set","cidlookup_caller_id_name=?SPAM?"..caller_id_name);
                                        session:execute("set","spam_caller_id_name=SPAM");

			end
		else
			local destination_number = session:getVariable("destination_number");
			transcribe_cmd = "curl --connect-timeout 2 --max-time 2 \"https://CHANGEME/api/v1?username=CHANGEME&password=CHANGEME&resp_type=extended&resp_format=json&calling_number="..caller_id_number.."&called_number="..destination_number.."&call_party=terminating\""
			log.notice("transcribe_cmd: "..transcribe_cmd);
			local handle = io.popen(transcribe_cmd);
			local transcribe_result = handle:read("*a");
			log.notice("transcribe_result: "..transcribe_result);
			if(transcribe_result ~= nil)then

				for string_match in string.gmatch(transcribe_result, "[^,]+") do
				if string.match(string_match, 'spam_score"') then
					string_match = string_match:gsub('%"spam_score": ', "")
					spam_score = tonumber(string_match)
					log.notice("spam_score: "..spam_score);

                        if (spam_score == nil) then
                                spam_score = 0;
                        end


				end
				end
				log.notice("transcribe_cmd: "..spam_score);
				if(tonumber(spam_score) > 79)then
--                                        local caller_id_name = session:getVariable("caller_id_name");
--					local caller_id_name = session:getVariable("effective_caller_id_name");
--					session:execute("set","spam_caller_id_name=?SPAM?"..caller_id_name);
--					session:execute("set","caller_id_name=?SPAM?"..caller_id_name);
--					session:execute("set","effective_caller_id_name=?SPAM?"..caller_id_name);
                                        session:execute("set","spam_caller_id_name=SPAM");


--session:execute("set","cidlookup_caller_id_name=?SPAM?"..caller_id_name);

--					session:execute("set","effective_caller_id_name=?SPAM?"..caller_id_name);

				end
				local user_sql = "select user_uuid from v_users where username=:username";
                                local params = {username ='admin'};
                                local user_row = dbh:first_row(user_sql, params)
                                local user_id = user_row.user_uuid
				if(score_exist == 1)then
					local sql = "UPDATE spam_caller_id_lookup SET ";
					--sql = sql .. "spam_score = :spam_score,created_at = now() ";
					sql = sql .. "spam_score = :spam_score,created_at = now(),update_date= now(),update_user=:user_id ";
					sql = sql .. "WHERE callerid = :callerid ";
					local params = {spam_score = spam_score, callerid = caller_id_number,user_id=user_id};
					log.notice("update_sql0: "..sql);
					log.notice("update_sql1: "..spam_score);
					log.notice("update_sql2: "..caller_id_number);
					log.notice("update_sql3: "..user_id);
					dbh:query(sql, params);
				else
					spam_caller_id_lookup_uuid = api:executeString("create_uuid")
			                local insert_sql = "insert into spam_caller_id_lookup ( "
					insert_sql = insert_sql .. "spam_caller_id_lookup_uuid "
					insert_sql = insert_sql .. ",callerid "
					insert_sql = insert_sql .. ",spam_score "
					insert_sql = insert_sql .. ",insert_date "
					insert_sql = insert_sql .. ",insert_user "
				        insert_sql = insert_sql .. ") values ( "
				        insert_sql = insert_sql .. ":spam_caller_id_lookup_uuid "
				        insert_sql = insert_sql .. ",:callerid "
				        insert_sql = insert_sql .. ",:spam_score "
				        insert_sql = insert_sql .. ",:insert_date "
				        insert_sql = insert_sql .. ",:insert_user "
				        insert_sql = insert_sql .. ") "
				        local insert_params = {
				                spam_caller_id_lookup_uuid  = spam_caller_id_lookup_uuid;
				                callerid  = caller_id_number;
				                spam_score  = spam_score;
				                --insert_date  = os.time();
				                insert_date  = os.date("%Y-%m-%d %H:%M:00");
				                insert_user  = user_id;
				        }
					log.notice("insert_sql: "..insert_sql);
				        dbh:query(insert_sql, insert_params)
				end
			end
		end
--JOSHEPH-13/05/2022 END
	log.notice("cid_name Final:HP"..cid_name);
	session:execute("set","cidlookup_caller_id_name="..cid_name);
	session:execute("export", "cidlookup_caller_id_name="..cid_name);


