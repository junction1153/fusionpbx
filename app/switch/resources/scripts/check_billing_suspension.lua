-- Set the API endpoint URL and Bearer token from the argv values

domain_uuid = session:getVariable("domain_uuid");
is_internal_call = session:getVariable("from_user_exists");

if not domain_uuid then
 --   freeswitch.consoleLog("error", "[check-suspended] Missing domain_uuid argument")
    return
end

local Database = require "resources.functions.database"

local dbh = Database.new('system')

local sql = [[
    select domain_setting_value from v_domain_settings where domain_setting_category = 'company' and domain_uuid = :domain_uuid and domain_setting_enabled = true and domain_setting_subcategory = 'billing_suspension'
]]

local params = {domain_uuid = domain_uuid}
-- freeswitch.consoleLog("notice", "[check-suspended] sql: " .. sql)
local response = dbh:first_row(sql, params)
dbh:release()

if response then
    if response.domain_setting_value == "true" then
        freeswitch.consoleLog("notice", "[check-suspended] Account suspended: " .. response.domain_setting_value)

        if is_internal_call == "true" then

            if session:ready() then
                session:execute("playback", "silence_stream://1000")
            end
            if session:ready() then
                session:streamFile("misc/suspended")
            end
--            if session:ready() then
--                session:streamFile("ivr/ivr-please_contact.wav")
--            end
--            if session:ready() then
--                session:streamFile("ivr/ivr-the_billing_department.wav")
--            end
--            if session:ready() then
--                session:streamFile("currency/and.wav")
--            end
--
--            if session:ready() then
--                session:streamFile("ivr/ivr-speak_to_a_customer_service_representative.wav")
--            end

            if session:ready() then
                session:sleep(1000) -- Wait for 1 second (1000 milliseconds)
            end
        end

        if session:ready() then
            freeswitch.consoleLog("notice", "[check-suspended] HANGUP ")
            -- inbound call message

            if session:ready() then
                session:execute("playback", "silence_stream://1000")
            end
        if session:ready() then
            session:streamFile("ivr/ivr-call_cannot_be_completed_as_dialed.wav")
        end
        if session:ready() then
            session:streamFile("ivr/ivr-please_check_number_try_again")
        end

            session:hangup("CALL_REJECTED")
        end


    end
else
    freeswitch.consoleLog("notice", "[check-suspended] No result found for domain_uuid: " .. domain_uuid)
end
