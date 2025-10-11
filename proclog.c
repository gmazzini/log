#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOTTOK 5

int main(void) {
  int c,len;
  char buf[1001],*token,tok[TOTTOK][100];

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
  if(c!=TOTTOK)exit(0);

    MYSQL *con = mysql_init(NULL);
    mysql_real_connect(con, dbhost, dbuser, dbpassword, dbname, 0, NULL, 0);
    mysql_query(con, "SET time_zone = '+00:00'");

    printf("<pre>\n%s\n</pre>\n",buf);

    mysql_close(con);

    return 0;
}
