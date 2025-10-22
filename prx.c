#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <mysql/mysql.h>
#include "log.def"
#define PORT 2333

char adif[20][200],adif1[20][20];
int adifextract(char *,int);

int main(void) {
  int sockfd,vv,gg,opt;
  struct sockaddr_in server_addr,client_addr;
  socklen_t addr_len=sizeof(client_addr);
  ssize_t len;
  char buffer[1000],buf[1000],aux1[300],aux2[300],aux3[300];
  MYSQL *con;
  FILE *fp;

  fp=fopen("/home/www/log/prx.log","a");
  if(fp==NULL)exit(-1);
  strcpy(adif1[0],"call"); strcpy(adif1[1],"freq"); strcpy(adif1[2],"freq_rx"); strcpy(adif1[3],"rst_sent"); strcpy(adif1[4],"rst_rcvd"); strcpy(adif1[5],"mode");
  strcpy(adif1[6],"time_on"); strcpy(adif1[7],"time_off"); strcpy(adif1[8],"stx_string"); strcpy(adif1[9],"stx"); strcpy(adif1[10],"srx_string"); strcpy(adif1[11],"srx");
  strcpy(adif1[12],"contest_id"); strcpy(adif1[13],"qso_date"); strcpy(adif1[14],"qso_date_off"); strcpy(adif1[15],"comment"); strcpy(adif1[16],"station_callsign");
  sockfd=socket(AF_INET,SOCK_DGRAM,0);
  if(sockfd<0)exit(-1);
  opt=1;
  if(setsockopt(sockfd,SOL_SOCKET,SO_REUSEPORT,&opt,sizeof(opt))<0)exit(-1);
  memset(&server_addr,0,sizeof(server_addr));
  server_addr.sin_family=AF_INET;
  server_addr.sin_addr.s_addr=INADDR_ANY;
  server_addr.sin_port=htons(PORT);
  if(bind(sockfd,(struct sockaddr *)&server_addr,sizeof(server_addr))<0)exit(-1);
  fprintf(fp,"Start %ld\n",time(NULL));
  for(;;){
    len=recvfrom(sockfd,buffer,sizeof(buffer)-1,0,(struct sockaddr *)&client_addr,&addr_len);
    if(len<0)continue;
    buffer[len]='\0';
    vv=17; gg=adifextract(buffer,vv);
    if(strcmp(adif[15],secret_rx)!=0)continue;
    if(adif[6][4]=='\0'){adif[6][4]='0'; adif[6][5]='0'; adif[6][6]='\0';}
    sprintf(aux1,"%.4s-%.2s-%.2s %.2s:%.2s:%.2s",adif[13],adif[13]+4,adif[13]+6,adif[6],adif[6]+2,adif[6]+4);
    if(adif[14][0]=='\0')strcpy(adif[14],adif[13]);
    if(adif[7][0]=='\0')strcpy(adif[7],adif[6]);
    if(adif[7][4]=='\0'){adif[7][4]='0'; adif[7][5]='0'; adif[7][6]='\0';}
    sprintf(aux2,"%.4s-%.2s-%.2s %.2s:%.2s:%.2s",adif[14],adif[14]+4,adif[14]+6,adif[7],adif[7]+2,adif[7]+4);
    sprintf(aux3,"('%s','%s','%s','%s','%s',%ld,%ld,'%s','%s','%s','%s','%s')",adif[16],adif[0],aux1,aux2,adif[5],(long)(atof(adif[1])*1000000.0),(long)(atof(adif[2])*1000000.0),adif[3],adif[4],(adif[8][0]=='\0')?adif[9]:adif[8],(adif[10][0]=='\0')?adif[11]:adif[10],adif[12]);
    fprintf(fp,"%s\n",aux3);
    sprintf(buf,"insert ignore into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value %s",aux3);
    con=mysql_init(NULL);
    if(con==NULL)continue;
    if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)continue;
    mysql_query(con,buf);
    mysql_close(con);
  }
}

int adifextract(char *input,int ntok){  
  char *p1,*p2,*p3;
  int i,nret=0,len;
  static char *p0;
  if(input!=NULL)p0=input;
  for(i=0;i<ntok;i++)adif[i][0]='\0';
  for(;;){
    p1=strchr(p0,'<');
    if(p1==NULL)return nret;
    p2=strchr(p1+1,'>');
    if(p2==NULL)return nret;
    p0=p2+1;
    if(strncasecmp("EOR",p1+1,3)==0)return nret;
    p3=memchr(p1+1,':',p2-p1-1);    
    if(p3==NULL)continue;
    len=atoi(p3+1);
    p0=p2+1+len;
    for(i=0;i<ntok;i++)if(strncasecmp(adif1[i],p1+1,p3-p1-1)==0)break;
    if(i==ntok)continue;
    strncpy(adif[i],p2+1,len);
    adif[i][len]='\0';
    nret++;
  }
  return nret;
}
