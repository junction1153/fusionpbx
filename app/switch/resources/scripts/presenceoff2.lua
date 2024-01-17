require "resources.functions.split"
local api = require "resources.functions.api"
local log = require "resources.functions.log".presence

--domain_name = env:getHeader("domain_name") ;
--caller_id_number = env:getHeader("caller_id_number");
--domain_name = "shapeint.pbx02.jcnt.net" ;
--caller_id_number = "206" ;

caller_id_number = argv[1];
domain_name = argv[2];
proto = "sip";
from = "301@omranitaub.pbx02.jcnt.net";
login = "301@omranitaub.pbx02.jcnt.net";


userid = caller_id_number  .. "@" .. domain_name

        uuid = uuid or api:execute('create_uuid')

        local event = freeswitch.Event("PRESENCE_IN");
        event:addHeader("proto", proto);
        event:addHeader("event_type", "presence");
        event:addHeader("alt_event_type", "dialog");



        event:addHeader("Presence-Call-Direction", "inbound");

--        event:addHeader("from", userid);
--        event:addHeader("login", userid);
--        event:addHeader("unique-id", uuid);
--        event:addHeader("status", "Active (1 waiting)");
--      if on then
--              event:addHeader("answer-state", "confirmed");
--              event:addHeader("rpid", "unknown");
--              event:addHeader("event_count", "1");
--      else
                event:addHeader("answer-state", "terminated");
--      end

        -- log.debug(event:serialize())

        event:fire();
