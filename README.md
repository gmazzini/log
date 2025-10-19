# log
easy and immediate web log
in (continuos) developing
by IK4LZH


On the lzh simple input format
DAAAAMMYY data AAAA MM YY
Fxxx frequency xxx in MHz
Mxxx mode xxx
HHMM CALL [sigtx [sigrx]] qso time HH MM with CALL optinal tx signal and if present optiona rx signal
regarding D F M command when set remain since new set
if not set and HHMM appears processing is stopped


PS for IOTA: cat xxx.cbr| grep "QSO:" | awk '{print $1" "$2" " $3" "$4" "$5" "$6" "$7" "$8$9" "$10" "$11" "$12$13" "$14}'

