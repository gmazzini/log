#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/socket.h>
#include <netdb.h>

int main(void) {
  int fd,r,i;
  struct addrinfo *res;
  char h,out[100],*p;
  printf("Content-Type: text/plain\r\n\r\n");
  getaddrinfo("188.209.85.92","6789",&(struct addrinfo){.ai_socktype=SOCK_STREAM},&res);
  fd=socket(res->ai_family,res->ai_socktype,res->ai_protocol);
  r=connect(fd,res->ai_addr,res->ai_addrlen);
  if(r==-1){printf("0,ND\n"); close(fd); return 0;}
  send(fd,"sfim\n",5,0);
  p=out;
  for(i=0;i<5;){
    r=recv(fd,&h,1,0);
    if(r<=0)break;
    if(h=='\n'){if(i==2)*p++=','; i++;}
    else if(i==2||i==4)*p++=h;
  }
  close(fd);
  *p++='\0';
  printf("%s\n",out);
}
