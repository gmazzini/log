#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <stdint.h>
#include <mysql/mysql.h>
#include "log.def"


int main(void){
  int c,act,vv,gg;
  char buf[1000],tok[2][100];  
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row,row1;
  
  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<2)tok[vv][gg++]=(char)c;
  }

  
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
/*
  sprintf(buf,"select mycall from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);
  */
  
    printf("Content-Type: text/plain\r\n\r\n");
  printf("%s,%s",tok[0],tok[1]);
   
  mysql_close(con);
  return 0;
}
