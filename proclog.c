#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOTTOK 5

int main(void) {
  int c,len,act;
  char buf[1001],aux1[100],aux2[100],*token,tok[TOTTOK][100],mycall[16];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  struct tm ts,te;
  time_t epoch,td;
  long lastserial;

  for(len=0;;){
    c=getchar();
    if(c==EOF)break;
    buf[len]=(char)c;
    if(len<1000)len++;
  }
  buf[len++]='\0';
  token=strtok(buf,",");
  for(c=0;;c++){
    strcpy(tok[c],token);
    token=strtok(NULL,",");
    if(token==NULL)break;
  }
  if(c!=TOTTOK-1)exit(1);
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
  act=0; if(tok[1][0]=='a')act=atoi(tok[1]+1);

  if(act==5){
    printf("Content-Type: text/plain\r\n\r\n");
    sprintf(buf,"select max(serial) from log where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    lastserial=atol(row[0]);
    mysql_free_result(res);
    sprintf(aux1,"%.4s-%.2s-%.2s 00:00:00",tok[4],tok[4]+5,tok[4]+8);
    sprintf(buf,"select serial from log where mycall='%s' and start>='%s' order by start limit 1",mycall,aux1);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    printf("%ld\n",lastserial-atol(row[0]));
    mysql_free_result(res);
    goto end;
  }

  if(act<=8){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    sprintf(buf,"select max(serial) from log where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    lastserial=atol(row[0]);
    mysql_free_result(res);
    // MANCA UPDATE SERIAL

    if(act<=5)sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where mycall='%s' and serial<=%ld order by serial desc limit %d",mycall,lastserial-atol(tok[2]),atoi(tok[3]));
    else sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where callsign like '%s' and mycall='%s' order by start desc limit %d offset %ld",tok[4],mycall,atoi(tok[3]),atol(tok[2]));
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)exit(1);
      aux1[0]='\0';
      if(atoi(row[8])==1)strcat(aux1,"L");
      if(atoi(row[9])==1)strcat(aux1,"E");
      if(atoi(row[10])==1)strcat(aux1,"Q");
      sscanf(row[1],"%d-%d-%d %d:%d:%d",&te.tm_year,&te.tm_mon,&te.tm_mday,&te.tm_hour,&te.tm_min,&te.tm_sec); te.tm_year-=1900; te.tm_mon-=1;
      sscanf(row[0],"%d-%d-%d %d:%d:%d",&ts.tm_year,&ts.tm_mon,&ts.tm_mday,&ts.tm_hour,&ts.tm_min,&ts.tm_sec); ts.tm_year-=1900; ts.tm_mon-=1;
      td=mktime(&te)-mktime(&ts);
      if(td==0)strcpy(aux2,"(0s)");
      else if(td<60)sprintf(aux2,"(%lds)",td);
      else if(td<3600)sprintf(aux2,"(%ldm)",td/60);
      else sprintf(aux2,"(%ldh)",td/3600);
      // MANCA BUTTON
      printf("%s%5s %12s %7.1f %4s %5s %5s %-3s ",row[0],aux2,row[2],atol(row[3])/1000.0,row[5],row[6],row[7],aux1);
      if(row[13][0]!='\0')printf(" (%s,%s,%s)",row[13],row[11],row[12]);
      if(atol(row[4])>0&&atol(row[4])!=atol(row[3]))printf(" [%+.1f]",(atol(row[4])-atol(row[3]))/1000.0);
      printf("\n");
    }
    mysql_free_result(res);
    printf("</pre>");
    goto end;
  }
  
  end:
  mysql_close(con);
  return 0;
}
