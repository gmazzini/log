// pcmd.c command processor by GM @2025 V 2.0
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <mysql/mysql.h>
#include "log.def"

int main(void) {
  int c,vv,gg;
  char buf[1000],aux1[300],tok[4][100],mycall[16],*p;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row,row1;
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
  sprintf(buf,"select mycall from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);

  p=strtok(tok[3],":");
  buf[0]='\0';
  if(strcmp(p,"DEL")==0 || strcmp(p,"DELETE")==0)sprintf(buf,"delete from log where mycall='%s' and callsign='%s' and start='%s'",mycall,tok[1],tok[2]);
  
  
FILE *fp;
  fp=fopen("/home/www/log/pcmd.txt","w");
  fprintf(fp,"%s,%s,%s,%s,%s\n",tok[0],tok[1],tok[2],tok[3],buf);
  fclose(fp);
  
}
