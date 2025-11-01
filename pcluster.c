#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <time.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netdb.h>
#include <pthread.h>

#define DXC_ADDR        "ham.homelinux.org"
#define DXC_PORT        "8000"
#define CALLSIGN        "IK4LZH"
#define TIMEOUT_SEC     300
#define RECONNECT_WAIT  5
#define ELM 		200000
#define WHOIS_ADDR      "127.0.0.1"
#define WHOIS_PORT      "8043"

struct data {
  char dx[20];
  char from[20];
  long freq;
  time_t time;
} *data;
long ndata=0;
pthread_mutex_t data_mtx=PTHREAD_MUTEX_INITIALIZER;

static void *whois_thread(void *arg) {
  (void)arg;
  int ls,cs,one,r;
  struct addrinfo hints,*res=NULL;
  char buf[100],out[100],aux1[300];
  long i,show,idx;
  struct tm te,*tm_now;

  memset(&hints,0,sizeof(hints));
  hints.ai_family=AF_UNSPEC;
  hints.ai_socktype=SOCK_STREAM;
  hints.ai_flags=AI_PASSIVE;
  if(getaddrinfo(WHOIS_ADDR,WHOIS_PORT,&hints,&res)!=0)return NULL;
  ls=socket(res->ai_family,res->ai_socktype,res->ai_protocol);
  if(ls<0){freeaddrinfo(res); return NULL;}
  one=1;
  setsockopt(ls,SOL_SOCKET,SO_REUSEADDR,&one,sizeof(one));
  if(bind(ls,res->ai_addr,res->ai_addrlen)!=0){freeaddrinfo(res); close(ls); return NULL;}
  freeaddrinfo(res);
  if(listen(ls,16)!=0){close(ls); return NULL;}
  for(;;){
    cs=accept(ls,NULL,NULL);
    if(cs<0)continue;
    r=recv(cs,buf,99,0);
    if(r<0){close(cs); continue;}
    buf[r]='\0';
    show=atol(buf);
    pthread_mutex_lock(&data_mtx);
    for(i=0;i<show;i++){
      idx=(i-1-n+ELM)%ELM;
      if(data[idx].freq>0){
        tm_now=gmtime(&data[idx].time); te=*tm_now; timegm(&te);
        strftime(aux1,sizeof(aux1),"%Y-%m-%d %H:%M:%S",&te);
        sprintf(out,"%s,%s,%ld,%s\n",aux1,data[idx].from,data[idx].freq,data[iidx].dx);
        send(cs,out,strlen(out),0);
      }
    }
    pthread_mutex_unlock(&data_mtx);
    close(cs);
  }
}

int main(void){
  struct addrinfo hints,*res,*rp;
  int sock,rc,one=1; 
  char buf[4096],aux1[300],*p,*q1,*q2,*q3;
  struct timeval to; 
  pthread_t th;
  long i;

  data=(struct data *)malloc(ELM*sizeof(struct data));
  if(data==NULL)return 0;
  for(i=0;i<ELM;i++)data[i].freq=0;
  pthread_create(&th,NULL,whois_thread,NULL);
  pthread_detach(th);

reconnect:
  memset(&hints,0,sizeof(hints));
  hints.ai_family=AF_UNSPEC;
  hints.ai_socktype=SOCK_STREAM;
  if(getaddrinfo(DXC_ADDR,DXC_PORT,&hints,&res)!=0){sleep(RECONNECT_WAIT); goto reconnect;}
  sock=-1;
  for(rp=res;rp;rp=rp->ai_next){
    sock=socket(rp->ai_family,rp->ai_socktype,rp->ai_protocol);
    if(sock<0)continue;
    if(connect(sock,rp->ai_addr,rp->ai_addrlen)==0)break;
    close(sock);
    sock=-1; 
  }
  freeaddrinfo(res);
  if(sock<0){sleep(RECONNECT_WAIT); goto reconnect;}
  to.tv_sec=TIMEOUT_SEC; to.tv_usec=0;
  setsockopt(sock,SOL_SOCKET,SO_RCVTIMEO,&to,sizeof(to));
  setsockopt(sock,SOL_SOCKET,SO_KEEPALIVE,&one,sizeof(one));

  for(;;){
    rc=recv(sock,buf,sizeof(buf)-1,0);
    if(rc<=0){close(sock); sleep(RECONNECT_WAIT); goto reconnect;}
    buf[rc]='\0';
    if(strstr(buf,"login:")){
      sprintf(aux1,"%s\n",CALLSIGN);
      send(sock,aux1,strlen(aux1),0);
      continue;
    }
    p=strstr(buf,"DX de ");
    if(p==NULL)continue;
    q1=strtok(p+6," ");
    q1[strlen(q1)-1]='\0';
    q2=strtok(NULL," ");
    q3=strtok(NULL," ");
    pthread_mutex_lock(&data_mtx);
    strcpy(data[ndata].dx,q3);
    strcpy(data[ndata].from,q1);
    data[ndata].freq=atof(q2)*1000;
    data[ndata].time=time(NULL);
    ndata=(ndata+1)%ELM;
    pthread_mutex_unlock(&data_mtx);

printf("%s %s %s\n",q1,q2,q3);
  }
}
