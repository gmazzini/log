// pradio.c radio function by GM @2025 V 2.0
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <signal.h>
#include <stdlib.h>
#include <sys/socket.h>
#include <netdb.h>
#include <mysql/mysql.h>
#include <arpa/inet.h>
#include <signal.h>
#include "/home/www/data/log.def"

static void alarm_handler(int sig){(void)sig;}
static const char *modets890s[16] = {"Unused","LSB","USB","CW","FM","AM","FSK","CW-R","Unused","FSK-R","PSK","PSK-R","LSB-D","USB-D","FM-D","AM-D"};

int main(void){
  int i,vv,gg,s,m;
  struct sockaddr_in a;
  char c,buf[256],tok[3][100],b[100],cmd[128],*ip,*user,*pass,*p1;
  long port,freq;
  time_t epoch;
  MYSQL *con;
  MYSQL_RES *rrr;
  MYSQL_ROW row;
  struct timeval tv;
  
  printf("Content-Type: text/plain\r\n\r\n");
  // 0:ota 1:{R=read S=set} 2=freq:mode
  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<3)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';

  con=mysql_init(NULL);
  if(con==NULL){printf("0,ND\n"); exit(0);}
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL){mysql_close(con); printf("0,ND\n"); exit(0);}
  epoch=time(NULL);
  sprintf(buf,"select radio from user where ota='%s' and lastota+durationota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); rrr=mysql_store_result(con); row=mysql_fetch_row(rrr);
  if(row==NULL){mysql_close(con); printf("0,ND\n"); exit(0);}
  strcpy(buf,row[0]);
  mysql_free_result(rrr);
  mysql_close(con);

  p1=strtok(buf,",");
  if(strcmp(p1,"TS890S")==0){
    ip=strtok(NULL,",");
    port=atol(strtok(NULL,","));
    user=strtok(NULL,",");
    pass=strtok(NULL,",");
    
    s=socket(AF_INET,SOCK_STREAM,0);
    memset(&a,0,sizeof(a));
    a.sin_family=AF_INET;
    a.sin_port=htons(port);
    inet_pton(AF_INET,ip,&a.sin_addr);
    
    signal(SIGALRM,alarm_handler);
    alarm(3);
    if(connect(s,(struct sockaddr*)&a,sizeof(a))<0){
      alarm(0);
      close(s);
      return 0;
    }
    alarm(0);
    tv.tv_sec=3; tv.tv_usec=0;
    setsockopt(s,SOL_SOCKET,SO_SNDTIMEO,&tv,sizeof(tv));
    setsockopt(s,SOL_SOCKET,SO_RCVTIMEO,&tv,sizeof(tv));

    sprintf(cmd,"##CN;");
    write(s,cmd,strlen(cmd));
    for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
    if(strcmp(b,"##CN1;")!=0){close(s); return 0;}

    sprintf(cmd,"##ID0%02d%02d%s%s;",strlen(user),strlen(pass),user,pass);
    write(s,cmd,strlen(cmd));
    for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
    if(strcmp(b,"##ID1;")!=0){close(s); return 0;}
 
    if(tok[1][0]=='R'){
      sprintf(cmd,"FA;");
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      *strchr(b,';')='\0';
      freq=atol(b+2);
      
      sprintf(cmd,"OM0;");
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      m=(b[3]>='0'&&b[3]<='9')?b[3]-'0':b[3]-'A'+10;

      printf("%ld,%s\n",freq,modets890s[m]);
    }
    else if(tok[1][0]=='S'){
      p1=strtok(tok[2],":");
      freq=atol(p1);
      // missed mode
      
      sprintf(cmd,"FA%011ld;",freq);
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      *strchr(b,';')='\0';

      fprintf(2,"%s\n",b);
      freq=atol(b+2);
      
      sprintf(cmd,"OM0;");
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      m=(b[3]>='0'&&b[3]<='9')?b[3]-'0':b[3]-'A'+10;

      printf("%ld,%s\n",freq,modets890s[m]);
    }
  }
  else if(strcmp(p1,"RIGCTLD")==0){
  }
  else printf("0,ND\n");

  /*
  
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
*/

  
}
