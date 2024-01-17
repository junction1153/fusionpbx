        api = freeswitch.API();

-- Added By Hemant Chaudhari --

-- Get ARGV
caller_id_number = session:getVariable("caller_id_number");
destination_number = session:getVariable("destination_number");
call_back_number = session:getVariable("call_back_number");
domain_name = session:getVariable("domain_name");
domain_uuid = session:getVariable("domain_uuid");
extension_uuid = session:getVariable("extension_uuid");

-- Originate Command for call back
--xapi = freeswitch.API();

session:sleep(2500);

cmd_string = "originate {sip_from_user="..caller_id_number..",sip_to_user="..destination_number..",call_back_script=true,caller_id_number="..call_back_number..",bleg_caller_id_number="..call_back_number..",ignore_early_media=true,context="..domain_name..",domain="..domain_name..",domain_name="..domain_name..",domain_uuid="..domain_uuid..",effective_caller_id_name="..caller_id_number..",outbound_caller_id_name="..caller_id_number..",extension_uuid="..extension_uuid..",origination_caller_id_name="..caller_id_number..",origination_caller_id_number="..caller_id_number..",outbound_caller_id_number="..caller_id_number..",effective_caller_id_number="..caller_id_number..",call_direction=outbound,accountcode="..domain_name.."}loopback/"..call_back_number.."/"..domain_name.." "..destination_number.." XML "..domain_name.."";



-- Execute string and Get UUID
uuid = api:executeString(cmd_string);
 freeswitch.consoleLog("callback"," CMD STRING ".. cmd_string)

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
