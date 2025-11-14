#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netdb.h>
#include <time.h>
#define SERVER "pool.ntp.org"

int main(){
  int sockfd, n;
  unsigned char packet[48];
  struct sockaddr_in server_addr;
  struct hostent *server;
  socklen_t addr_len;
  uint32_t secs;
  time_t epoch;
  unsigned int i;

  printf("Content-Type: text/plain\r\n\r\n");
  sockfd=socket(AF_INET,SOCK_DGRAM,IPPROTO_UDP);
  if(sockfd<0)return 1;
  server=gethostbyname(SERVER);
  if(server==NULL)return 1;
  memset(&server_addr,0,sizeof(server_addr));
  server_addr.sin_family=AF_INET;
  server_addr.sin_port=htons(123);
  memcpy(&server_addr.sin_addr.s_addr,server->h_addr,server->h_length);
  memset(packet,0,sizeof(packet));
  packet[0]=0x1B;
  n=sendto(sockfd,packet,sizeof(packet),0,(struct sockaddr *)&server_addr,sizeof(server_addr));
  if(n<0){close(sockfd); return 1;}
  addr_len=sizeof(server_addr);
  n=recvfrom(sockfd,packet,sizeof(packet),0,(struct sockaddr *)&server_addr,&addr_len);
  if(n<0){close(sockfd); return 1;}
  memcpy(&secs,packet+40,sizeof(secs));
  secs=ntohl(secs);
  epoch=(time_t)(secs-2208988800U);
  printf("%ld\n",(long)epoch);
  close(sockfd);
  return 0;
}
