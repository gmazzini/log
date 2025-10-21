#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>

#define PORT 2333

int main(void) {
  int sockfd;
  struct sockaddr_in server_addr,client_addr;
  socklen_t addr_len=sizeof(client_addr);
  char buffer[1000];

  sockfd=socket(AF_INET,SOCK_DGRAM,0);
  if(sockfd<0)return;
  memset(&server_addr,0,sizeof(server_addr));
    server_addr.sin_family=AF_INET;
    server_addr.sin_addr.s_addr=INADDR_ANY;
    server_addr.sin_port=htons(PORT);

    if (bind(sockfd, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("bind");
        close(sockfd);
        exit(EXIT_FAILURE);
    }

    for (;;) {
        ssize_t len = recvfrom(sockfd, buffer, sizeof(buffer) - 1, 0,
                               (struct sockaddr *)&client_addr, &addr_len);
        if (len < 0) {
            perror("recvfrom");
            continue;
        }

        buffer[len] = '\0';
        printf("%s\n", buffer);
        fflush(stdout);
    }

    close(sockfd);
    return 0;
}
