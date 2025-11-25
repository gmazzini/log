// pcmd.c command processor by GM @2025 V 2.0
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <mysql/mysql.h>
#include "/home/www/data/log.def"

int main(void) {
  int c,vv,gg;
  char buf[1000],tok[4][100],mycall[16],*p;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  time_t epoch;

  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<4)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';
  
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
  epoch=time(NULL);
  sprintf(buf,"select mycall from user where ota='%s' and lastota+durationota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);

  p=strtok(tok[3],"=");
  buf[0]='\0';
  if(strcmp(p,"DEL")==0 || strcmp(p,"DELETE")==0)
    sprintf(buf,"delete from log where mycall='%s' and callsign='%s' and open=%lld",mycall,tok[2],atoll(tok[1]));
  else if(strcmp(p,"FT")==0 || strcmp(p,"FREQTX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set freqtx=%ld where mycall='%s' and callsign='%s' and open=%lld",atol(p)*1000L,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"FR")==0 || strcmp(p,"FREQRX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set freqrx=%ld where mycall='%s' and callsign='%s' and open=%lld",atol(p)*1000L,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"M")==0 || strcmp(p,"MODE")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set mode='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"ST")==0 || strcmp(p,"SIGNALTX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set signaltx='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"SR")==0 || strcmp(p,"SIGNALRX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set signalrx='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"C")==0 || strcmp(p,"CALL")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set callsign='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"DTS")==0 || strcmp(p,"DATETIMESTART")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set open=%lld where mycall='%s' and callsign='%s' and open=%lld",dtc2e(p),mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"DTE")==0 || strcmp(p,"DATETIMEEND")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set end='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }  
  else if(strcmp(p,"CO")==0 || strcmp(p,"CONTEST")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set contest='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"COT")==0 || strcmp(p,"CONTESTTX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set contesttx='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  else if(strcmp(p,"COR")==0 || strcmp(p,"CONTESTRX")==0){
    p=strtok(NULL,"=");
    if(p!=NULL)sprintf(buf,"update log set contestrx='%s' where mycall='%s' and callsign='%s' and open=%lld",p,mycall,tok[2],atoll(tok[1]));
  }
  if(buf[0]!='\0')mysql_query(con,buf);
  mysql_close(con);
}
