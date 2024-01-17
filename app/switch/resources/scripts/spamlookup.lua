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
--      session:answer();

--get the variables
        local destination_number = session:getVariable("destination_number_cust");
        local caller_id_number = session:getVariable("caller_id_number_cust");
        local disposition = 'UNALLOCATED_NUMBER'
        log.notice("destination_number:HP: "..destination_number);
        log.notice("caller_id_number:HP: "..caller_id_number);
        if(caller_id_number ~= '')then
                local sql = "select spam_lookup_uuid,isspam ";
                sql = sql .. "from spam_lookup ";
                sql = sql .. "where did = :cid ";
                sql = sql .. " AND created_at > now() - interval '6h' ";

                local params = {cid=caller_id_number};
                log.notice("spam_lookup_SQL: "..sql);

                local row = dbh:first_row(sql, params)
                if not row then
                        local bridge_str = "[leg_timeout=1]sofia/gateway/037d06b2-8e1c-40e1-a5d7-fc47e58ac398/"..destination_number
                        log.notice("bridge_str:HP: "..bridge_str);
                        local bridge_session =  session:execute("bridge",bridge_str);
                        disposition = session:getVariable("originate_disposition");
                        log.notice("disposition0:HP: "..disposition);
                        local disposition_flag = '1'
                        if(disposition == 'CALL_REJECTED')then
                                disposition_flag = '0'
                        end
			spam_lookup_uuid = api:executeString("create_uuid")
                        log.notice("disposition_flag:HP:spam_lookup_uuid "..spam_lookup_uuid);

                        log.notice("disposition_flag:HP: "..disposition_flag);
                        local insert_sql = "insert into spam_lookup ( "
                        insert_sql = insert_sql .. "did, "
                        insert_sql = insert_sql .. "spam_lookup_uuid, "

                        insert_sql = insert_sql .. "isspam "
                        insert_sql = insert_sql .. ") values ( "
                        insert_sql = insert_sql .. ":caller_id_number, "
                        insert_sql = insert_sql .. ":spam_lookup_uuid, "
                        insert_sql = insert_sql .. ":disposition_flag "
                        insert_sql = insert_sql .. ") "

                        local insert_params = {
                                caller_id_number  = caller_id_number;
                                disposition_flag   = disposition_flag;
				spam_lookup_uuid  = spam_lookup_uuid
                        }
                        dbh:query(insert_sql, insert_params)


                else
                        local spam_lookup_uuid = row.spam_lookup_uuid;
                        if(spam_lookup_uuid ~= nil)then
                                local isspam = row.isspam;
                                log.notice("spam_lookup_SQL_spam_lookup_uuid:"..spam_lookup_uuid);
                                log.notice("spam_lookup_SQL:"..spam_lookup_uuid);
                                if(isspam == '0')then
                                        disposition = 'CALL_REJECTED'
                                        log.notice("disposition1:HP: "..disposition);
                                else
                                        log.notice("disposition2:HP: "..disposition);
                                end
                        end
                end
        end
        log.notice("disposition3:HP: "..disposition);
--        session:execute("set","call_timeout=1");
      session:execute("set","spam_status="..disposition);
      session:execute("export", "spam_status="..disposition);
