-- Cleanup database

api = freeswitch.API()
sip_to_user = env:getHeader("variable_last_sent_callee_id_number")

if (sip_to_user ~= nil) then
--serialized = env:serialize()
--freeswitch.consoleLog("INFO","[hangup]\n" .. serialized .. "\n")

    freeswitch.consoleLog("INFO", "[DB_CLEANUP] Cleaning " .. sip_to_user .. "\n")
    api:executeString('db delete/number_transfer_store/'..sip_to_user)
    api:executeString('db delete/number_transfer_store/'..sip_to_user..'_name')
end
