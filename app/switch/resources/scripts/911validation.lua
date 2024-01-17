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
        local emergency_caller_id_number = session:getVariable("emergency_caller_id_number");
        local outgoing_caller_id_number =  session:getVariable("outgoing_caller_id_number");
        local caller_number = "";
        if(outgoing_caller_id_number ~= nil)then
                log.notice("emergency_outgoing_caller_id_number:"..outgoing_caller_id_number);
                caller_number = outgoing_caller_id_number;
        end

        if(emergency_caller_id_number ~= nil)then
                log.notice("emergency_emergency_caller_id_number:"..emergency_caller_id_number);
                caller_number = emergency_caller_id_number;
        end
--      caller_number = 12126609962
        log.notice("emergency_caller_number:"..caller_number);
        local hangup_flag = 0;
        if(caller_number ~= '')then
                local sql = "select cid_uuid ";
                sql = sql .. "from c_cid911validate ";
                sql = sql .. "where cid = :cid ";
                local params = {cid=caller_number};
                log.notice("emergency_SQL: "..sql);

                local row = dbh:first_row(sql, params)
                if not row then
                        freeswitch.consoleLog("NOTICE", "Emergency Caller ID not Match In Database\n");
                        session:hangup();
                else
                        local id = row.cid_uuid;
                        if(id ~= nil)then
                                log.notice("emergency_IDs:"..id);
                                hangup_flag = 1
                        end
                end
        end
                log.notice("emergency_hangup_flag:"..hangup_flag);
        if(hangup_flag == 0)then
                freeswitch.consoleLog("NOTICE", "Emergency Caller ID not Match In Database\n");
                session:hangup();
        end

