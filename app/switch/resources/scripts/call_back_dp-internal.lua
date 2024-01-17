
        api = freeswitch.API();

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


-- Added By Hemant Chaudhari --

-- Get ARGV
caller_id_number = session:getVariable("caller_id_number");
destination_number = session:getVariable("destination_number");
call_back_number = session:getVariable("call_back_number");
domain_name = session:getVariable("domain_name");
domain_uuid = session:getVariable("domain_uuid");
extension_uuid = session:getVariable("extension_uuid");
sip_from_user = session:getVariable("sip_from_user");
custom_callback = session:getVariable("custom_callback");
                custom_extension = session:getVariable("custom_extension");

        require "resources.functions.send_presence";

-- Originate Command for call back
--xapi = freeswitch.API();



       if (session:ready()) then
--                enabled = session:getVariable("enabled");
--              pin_number = session:getVariable("pin_number");
--              sounds_dir = session:getVariable("sounds_dir");
                domain_name = session:getVariable("domain_name");
                extension_uuid = session:getVariable("extension_uuid");
                domain_uuid = session:getVariable("domain_uuid");
                custom_extension = session:getVariable("custom_extension");
                end



        local dbh = Database.new('system');
--                local sql = "SELECT custom_callback FROM v_extensions WHERE domain_uuid = '"..domain_uuid.."' AND extension = '"..custom_extension.."' and custom_callback = 'true' ";
                local sql = "SELECT custom_callback FROM v_extensions WHERE domain_uuid = '"..domain_uuid.."' AND extension = '"..custom_extension.."'";
               x = 1;
                directory = {}
                dbh:query(sql, params, function(row)

custom_callback = row.custom_callback;

session:sleep(2500);


if (string.len(destination_number) < 8 and custom_callback == "true") then
cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..sip_from_user..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..sip_from_user..",call_direction=outbound,accountcode="..domain_name.."}loopback/99946"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";
end

if (string.len(destination_number) > 8 and custom_callback == "true") then
cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..sip_from_user..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..sip_from_user..",call_direction=outbound,accountcode="..domain_name.."}loopback/99946"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";
end

if (string.len(destination_number) < 8 and custom_callback ~= "true") then
--cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..destination_number..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..destination_number..",call_direction=outbound,accountcode="..domain_name.."}loopback/99945"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";
cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..sip_from_user..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..sip_from_user..",call_direction=outbound,accountcode="..domain_name.."}loopback/99945"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";
end

if (string.len(destination_number) > 8 and custom_callback ~= "true") then
cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..sip_from_user..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..sip_from_user..",call_direction=outbound,accountcode="..domain_name.."}loopback/99945"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";
end



-- Execute string and Get UUID
uuid = api:executeString(cmd_string);

-- Trim UUID
uuid = uuid:sub(5)

-- YYYY-MON-DD
year = os.date("%Y");
month = os.date("%b");
day = os.date("%d");

-- Remove spaces
str = string.gsub(uuid, "%s+", "")

-- UUID Record
--rec_path = "bgapi uuid_record "..str.." start /var/lib/freeswitch/recordings/pbx02nyc/"..domain_name.."/archive/"..year.."/"..month.."/"..day.."/"..str..".wav"
--rec_path = "bgapi uuid_record "..str.." start /var/lib/freeswitch/recordings/pbx02nyc/"..domain_name.."/archive/"..year.."/"..month.."/"..day.."/"..caller_id_number.."/"..destination_number..".wav"
--uuid1 = api:executeString(rec_path);

 end);
--end
