#!/bin/bash


if [ $1 == 1 ]
then
 iptables -nvL f2b-sip-auth-failure
else
 fail2ban-client set  sip-auth-failure unbanip $1
fi

