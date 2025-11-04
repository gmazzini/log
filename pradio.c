// pradio.c radio function by GM @2025 V 2.0
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <signal.h>
#include <stdlib.h>
#include <sys/socket.h>
#include <netdb.h>
#include <mysql/mysql.h>
#include "log.def"

int main(void){
  int fd,r,i,vv,gg,c;
  struct addrinfo *res;
  char h,buf[256],tok[3][100],ip[20],port[10],mode[10],aux1[300];
  long freq;
  time_t epoch;
  MYSQL *con;
  MYSQL_RES *rrr;
  MYSQL_ROW row;
  
  printf("Content-Type: text/plain\r\n\r\n0,ND\n");
  // 0:ota 1:{R=read S=set} 2=freq,mode
  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<3)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';

      printf("-\n");

  con=mysql_init(NULL);
  if(con==NULL){printf("0,ND\n"); exit(0);}
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL){mysql_close(con); printf("0,ND\n"); exit(0);}
  epoch=time(NULL);
  sprintf(buf,"select rigctld_ip,rigctld_port from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); rrr=mysql_store_result(con); row=mysql_fetch_row(rrr);
  if(row==NULL){mysql_close(con); printf("0,ND\n"); exit(0);}
  strcpy(ip,row[0]); strcpy(port,row[1]);
  mysql_free_result(rrr);
  mysql_close(con);
  if(getaddrinfo(ip,port,&(struct addrinfo){.ai_socktype=SOCK_STREAM},&res)!=0){printf("0,ND\n"); exit(0);}
  fd=socket(res->ai_family,res->ai_socktype,res->ai_protocol);
  if(fd<0){printf("0,ND\n"); freeaddrinfo(res); exit(0);}
  r=connect(fd,res->ai_addr,res->ai_addrlen);
  if(r==-1){printf("0,ND\n"); close(fd); freeaddrinfo(res); exit(0);}
  
  if(tok[1][0]=='R'){
    send(fd,"sfim\n",5,0);
    for(i=0;i<5;){
      r=recv(fd,&h,1,0);
      if(r<=0)break;
      if(h=='\n'){if(i==2)putchar(','); i++;}
      else if(i==2||i==4)putchar(h);
    }
    putchar('\n');
  }
  else if(tok[1][0]=='S'){
    sscanf(tok[2],"%ld:%s",&freq,mode);
    sprintf(aux1,"F %ld\n",freq);
    send(fd,aux1,strlen(aux1),0);
    sprintf(aux1,"M %s 0\n",mode);
    send(fd,aux1,strlen(aux1),0);
    printf("%ld,%s\n",freq,mode);
  }
  else printf("0,ND\n");
  close(fd);
  freeaddrinfo(res);
}
