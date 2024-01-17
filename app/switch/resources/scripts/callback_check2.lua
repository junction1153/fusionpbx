
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

        if (session:ready()) then
--                enabled = session:getVariable("enabled");
--              pin_number = session:getVariable("pin_number");
--              sounds_dir = session:getVariable("sounds_dir");
                domain_name = session:getVariable("domain_name");
                extension_uuid = session:getVariable("extension_uuid");
                domain_uuid = session:getVariable("domain_uuid");
                custom_extension = session:getVariable("custom_extension");
	        end


--check if the session is ready
        if not session:ready() then return end

        local dbh = Database.new('system');
--answer the call
--      session:answer();
--      local custom_callback = session:getVariable("custom_callback");
--      local domain_name = session:getVariable("domain_name");

--get the extensions from the database
                local sql = "SELECT custom_callback FROM v_extensions WHERE domain_uuid = '"..domain_uuid.."' AND extension = '"..custom_extension.."' and custom_callback = 'true' ";

                freeswitch.consoleLog("notice", "[directory] voicemail_dir: " .. sql .. "\n");
session:execute("sleep", "2000")


--              local params = {domain_uuid = domain_uuid, extension_uuid = :extension_uuid, custom_callback = :custom_callback};
--              freeswitch.consoleLog("notice", "[directory] SQL: " .. sql .. "; params: " .. json.encode(params) .. "\n");
                x = 1;
                directory = {}
                dbh:query(sql, params, function(row)
                        custom_callback = row.custom_callback;
--                      effective_caller_id_name = row.effective_caller_id_name;
                        if(custom_callback ~= true)then
--                              log.notice("CALLBACK:: yes");


                        session:execute("export", "custom_callback=true");
                        session:execute("set", "custom_callback=true");

else
                                log.notice("CALLBACK:: no result");

--end
--                              local first_three_digit = string.sub(effective_caller_id_name, 1, 3)
--                              log.notice("first_three_digit:HP:: "..first_three_digit);
--                              if(first_three_digit == 'XXX' or first_three_digit == 'xxx' )then
--                                      log.notice("BLOCKED CALLER ID:HP:: "..effective_caller_id_name);
--                                      session:hangup();

--     session:answer();

  --      session:execute("playback","misc/suspended-in");
--        session:execute("transfer", row.extension.." XML "..row.context);
    --                                  session:hangup();

  --                                      return;
                              end
                        end);
--      end
        --return;
--end
