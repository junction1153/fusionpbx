--      Software distributed under the License is distributed on an "AS IS" basis,
--      WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
--      for the specific language governing rights and limitations under the
--      License.
--
--      The Original Code is FusionPBX
--
--      The Initial Developer of the Original Code is
--      Mark J Crane <markjcrane@fusionpbx.com>
--      Copyright (C) 2010-2019
--      the Initial Developer. All Rights Reserved.
--
--      Contributor(s):
--      Mark J Crane <markjcrane@fusionpbx.com>

--set default variables
        min_digits = "1";
        max_digits = "11";
        max_tries = "3";
        digit_timeout = "3000";

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
                pin_number = session:getVariable("pin_number");
                sounds_dir = session:getVariable("sounds_dir");
                domain_uuid = session:getVariable("domain_uuid");
                domain_name = session:getVariable("domain_name");
                extension_uuid = session:getVariable("extension_uuid");
        end

--get the variables
        if (session:ready()) then
                enabled = session:getVariable("enabled");
                pin_number = session:getVariable("pin_number");
                sounds_dir = session:getVariable("sounds_dir");
                domain_uuid = session:getVariable("domain_uuid");
                domain_name = session:getVariable("domain_name");
                extension_uuid = session:getVariable("extension_uuid");
        end

--set the sounds path for the language, dialect and voice
        if (session:ready()) then
                default_language = session:getVariable("default_language");
                default_dialect = session:getVariable("default_dialect");
                default_voice = session:getVariable("default_voice");
                if (not default_language) then default_language = 'en'; end
                if (not default_dialect) then default_dialect = 'us'; end
                if (not default_voice) then default_voice = 'callie'; end
        end

--wait a moment to sleep
        if (session:ready()) then
                session:sleep(1000);
        end

--connect to the database
        local Database = require "resources.functions.database";
        dbh = Database.new('system');

        local settings = Settings.new(dbh, domain_name, domain_uuid);

--include json library
        --debug["sql"] = true;
        local json
        if (debug["sql"]) then
                json = require "resources.functions.lunajson"
        end

--determine whether to update the dial string
        local sql = "select * from v_extensions ";
        sql = sql .. "where domain_uuid = :domain_uuid ";
        sql = sql .. "and extension_uuid = :extension_uuid ";
        local params = {domain_uuid = domain_uuid, extension_uuid = extension_uuid};
        if (debug["sql"]) then
                freeswitch.consoleLog("notice", "[custom_callback] " .. sql .. "; params:" .. json.encode(params) .. "\n");
        end
        dbh:query(sql, params, function(row)
                extension = row.extension;
                number_alias = row.number_alias or '';
                accountcode = row.accountcode;
                custom_callback = row.custom_callback;
--              forward_all_destination = row.forward_all_destination;
--              forward_all_enabled = row.forward_all_enabled;
                context = row.user_context;
        end);

--send information to the console
        if (session:ready()) then
                freeswitch.consoleLog("NOTICE", "[custom_callback] custom_callback "..custom_callback.."\n");
                freeswitch.consoleLog("NOTICE", "[custom_callback] extension "..extension.."\n");
                freeswitch.consoleLog("NOTICE", "[custom_callback] accountcode "..accountcode.."\n");
                --freeswitch.consoleLog("NOTICE", "[custom_callback] enabled before "..enabled.."\n");
        end

--toggle do not disturb
        if (enabled == "toggle") then
                if (custom_callback == "true") then
                        enabled = "false";
                else
                        enabled = "true";
                end
        end

--send information to the console
        if (session:ready()) then
                freeswitch.consoleLog("NOTICE", "[custom_callback] enabled "..enabled.."\n");
        end

--set the dial string
        if (enabled == "true") then
                local user = (number_alias and #number_alias > 0) and number_alias or extension;
        end

--set do not disturb
        if (enabled == "true") then
                --set do_not_disturb_enabled
                        custom_callback_enabled = "true";
                --notify the caller
                        if (session:ready()) then
                                session:streamFile(sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-enabled.wav");
                        end
        end

--unset do not disturb
        if (enabled == "false") then
                --set fdo_not_disturb_enabled
                        custom_callback_enabled = "false";
                --notify the caller
                        if (session:ready()) then
                                session:streamFile(sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-disabled.wav");
                       end
        end

--update the extension
        sql = "update v_extensions set ";
        if (enabled == "true") then
                sql = sql .. "custom_callback = 'true' ";
--              sql = sql .. "forward_all_enabled = 'false' ";
        else
                sql = sql .. "custom_callback = 'false' ";
        end
        sql = sql .. "where domain_uuid = :domain_uuid ";
        sql = sql .. "and extension_uuid = :extension_uuid ";
        local params = {domain_uuid = domain_uuid, extension_uuid = extension_uuid};
        if (debug["sql"]) then
                freeswitch.consoleLog("notice", "[custom_callback] "..sql.."; params:" .. json.encode(params) .. "\n");
        end
        dbh:query(sql, params);
--clear the cache
        if extension and #extension > 0 and cache.support() then
                cache.del("directory:"..extension.."@"..context);
                if #number_alias > 0 then
                        cache.del("directory:"..number_alias.."@"..context);
                end
        end

--wait for the file to be written before proceeding
        if (session:ready()) then
                session:sleep(1000);
        end

--end the call
        if (session:ready()) then
                session:hangup();
        end

-- BLF for display DND status
--      blf.dnd(enabled == "true", extension, number_alias, domain_name)

-- Turn off BLF for call forward
--      if forward_all_enabled == 'true' and enabled == 'true' then
--              blf.forward(false, extension, number_alias,
--                      forward_all_destination, nil, domain_name
--              )
--      end

--disconnect from database
        dbh:release()
