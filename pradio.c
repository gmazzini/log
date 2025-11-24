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
#include "/home/www/data/log.def"

static const char *modets890s[16] = {"Unused","LSB","USB","CW","FM","AM","FSK","CW-R","Unused","FSK-R","PSK","PSK-R","LSB-D","USB-D","FM-D","AM-D"};

int main(void){
  int i,vv,gg,s,m,loop;
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

    tv.tv_sec=2; tv.tv_usec=0;
    setsockopt(s,SOL_SOCKET,SO_SNDTIMEO,&tv,sizeof(tv));
    if(connect(s,(struct sockaddr*)&a,sizeof(a))<0){close(s); return 0;}
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
      p1=strtok(NULL,":");
      
      sprintf(cmd,"FA%011ld;",freq);
      write(s,cmd,strlen(cmd));
      sprintf(cmd,"FA;",freq);
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      *strchr(b,';')='\0';
      freq=atol(b+2);

      if(p1!=NULL){
        for(i=0;i<16;i++)if(strcmp(modets890s[i],p1)==0)break;
        if(i<16){
          sprintf(cmd,"OM0%c;",(i<10)?'0'+i:'A'+i-10);
          write(s,cmd,strlen(cmd));
        }
      }
      sprintf(cmd,"OM0;");
      write(s,cmd,strlen(cmd));
      for(i=0;i<100 && read(s,&c,1)==1;){b[i++]=c; if(c==';')break;} b[i]='\0';
      m=(b[3]>='0'&&b[3]<='9')?b[3]-'0':b[3]-'A'+10;

      printf("%ld,%s\n",freq,modets890s[m]);
      close(s);
    }
  }
  else if(strcmp(p1,"RIGCTRLD")==0){
    ip=strtok(NULL,",");
    port=atol(strtok(NULL,","));

    printf("%s %ld\n",ip,port);
    

    s=socket(AF_INET,SOCK_STREAM,0);
    memset(&a,0,sizeof(a));
    a.sin_family=AF_INET;
    a.sin_port=htons(port);
    inet_pton(AF_INET,ip,&a.sin_addr);

    tv.tv_sec=2; tv.tv_usec=0;
    setsockopt(s,SOL_SOCKET,SO_SNDTIMEO,&tv,sizeof(tv));
    if(connect(s,(struct sockaddr*)&a,sizeof(a))<0){close(s); return 0;}
    setsockopt(s,SOL_SOCKET,SO_SNDTIMEO,&tv,sizeof(tv));
    setsockopt(s,SOL_SOCKET,SO_RCVTIMEO,&tv,sizeof(tv));

    if(tok[1][0]=='R'){
      printf(cmd,"sfim\n");
      write(s,cmd,strlen(cmd));
      for(loop=i=0;i<100 && loop<20;)if(recv(s,&c,1,MSG_DONTWAIT)==1){b[i++]=c; loop=0;} else {usleep(10000); loop++;} b[i]='\0';
      p1=strtok(b,"\n"); p1=strtok(b,"\n"); p1=strtok(b,"\n");
      printf("%ld,",atol(p1));
      p1=strtok(b,"\n"); p1=strtok(b,"\n");
      printf("%s\n",p1);
    }
    close(s);    
  }
  else printf("0,ND\n");

  /*
  

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
