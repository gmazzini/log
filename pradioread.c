#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <signal.h>
#include <sys/wait.h>
#include <sys/socket.h>
#include <netdb.h>

static pid_t child_pid = -1;
static volatile sig_atomic_t timedout = 0;

static void on_alarm(int sig){
  (void)sig;
  timedout = 1;
  if(child_pid > 0) kill(child_pid, SIGKILL);
}

int main(void) {
  int fds[2];
  if (pipe(fds) == -1) {
    printf("Content-Type: text/plain\r\n\r\n0,ND\n");
    return 0;
  }

  child_pid = fork();
  if(child_pid == 0){
    // --- FIGLIO: fa rete+parsing, scrive su stdout reindirizzato alla pipe ---
    close(fds[0]);
    dup2(fds[1], STDOUT_FILENO);
    close(fds[1]);

    int fd,r,i;
    struct addrinfo *res;
    char h,out[100],*p;

    if (getaddrinfo("188.209.85.92","6789",
        &(struct addrinfo){.ai_socktype=SOCK_STREAM}, &res) != 0) {
      write(1, "0,ND\n", 5);
      _exit(0);
    }

    fd=socket(res->ai_family,res->ai_socktype,res->ai_protocol);
    if (fd < 0) { write(1,"0,ND\n",5); freeaddrinfo(res); _exit(0); }

    r=connect(fd,res->ai_addr,res->ai_addrlen);
    if(r==-1){ write(1,"0,ND\n",5); close(fd); freeaddrinfo(res); _exit(0); }

    send(fd,"sfim\n",5,0);

    p=out;
    for(i=0;i<5;){
      r=recv(fd,&h,1,0);
      if(r<=0)break;
      if(h=='\n'){ if(i==2)*p++=','; i++; }
      else if(i==2||i==4) *p++=h;
    }
    close(fd);
    freeaddrinfo(res);

    *p++='\0';
    if (out[0] != '\0') {
      size_t len = strlen(out);
      if (len > 0) write(1, out, len);
      write(1, "\n", 1);
    } else {
      write(1, "0,ND\n", 5);
    }
    _exit(0);
  }

  // --- PADRE: header CGI, timeout 1s, legge dalla pipe ---
  close(fds[1]);
  printf("Content-Type: text/plain\r\n\r\n");

  struct sigaction sa;
  memset(&sa, 0, sizeof(sa));
  sa.sa_handler = on_alarm;
  sigemptyset(&sa.sa_mask);
  sigaction(SIGALRM, &sa, NULL);

  alarm(1); // timeout duro: 1 secondo

  char buf[256];
  ssize_t n;
  int printed = 0;

  while((n = read(fds[0], buf, sizeof(buf))) > 0){
    fwrite(buf, 1, (size_t)n, stdout);
    printed = 1;
  }
  close(fds[0]);

  alarm(0); // cancella l’allarme se non è scattato

  int status;
  waitpid(child_pid, &status, 0);

  if(timedout || !printed){
    printf("0,ND\n");
  }

  return 0;
}
