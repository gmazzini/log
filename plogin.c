#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "log.def"

int main(void){
  int c,vv,gg;
  char buf[1000],tok[2][100];
  time_t epoch;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  
  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<2)tok[vv][gg++]=(char)c;
  }
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  epoch=time(NULL);
  printf("1\n");
  sprintf(buf,"select ota from user where mycall='%s' and md5password='%s' and lota>%ld limit 1",tok[0],tok[1],epoch);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    printf("2\n");

  if(row==NULL)exit(1);
  printf("Content-Type: text/plain\r\n\r\n");
  printf("%s",row[0]);
  mysql_free_result(res);
  mysql_close(con);
  return 0;
}
