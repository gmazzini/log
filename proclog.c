#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOTTOK 5

int main(void) {
  int c,len;
  char buf[1001],*token,tok[TOTTOK][100],mycall[16];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  time_t epoch;

  for(len=0;;){
    c=getchar();
    if(c==EOF)break;
    buf[len]=(char)c;
    if(len<1000)len++;
  }
  buf[len++]='\0';
  printf("Status: 200 OK\r\n");
  printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
  token=strtok(buf,",");
  for(c=0;;c++){
    printf("%d %s<br>\n",c,token);
    strcpy(tok[c],token);
    token=strtok(NULL,",");
    if(token==NULL)break;
  }
  if(c!=TOTTOK-1)exit(1);
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
  epoch=time();
  sprintf(buf,"SELECT mycall FROM user WHERE ota='%s and lota<%ld' LIMIT 1",tok[0],epoch);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);

  
    printf("<pre>\n%s\n%s</pre>\n",buf,mycall);

    mysql_close(con);

    return 0;
}
