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
  for(c=0;c<TOTTOK;c++){
    printf("%d %s<br>\n",c,token);
    strcpy(tok[c],token);
    token=strtok(NULL,",");
  //  if(token==NULL)break;
  }
//  if(c!=TOTTOK-1)exit(1);
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
  epoch=time(NULL);
  sprintf(buf,"select mycall from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);
  
  if(strcmp(tok[1],"a01")==0){
    printf("<pre>");
    sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where mycall='%s' and serial<=%ld order by serial desc limit %d",mycall,1000000,atoi(tok[3]));
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)exit(1);
      // printf("%s",row[2]);
      printf("%s%5s %12s %7.1f %4s %5s %5s %-3s ",row[0],"xxxx",row[2],atol(row[3])/1000.0,row[5],row[6],row[7],"xxx");
      printf("\n");
    }
    mysql_free_result(res);
    printf("</pre>");
  }
  
  printf("<pre>\n%s\n%s</pre>\n",buf,mycall);
  mysql_close(con);
  return 0;
}
