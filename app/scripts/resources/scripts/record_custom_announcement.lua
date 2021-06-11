--include config.lua
	require "resources.functions.config";

--create the api object
	api = freeswitch.API();

	require "resources.functions.channel_utils";

--check if the session is ready
	if not session:ready() then return end

--answer the call
--	session:answer();
--	local caller_id_number = session:getVariable("caller_id_number_cust");


record_path = session:getVariable("record_path");
record_name = session:getVariable("record_name");
uuid_record = session:getVariable("uuid_record");
uuid = session:getVariable("uuid");


--	session:execute("set","record_path="..record_path);
--        session:execute("set","record_name="..record_name);

--        session:execute("set","recording_follow_transfer=true");
--        session:execute("set","record_append=true");
--        session:execute("set","record_in_progress=true");
--        session:execute("set","..uuid_record ..uuid start ..record_path/..record_name");
        session:execute("set","${uuid_record ${uuid} start ${record_path}/${record_name}}");
	session:execute("playback","misc/call_monitoring_blurb.wav");

    session:destroy();

