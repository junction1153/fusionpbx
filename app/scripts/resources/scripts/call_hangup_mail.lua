dat = env:serialize()
freeswitch.consoleLog("INFO","Here's everything:\n" .. dat .. "\n")

-- Grab a specific channel variable
dat = env:getHeader("uuid")
freeswitch.consoleLog("INFO","Inside hangup hook, uuid is: " .. dat .. "\n")

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
        caller_id_name = env:getHeader("caller_id_name")
        caller_id_number = env:getHeader("caller_id_number")
        caller_destination = env:getHeader("caller_destination")
        phone_number = env:getHeader("caller_destination")
        local call_direction = env:getHeader("call_direction");
        valet_parking_orbit_exten = env:getHeader("valet_parking_orbit_exten")
        last_bridge_hangup_cause = env:getHeader("last_bridge_hangup_cause")
        transfer_fallback_extension = env:getHeader("transfer_fallback_extension")
        flags_val = 0
        log.notice("HASU0");
        billsec = env:getHeader("billsec")
        if(valet_parking_orbit_exten and valet_parking_orbit_exten ~= nil)then
                if(last_bridge_hangup_cause and last_bridge_hangup_cause ~= nil)then
                        log.notice("HASU1");
                        caller_destination = env:getHeader("last_sent_callee_id_number")
                        flags_val = 1
                        log.notice("valet_parking_orbit_exten:HP:: "..valet_parking_orbit_exten);
                        caller_id_name = env:getHeader("last_sent_callee_id_name")
                        caller_id_number = env:getHeader("last_sent_callee_id_number")
                        if(call_direction=='inbound')then
                                caller_id_number = caller_destination
                        end
                        phone_number = env:getHeader("sip_to_user")
                        dialed_user = env:getHeader("caller_id_number")
                        log.notice("valet_parking_orbit_exten:HP:: PASS");
                        log.notice("valet_parking_orbit_exten:HP:: PASS caller_destination"..caller_destination);
                        log.notice("valet_parking_orbit_exten:HP:: PASS phone_number"..phone_number);
                        caller_id_name = env:getHeader("cidlookup_caller_id_name")
                else
                        log.notice("HASU4");
                        return
                        log.notice("valet_parking_orbit_exten:HP:: RETURN");
                end
        else
                if(transfer_fallback_extension and transfer_fallback_extension ~= 'operator')then
                        log.notice("HASU2");
                else
                        last_bridge_role = env:getHeader("last_bridge_role")
                        if(last_bridge_role and last_bridge_role ~= 'originator')then
                                log.notice("HASU5");
                        else
                                log.notice("HASU3");
                                flags_val = 1
                                billsec = env:getHeader("answersec")
                                log.notice("valet_parking_orbit_exten:HP:: NORMAL RETURN");
                                caller_destination = env:getHeader("caller_id_number")
                                caller_id_name = env:getHeader("caller_id_name")
                                caller_id_number = env:getHeader("caller_id_number")
                                phone_number = env:getHeader("caller_destination")
                                dialed_user = env:getHeader("dialed_user")
                                log.notice("valet_parking_orbit_exten:HP:: PASS NORMAL caller_destination"..caller_destination);
                                log.notice("valet_parking_orbit_exten:HP:: PASS NORMAL phone_number"..phone_number);
                        end
                end
        end
--      transfer_fallback_extension = env:getHeader("transfer_fallback_extension")
--      if(transfer_fallback_extension and transfer_fallback_extension ~= nil and transfer_fallback_extension ~= 'operator')then
--              log.notice("transfer_fallback_extension:HP:: "..transfer_fallback_extension);
--
--              flags_val = 0
--              caller_id_name = env:getHeader("caller_id_name")
--              caller_id_number = env:getHeader("caller_id_number")
--              caller_destination = env:getHeader("sip_from_user_stripped")
--      end
        log.notice(":HP-RETURN:: RETURN:::flags_val"..flags_val);

        if(tonumber(flags_val) == 0)then
                return
                log.notice(":HP-RETURN:: RETURN");
        end
        log.notice("CallerID caller_id_number:HP:: "..billsec);
        log.notice("CallerID caller_id_number:HP:: "..caller_id_number);
        caller_id_name = caller_id_name:gsub(" ", "--")
        log.notice(" CallerID caller_id_name:HP:: "..caller_id_name);
--check if the session is ready

        local dbh = Database.new('system');

        if(call_direction=='inbound')then
                log.notice("CallerID command:HP:::cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_hangup_mail_send.php "..caller_id_name.." "..caller_id_number.." "..billsec.." 2 "..dialed_user);
                os.execute("cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_hangup_mail_send.php "..caller_id_name.." "..caller_id_number.." "..billsec.." 2 "..dialed_user)
        else
                log.notice("CallerID command:HP:::cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_hangup_mail_send.php "..caller_destination.." "..phone_number.." "..billsec.." 1");
                os.execute("cd /var/www/fusionpbx/app/extensions/ && /usr/bin/php start_call_hangup_mail_send.php "..caller_destination.." "..phone_number.." "..billsec.." 1")

        end

