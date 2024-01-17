api = freeswitch.API();
        require "resources.functions.config";
destination_number = session:getVariable("c_dn");
domain_name= session:getVariable("c_domain");

--fsexport = api:execute("system", "fs_cli -x 'sofia xmlstatus profile internal reg 207'");
--fs_cli -x 'sofia status profile internal reg 201'  | grep -E 'Agent|Auth-Realm' | tr '\n' ' ' '

--c_useragent0 = api:execute("system", "fs_cli -x 'sofia status profile internal reg "..destination_number.."' | grep Agent");
c_useragent0 = api:execute("system", "fs_cli -x 'sofia status profile internal user "..destination_number.."@"..domain_name.."'| grep Agent");

session:execute("set", "c_useragent= " .. c_useragent0 .. "");

--                        session:sleep(3000);


    session:destroy();

--session:execute("set", "c_useragent2= %[[{]]regex(".. c_useragent0 ..[[|]]^[[.]]*(Bria)[[.]]*$[[|]]%1[[}]]"");




--c_useragent = api:execute("regex", "..c_useragent0..|\\b(\\w*Bria\\w*)\\b|%1)");





--log.notice("useragent: "..c_useragent.."");



--dn = destination_number

--session:execute("set", "fsexport=${regex(?<=<agent>).*?(?=<\\[[/]]agent>)|%1)");

--destination_result = trim(api:execute("regex", "m:~"..aleg_number.."~"..r.dialplan_detail_data.."~$1"));

--destnum = api:execute("regex", "(?<=<agent>).*?(?=<\\[[/]]agent>)|%1)");

--session:execute("set", "fsexport2=${regex(".. fsexport .. "|?<=<agent>).*?(?=<\\[[/]]agent>)|%1)");
--session:execute("set", "fsexport2=${regex(".. fsexport .. "|?<=<agent>).*?(?=<\\[[/]]agent>)|207");

--session:execute("set", "fsexport2=${regex(".. fsexport .."|(?<=<agent>).*?(?=<\\[[/]]agent>)|%1)");
--fsexport2 = api:execute("regex", "..fsexport|(?<=<agent>).*?(?=<\\[[//]agent>)|%1)");

--regex [[<agent>test</agent>]]|[[?<=<agent>).*?(?=<\/agent>)]]

--session:setVariable("fsexport2", fsexport2);



--log.notice("myinfo: "..destination_number.."");

--  session:execute("sleep", "1000")

