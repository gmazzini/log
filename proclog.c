#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOTTOK 5

MYSQL_ROW searchcty(MYSQL *,char *);
char *myband[434]={[0]="0",[1]="160",[3]="80",[5]="60",[7]="40",[10]="30",[14]="20",[18]="17",[21]="15",[24]="12",[28]="10",[29]="10",[50]="6",[144]="2",[145]="2",[430]="0.7",[431]="0.7",[432]="0.7",[433]="0.7"};
char *mymode(char *s){
 if(!s)return"ND";
 if(!strcmp(s,"CW"))return"CW";
 if(!strcmp(s,"FT8")||!strcmp(s,"RTTY")||!strcmp(s,"MFSK")||!strcmp(s,"FT4")||!strcmp(s,"PKT")||!strcmp(s,"TOR")||!strcmp(s,"AMTOR")||!strcmp(s,"PSK"))return"DG";
 if(!strcmp(s,"SSB")||!strcmp(s,"USB")||!strcmp(s,"LSB")||!strcmp(s,"FM")||!strcmp(s,"AM"))return"PH";
 return"ND";
}

int main(void) {
  int c,len,act,ndata2[10];
  char buf[1001],aux1[300],aux2[300],*token,tok[TOTTOK][100],mycall[16];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row,row1;
  struct tm ts,te;
  time_t epoch,td;
  long lastserial,l1,l2;
  struct data2 {char lab[10]; long num;} data2[10][100];

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
    sprintf(buf,"select callsign,start from log where mycall='%s' and serial=0 order by start",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      lastserial++;
      sprintf(aux1,"update log set serial=%ld where mycall='%s' and callsign='%s' and start='%s'",lastserial,mycall,row[0],row[1]);
      mysql_query(con,aux1);
    }
    mysql_free_result(res);
    if(act<=5)sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where mycall='%s' and serial<=%ld order by serial desc limit %d",mycall,lastserial-atol(tok[2]),atoi(tok[3]));
    else sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where callsign like '%s' and mycall='%s' order by start desc limit %d offset %ld",tok[4],mycall,atoi(tok[3]),atol(tok[2]));
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
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
 
  if(act==9){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    l1=l2=0;
    sprintf(buf,"select start,callsign from log where mycall='%s' and dxcc=0",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      row1=searchcty(con,row[1]);
      if(row1!=NULL){
        sprintf(aux1,"Update log set dxcc=%d where mycall='%s' and start='%s' and callsign='%s' and dxcc=0",atoi(row1[2]),mycall,row[0],row[1]);
        mysql_query(con,aux1);
        l1++;
      }
      else l2++;
    }
    // MANCA PROCESSA LABEL
    mysql_free_result(res);
    printf("Set dxcc: %ld\nNot found dxcc: %ld</pre>",l1,l2);
    goto end;
  }

  if(act==10){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    sprintf(buf,"select callsign,freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='%s' limit 50000",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    ndata2[0]=ndata2[1]=ndata2[2]=ndata2[3]=0;
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      if(row[1][0]=='\0')continue;
      sprintf(aux1,"%s%s",mymode(row[2]),myband[(int)(atol(row[1])/1000000.0)]);
      for(l1=0;l1<ndata2[0];l1++)if(strcmp(data2[0][l1].lab,aux1)==0)break;
      if(l1==ndata2[0]){strcpy(data2[0][ndata2[0]].lab,aux1); data2[0][ndata2[0]].num=1; ndata2[0]++; }
      else data2[0][l1].num++;
      if(atoi(row[3])==1){
        for(l1=0;l1<ndata2[1];l1++)if(strcmp(data2[1][l1].lab,aux1)==0)break;
        if(l1==ndata2[1]){strcpy(data2[1][ndata2[1]].lab,aux1); data2[1][ndata2[1]].num=1; ndata2[1]++; }
        else data2[1][l1].num++;
      }
      if(atoi(row[4])==1){
        for(l1=0;l1<ndata2[2];l1++)if(strcmp(data2[2][l1].lab,aux1)==0)break;
        if(l1==ndata2[2]){strcpy(data2[2][ndata2[2]].lab,aux1); data2[2][ndata2[2]].num=1; ndata2[2]++; }
        else data2[2][l1].num++;
      }
      if(atoi(row[5])==1){
        for(l1=0;l1<ndata2[3];l1++)if(strcmp(data2[3][l1].lab,aux1)==0)break;
        if(l1==ndata2[3]){strcpy(data2[3][ndata2[3]].lab,aux1); data2[3][ndata2[3]].num=1; ndata2[3]++; }
        else data2[3][l1].num++;
      }
     
     
    }
    for(l1=0;l1<ndata2[0];l1++){
      printf("%s %ld",data2[0][l1].lab,data2[0][l1].num);
      for(l2=0;l2<ndata[1];l2++)if(strcmp(data2[0][l1].lab,data2[1][l2].lab)==0)break;
      printf(" %ld",(l2<ndata[1])?data2[0][l1].num:0);
    }
    printf("</pre>");
    goto end;
  }
  
  end:
  mysql_close(con);
  return 0;
}

MYSQL_ROW searchcty(MYSQL *con,char *incall){
  char buf[1000],*p,call[20];
  static MYSQL_RES *res;
  static MYSQL_ROW row;
  int i,n;
  const char *suffixes[]={"P","M","LH","MM","AM","A","QRP","0","1","2","3","4","5","6","7","8","9"};
  n=sizeof(suffixes)/sizeof(suffixes[0]);
  strcpy(call,incall);
  p=strrchr(call,'/');
  if(p){
    for(i=0;i<n;i++)if(strcmp(p+1,suffixes[i])==0)break;
    if(i<n)*p='\0';
  }
  p=strrchr(call,'/');
  if(p){
    n=strlen(call);
    if((p-call)<(n-(p-call)-1))*p='\0';
    else strcpy(call,p+1);
  }
  n=strlen(call);
  for(i=n;i>0;i--){
    call[i]='\0';
    sprintf(buf,"select base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift from cty where prefix='%s' limit 1",call);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    row=mysql_fetch_row(res);
    if(row!=NULL)break;
  }
  return row;
}
