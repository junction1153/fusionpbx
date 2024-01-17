--
--	FusionPBX
--	Version: MPL 1.1
--
--	The contents of this file are subject to the Mozilla Public License Version
--	1.1 (the "License"); you may not use this file except in compliance with
--	the License. You may obtain a copy of the License at
--	http://www.mozilla.org/MPL/
--
--	Software distributed under the License is distributed on an "AS IS" basis,
--	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
--	for the specific language governing rights and limitations under the
--	License.
--
--	The Original Code is FusionPBX
--
--	The Initial Developer of the Original Code is
--	Mark J Crane <markjcrane@fusionpbx.com>
--	Copyright (C) 2010-2019
--	the Initial Developer. All Rights Reserved.
--
--	Contributor(s):
--	Mark J Crane <markjcrane@fusionpbx.com>

--set default variables
	min_digits = "1";
	max_digits = "11";
	max_tries = "3";
	digit_timeout = "3000";

        require "resources.functions.channel_utils";
        local log = require "resources.functions.log".follow_me
        local cache = require "resources.functions.cache"
        local Database = require "resources.functions.database"
        local json


--include config.lua
        require "resources.functions.config";

--create the api object
        api = freeswitch.API();

--define the trim function
        require "resources.functions.trim";

--define the explode function
        require "resources.functions.explode";

--create the api object
        api = freeswitch.API();

--include config.lua
        require "resources.functions.config";

--      local blf = require "resources.functions.blf"
        local cache = require "resources.functions.cache"
        local Settings = require "resources.functions.lazy_settings"
--      local notify = require "app.feature_event.resources.functions.feature_event_notify"


--answer the call
	if (session:ready()) then
		session:answer();
	end

--get the variables
	if (session:ready()) then
		enabled = session:getVariable("enabled");
--		pin_number = session:getVariable("pin_number");
--		sounds_dir = session:getVariable("sounds_dir");
		domain_name = session:getVariable("domain_name");
		extension_uuid = session:getVariable("extension_uuid");
	end

--connect to the database
	local Database = require "resources.functions.database";
--	dbh = Database.new('system');
        local dbh = Database.new('system');
--        local dbh:query(sql, params, function(rows)

--	local settings = Settings.new(dbh, domain_name, domain_uuid);

--determine whether to update the dial string
	local sql = "select * from v_extensions  ";
	sql = sql .. "where domain_uuid = :domain_uuid ";
	sql = sql .. "and extension_uuid = :extension_uuid ";
        sql = sql .. "and custom_callback = 'true' ";

                local callback_result = dbh:first_row(custom_callback, params)
              if custom_callback then
session:execute("set", "custom_callback=true");
                                log.notice("cb:JA:: TRUE");
end

--	local params = {domain_uuid = domain_uuid, extension_uuid = extension_uuid};
--	if (debug["sql"]) then
--		freeswitch.consoleLog("notice", "[custom_callback] " .. sql .. "; params:" .. json.encode(params) .. "\n");
--	end
--	dbh:query(sql, params, function(row)
--		extension = row.extension;
--		number_alias = row.number_alias or '';
--		accountcode = row.accountcode;
--		custom_callback = row.custom_callback;
--		forward_all_destination = row.forward_all_destination;
--		forward_all_enabled = row.forward_all_enabled;
--		context = row.user_context;
--	end);


--toggle do not disturb
--	if (enabled == "toggle") then
--		if (custom_callback == "true") then
--			enabled = "false";
--		else
--			enabled = "true";
--		end
--	end
--
--send information to the console
--	if (session:ready()) then
--		freeswitch.consoleLog("NOTICE", "[custom_callback] enabled "..enabled.."\n");
--	end

--set the dial string
--	if (enabled == "true") then
--		local user = (number_alias and #number_alias > 0) and number_alias or extension;
--	end

--set do not disturb
--	if (enabled == "true") then
--		--set do_not_disturb_enabled
--			custom_callback_enabled = "true";
--		--notify the caller
--			if (session:ready()) then
--				session:streamFile(sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-enabled.wav");
--			end
--	end

--unset do not disturb
--	if (enabled == "false") then
--		--set fdo_not_disturb_enabled
--			do_not_disturb_enabled = "false";
--		--notify the caller
--			if (session:ready()) then
--				session:streamFile(sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-disabled.wav");
--			end
--	end

--update the extension
--	sql = "update v_extensions set ";
--	if (enabled == "true") then
--		sql = sql .. "custom_callback = 'true' ";
----		sql = sql .. "forward_all_enabled = 'false' ";
--	else
--		sql = sql .. "custom_callback = 'false' ";
--	end
--	sql = sql .. "where domain_uuid = :domain_uuid ";
--	sql = sql .. "and extension_uuid = :extension_uuid ";
--	local params = {domain_uuid = domain_uuid, extension_uuid = extension_uuid};
--	if (debug["sql"]) then
--		freeswitch.consoleLog("notice", "[custom_callback] "..sql.."; params:" .. json.encode(params) .. "\n");
--	end
--	dbh:query(sql, params);

--disconnect from database
--	dbh:release()
