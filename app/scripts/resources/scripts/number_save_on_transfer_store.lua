-- Save number_answered / original caller_id to database

--api = freeswitch.API()

if (session:ready()) then
    answered_extension = session:getVariable("dialed_user")

    caller_id = session:getVariable("restored_number_on_transfer")
    caller_name = session:getVariable("restored_number_on_transfer_name")

    if (caller_id == nil) then
        caller_id = session:getVariable("sip_from_user")
    end
    if (caller_name == nil) then
        caller_name = session:getVariable("sip_from_display")
    end
    if (answered_extension ~= nil and caller_id ~= nil) then
        freeswitch.consoleLog("INFO", "[NUMBER_ON_TRANSFER_SAVE] Got answered call from "..caller_id.." to "..answered_extension.."\n")
        session:execute('db', 'insert/number_transfer_store/'..answered_extension..'/'..caller_id)
        if (caller_name ~= nil) then
            session:execute('db', 'insert/number_transfer_store/'..answered_extension..'_name/'..caller_name)
        end
    end
end
