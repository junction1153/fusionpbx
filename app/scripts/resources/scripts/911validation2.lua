--define the trim function
	require "resources.functions.trim"

--define the explode function
	require "resources.functions.explode"

--create the api object
	api = freeswitch.API();
	uuid = argv[1];
	domain_uuid = argv[2];
	if not uuid or uuid == "" then return end;
	caller = api:executeString("uuid_getvar " .. uuid .. " outbound_caller_id_number");
	callee = api:executeString("uuid_getvar " .. uuid .. " destination_number");

--clean local country prefix from caller (ex: +39 or 0039 in Italy)
--	exitCode    = api:executeString("uuid_getvar " .. uuid .. " default_exitcode");
--	countryCode = api:executeString("uuid_getvar " .. uuid .. " default_countrycode");
--
--	if ((countryCode ~= nil) and (string.len(countryCode) > 0)) then
--
--		countryPrefix = "+" .. countryCode;
--
--		if (string.sub(caller, 1, string.len(countryPrefix)) == countryPrefix) then
--			cleanCaller = string.sub(caller, string.len(countryPrefix)+1);
--			freeswitch.consoleLog("NOTICE", "[cidlookup] ignoring local international prefix " .. countryPrefix .. ": " .. caller .. " ==> " .. cleanCaller .. "\n");
--			caller = cleanCaller;
--		else
--			if ((exitCode ~= nil) and (string.len(exitCode) > 0)) then
--
--				countryPrefix = exitCode .. countryCode;
--
--				if (string.sub(caller, 1, string.len(countryPrefix)) == countryPrefix) then
--					cleanCaller = string.sub(caller, string.len(countryPrefix)+1);
--					freeswitch.consoleLog("NOTICE", "[cidlookup] ignoring local international prefix " .. countryPrefix .. ": " .. caller .. " ==> " .. cleanCaller .. "\n");
--					caller = cleanCaller;
--				end;
--			end;
--		end;
--	end;

--include config.lua
	require "resources.functions.config";

--include json library
	local json
	if (debug["sql"]) then
		json = require "resources.functions.lunajson"
	end

if (params:getHeader('variable_effective_caller_id_number') ~= nil) then
    callerid_number = params:getHeader('variable_effective_caller_id_number')
    callerid_name = params:getHeader('variable_effective_caller_id_name')
Logger.info("[Dialplan] Caller Id name / number  : "..callerid_name.." / "..callerid_number)
end

--connect to the database
	local Database = require "resources.functions.database";
	dbh = Database.new('system');
	if (database["type"] == "mysql") then
		sql = "SELECT CONCAT(v_contacts.contact_name_given, ' ', v_contacts.contact_name_family) AS name FROM v_contacts ";
	elseif (database["type"] == "pgsql") then
		sql = "SELECT cid FROM c_cid911validate WHERE cid = '12126609962' ";
	else
		sql = "SELECT cid FROM c_cid911validate WHERE cid = '12126609962' ";
	end
--	sql = sql .. "INNER JOIN v_contact_phones ON v_contact_phones.contact_uuid = v_contacts.contact_uuid ";
--	sql = sql .. "INNER JOIN v_destinations ON v_destinations.domain_uuid = v_contacts.domain_uuid AND v_destinations.destination_number = :callee ";
	
	local params;
	if ((not domain_uuid) or (domain_uuid == "")) then
		sql = sql .. " WHERE cid = ':callee' ";
		params = {caller = caller, callee = callee};
	else
                sql = sql .. " WHERE cid = ':callee' ";
		params = {caller = caller, domain_uuid = domain_uuid, callee = callee};
	end
--
--	if (debug["sql"]) then
--		freeswitch.consoleLog("notice", "[cidlookup] SQL: "..sql.."; params:" .. json.encode(params) .. "\n");
--	end
--
--	dbh:query(sql, params, function(row)
--		cid = row.cid;
--	end);

	if (cid == nil) then
		freeswitch.consoleLog("NOTICE", "[cidlookup] caller name from contacts db is nil\n");
	else
		freeswitch.consoleLog("NOTICE", "[cidlookup] caller name from contacts db: "..name.."\n");
	end

--check if there is a record, if it not then use cidlookup
	if (cid == nil) then
                freeswitch.consoleLog("NOTICE", "Hello: "..callee.."\n");
--  session:execute("sleep", "5000");
session:setAutoHangup(false)

--		cidlookup_exists = api:executeString("module_exists mod_cidlookup");
		if (cid == "12126609962") then
                freeswitch.consoleLog("NOTICE", "Hello2 "..callee.."\n");
--  session:execute("sleep", "5000");
session:setAutoHangup(false)

--		    name = api:executeString("cidlookup " .. caller);
		end
	end

--set the caller id name
--	if ((name ~= nil) and (string.len(name) > 0)) then
--		api:executeString("uuid_setvar " .. uuid .. " ignore_display_updates false");
--	
--		freeswitch.consoleLog("NOTICE", "[cidlookup] uuid_setvar " .. uuid .. " caller_id_name " .. name);
--		api:executeString("uuid_setvar " .. uuid .. " caller_id_name " .. name);
--
--		freeswitch.consoleLog("NOTICE", "[cidlookup] uuid_setvar " .. uuid .. " effective_caller_id_name " .. name);
--		api:executeString("uuid_setvar " .. uuid .. " effective_caller_id_name " .. name);
--	end
