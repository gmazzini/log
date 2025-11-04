// pradio.c radio function by GM @2025 V 2.0
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <signal.h>
#include <sys/wait.h>
#include <sys/socket.h>
#include <netdb.h>
#include <mysql/mysql.h>
#include "log.def"

static pid_t child_pid=-1;
static volatile sig_atomic_t timedout=0;

static void on_alarm(int sig){
  (void)sig;
  timedout=1;
  if(child_pid>0)kill(child_pid,SIGKILL);
}

int main(void){
  int fds[2],fd,r,i,printed,status,vv,gg,c;
  struct addrinfo *res;
  char h,out[100],*p,buf[256],tok[3][100],ip[20],port[10],mode[10],aux1[300];
  long freq;
  size_t len;
  time_t epoch;
  struct sigaction sa;
  ssize_t n;
  MYSQL *con;
  MYSQL_RES *rrr;
  MYSQL_ROW row;

  if(pipe(fds)==-1){
    printf("Content-Type: text/plain\r\n\r\n0,ND\n");
    return 0;
  }
  child_pid=fork();
  if(child_pid==0){
    close(fds[0]);
    dup2(fds[1],STDOUT_FILENO);
    close(fds[1]);
    // 0:ota 1:{R=read S=set} 2=freq,mode
    for(vv=0,gg=0;;){
      c=getchar();
      if(c==EOF)break;
      if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
      if(vv<3)tok[vv][gg++]=(char)c;
     }
    tok[vv][gg]='\0';
    con=mysql_init(NULL);
    if(con==NULL){write(1,"0,ND\n",5); _exit(0);}
    if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL){mysql_close(con); write(1,"0,ND\n",5); _exit(0);}
    epoch=time(NULL);
    sprintf(buf,"select rigctld_ip,rigctld_port from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
    mysql_query(con,buf); rrr=mysql_store_result(con); row=mysql_fetch_row(rrr);
    if(row==NULL){mysql_close(con); write(1,"0,ND\n",5); _exit(0);}
    strcpy(ip,row[0]); strcpy(port,row[1]);
    mysql_free_result(rrr);
    mysql_close(con);
    if(getaddrinfo(ip,port,&(struct addrinfo){.ai_socktype=SOCK_STREAM},&res)!=0){write(1,"0,ND\n",5); _exit(0);}
    fd=socket(res->ai_family,res->ai_socktype,res->ai_protocol);
    if(fd<0){write(1,"0,ND\n",5); freeaddrinfo(res); _exit(0);}
    r=connect(fd,res->ai_addr,res->ai_addrlen);
    if(r==-1){write(1,"0,ND\n",5); close(fd); freeaddrinfo(res); _exit(0);}
    if(tok[1][0]=='R'){
      send(fd,"sfim\n",5,0);
      p=out;
      for(i=0;i<5;){
        r=recv(fd,&h,1,0);
        if(r<=0)break;
        if(h=='\n'){if(i==2)*p++=','; i++;}
        else if(i==2||i==4)*p++=h;
      }
    }
    else if(tok[1][0]=='S'){
      sscanf(tok[2],"%ld:%s",&freq,mode);
      sprintf(aux1,"F %ld\n",freq);
      send(fd,aux1,strlen(aux1),0);
      sprintf(out,"%ld,%s",freq,mode);
      p=out+strlen(out);
    }
    else p=out;
    close(fd);
    freeaddrinfo(res);
    *p++='\0';
    if(out[0]!='\0'){
      len=strlen(out);
      if(len>0)write(1,out,len);
      write(1,"\n",1);
    } 
    else write(1,"0,ND\n",5);
    _exit(0);
  }
  close(fds[1]);
  printf("Content-Type: text/plain\r\n\r\n");
  memset(&sa,0,sizeof(sa));
  sa.sa_handler=on_alarm;
  sigemptyset(&sa.sa_mask);
  sigaction(SIGALRM,&sa,NULL);
  alarm(1);
  printed=0;
  while((n=read(fds[0],buf,sizeof(buf)))>0){
    fwrite(buf,1,(size_t)n,stdout);
    printed=1;
  }
  close(fds[0]);
  alarm(0);
  waitpid(child_pid,&status,0);
  if(timedout || !printed)printf("0,ND\n");
  return 0;
}
