-- valet park dialplan replacement
-- get the valet park lot passed as argv[1]
--create the api object
    api = freeswitch.API();
        require "resources.functions.channel_utils";
        local log = require "resources.functions.log".follow_me
        local cache = require "resources.functions.cache"
        local Database = require "resources.functions.database"

    park_lot = argv[1];

--    custom_cid_num = "test"
--    custom_cid_num = session:getVariable("custom_valetcid");


--freeswitch.consoleLog("NOTICE", "xxxxxxx Park Status: timeout" ..custom_cid_num);


    hold_music    = session:getVariable("hold_music");

    if not park_lot or park_lot == "" then return end;
--get active lots for the domain
    domain_name = session:getVariable("domain_name");
    context     = session:getVariable("call_direction");

--sip_rh_P-Asserted-Identity = session:getVariable("sip_rh_P-Asserted-Identity");
    
    --freeswitch.consoleLog("err",domain_name);
    --freeswitch.consoleLog("err",context);

    lot_in_use = string.find(api:executeString("valet_info park@" .. domain_name),park_lot) or false;

    referred_by_user=string.match(session:getVariable("sip_h_Referred-By") or "",'sip:(.*)@.*') or "";

    if not(referred_by_user=="") then
    -- if trying to park to the occupied lot return call back to the referrer
        if lot_in_use then
--            session:execute("transfer", referred_by_user .." XML "..context);
            session:execute("transfer", referred_by_user .." XML "..domain_name);
            return;
        end
    else
    -- user just pressed an empty parking lot button... respond with 404
        if not lot_in_use then
            session:execute("respond", "404");
            return;
        end
    end
-- do the actual valet park stuff here
    session:setVariable("valet_hold_music",hold_music);


  --     session:setVariable("sip_rh_P-Asserted-Identity","sip:"..custom_cid_num);
 --    session:setVariable("sip_rh_P-Asserted-Identity","sip:44433333");


    if (session:getVariable("valet_parking_timeout") == nil) then
        -- Set to 10 minutes if not specified
        session:setVariable("valet_parking_timeout","600");
    end

    if (session:getVariable("valet_parking_orbit_exten") == nil) then
        -- Set to the referred users extension if not specified
        session:setVariable("valet_parking_orbit_exten",referred_by_user);
    end

    freeswitch.consoleLog("parkinfo", "park@"..domain_name.." "..park_lot);
    session:execute("valet_park", "park@"..domain_name.." "..park_lot);
