#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <curl/curl.h>
#include <mysql/mysql.h>
#include <unistd.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <openssl/ssl.h>

#include "/home/www/data/log.def"
#define BUFOUT 20000000L

char **wccall;
long wcn;
int newout=1;

static size_t write_cb(void *ptr,size_t size,size_t nmemb,void *userdata){
  size_t realsize=size*nmemb;
  char **buffer=(char **)userdata;
  static char *out=NULL;
  static size_t actpos=0;
  if(out==NULL){
    out=(char *)malloc(BUFOUT*sizeof(char));
    if(out==NULL)return 0;
  }
  if(newout){actpos=0; newout=0;}
  memcpy(out+actpos,ptr,realsize);
  actpos+=realsize;
  *(out+actpos)='\0'; 
  *buffer=out;
  return realsize;
}

static size_t xxxwrite_cb(void *ptr,size_t size,size_t nmemb,void *userdata){
  size_t realsize=size*nmemb;
  char **buffer=(char **)userdata;
  char *newbuf;
  newbuf=realloc(*buffer,(*buffer?strlen(*buffer):0)+realsize+1);
  if(!newbuf)return 0;
  if(!*buffer)newbuf[0]='\0';
  strncat(newbuf,ptr,realsize);
  *buffer=newbuf;
  return realsize;
}

char *mypost(char *url,char *cookie,char *post){
  CURL *ch;
  CURLcode res;
  char *out;
  char agent[256];

  newout=1;
  sprintf(agent,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36");
  ch=curl_easy_init();
  if(!ch)return NULL;
  curl_easy_setopt(ch,CURLOPT_URL,url);
  curl_easy_setopt(ch,CURLOPT_SSL_VERIFYPEER,0L);
  curl_easy_setopt(ch,CURLOPT_FOLLOWLOCATION,1L);
  curl_easy_setopt(ch,CURLOPT_USERAGENT,agent);
  if(cookie!=NULL)curl_easy_setopt(ch,CURLOPT_COOKIE,cookie);
  if(post!=NULL){
    curl_easy_setopt(ch,CURLOPT_POST,1L);
    curl_easy_setopt(ch,CURLOPT_POSTFIELDS,post);
  }
  curl_easy_setopt(ch,CURLOPT_WRITEFUNCTION,write_cb);
  curl_easy_setopt(ch,CURLOPT_WRITEDATA,&out);
  res=curl_easy_perform(ch);
  curl_easy_cleanup(ch);
  if(res!=CURLE_OK)return NULL;
  return out;
}

int readqrz(char *call,long *visit,int *webcon){
  char *out,tok[100],*p1,*p2,*p3,tmpc,url[200];

  *visit=0; *webcon=0; wcn=0;
  sprintf(url,"https://www.qrz.com/lookup/%s",call);
  out=mypost(url,NULL,NULL);
  if(out==NULL)return 0;
 
  // number of visit
  strcpy(tok,"<span class=\"ml1\">Lookups: ");
  p1=strstr(out,tok);
  if(p1==NULL)return 0;
  p1+=strlen(tok);
  p2=strstr(p1,"</span>");
  if(p2==NULL)return 0;
  tmpc=*p2; *p2='\0'; *visit=atol(p1); *p2=tmpc;
  
  // url
  strcpy(tok,"var wc_summary = \"");
  p1=strstr(out,tok);
  if(p1==NULL)return 0;
  p1+=strlen(tok);
  p2=strstr(p1,"\"");
  if(p2==NULL)return 0;
  strncpy(url,p1,p2-p1); url[p2-p1]='\0';
  
  // webcon
  strcpy(tok,"<a href=\"#t_webcon\">Web <span class=\"f8\">");
  p1=strstr(out,tok);
  if(p1==NULL)*webcon=0;
  else {
    p1+=strlen(tok);
    p2=strstr(p1,"</span></a>");
    if(p2==NULL)*webcon=0;
    else {
      tmpc=*p2; *p2='\0'; *webcon=(atol(p1)>0)?1:0; *p2=tmpc;
    }
  } 
  
  // visit webcon page
  out=mypost(url,NULL,NULL);
  if(out==NULL)return 0;
  strcpy(tok,"href=\"https://www.qrz.com/db/");
  for(p1=out;;){
    p1=strstr(p1,tok);
    if(p1==NULL)break;
    p1+=strlen(tok);
    p2=strstr(p1,"/");
    if(p2==NULL)break;
    for(p3=p1;p3<p2;p3++)if((*p3>='A'&&*p3<='Z')||(*p3>='0'&&*p3<='9'))wccall[wcn][p3-p1]=*p3; else break;
    if(p3-p1>=3&&p3==p2){wccall[wcn][p3-p1]='\0'; wcn++;}
  }
  return 1;
}

char *toend(char *s){
  int escaped=0;
  for(;*s;s++){
    if(escaped){escaped=0; continue;}
    if(*s=='\\'){escaped=1; continue;}
    if(*s=='"')return s;
  }
  return NULL;
}

int setqrz(char *call){
  char *out,tok[100],*p1,*p2,*p3,url[200],userid[200],post[200],buf[10000],cookie[10000],*pc;
  FILE *fp;
  int i;

  // url
  sprintf(url,"https://www.qrz.com/lookup/%s",call);
  out=mypost(url,NULL,NULL);
  if(out==NULL)return 0;
  strcpy(tok,"var wc_summary = \"");
  p1=strstr(out,tok);
  if(p1==NULL)return 0;
  p1+=strlen(tok);
  p2=strstr(p1,"\"");
  if(p2==NULL)return 0;
  strncpy(url,p1,p2-p1); url[p2-p1]='\0';
  if(strlen(url)<5)return 0;
  printf("URL: %s\n",url);

  // read cookie
  fp=fopen("/home/www/data/qrz_cookie","r");
  if(fp==NULL)return 0;
  pc=cookie;
  for(i=0;fgets(buf,10000,fp);){
    if(i==0){
      strcpy(tok,"\"name\": \"");
      p1=strstr(buf,tok);
      if(p1!=NULL){
        p1+=strlen(tok);
        p2=toend(p1);
        for(p3=p1;p3<p2;p3++)if(*p3!='\\' || *(p3+1)!='"')*pc++=*p3; else {*pc++='"'; p3++;} 
        i=1;
      }
    }
    else {
      strcpy(tok,"\"value\": \"");
      p1=strstr(buf,tok);
      if(p1!=NULL){
        *pc++='=';
        p1+=strlen(tok);
        p2=toend(p1);
        for(p3=p1;p3<p2;p3++)if(*p3!='\\' || *(p3+1)!='"')*pc++=*p3; else {*pc++='"'; p3++;} 
        i=0;
        *pc++=';';
        *pc++=' ';
      }
    }
  }
  fclose(fp);  

  // look for userid
  out=mypost(url,cookie,NULL);
  if(out==NULL)return 0;
  strcpy(tok,"name=\"wc_userid\" value=\"");
  p1=strstr(out,tok);
  if(p1==NULL)return 0;
  p1+=strlen(tok);
  p2=strstr(p1,"\"");
  if(p2==NULL)return 0;
  strncpy(userid,p1,p2-p1); userid[p2-p1]='\0';
  if(atol(userid)==0)return 0;
  printf("userid: %s\n",userid);

  // set the wc
  sprintf(url,"https://www.qrz.com/db/%s",call);
  sprintf(post,"webcon=1&wc_userid=%s",userid);
  mypost(url,cookie,post);

  return 1;
}

int myemailsend(char *from,char *to,char *subject,char *body){
  int sock;
  struct hostent *he;
  struct sockaddr_in addr;
  SSL_CTX *ctx;
  SSL *ssl;
  char buf[8192];
  int n;

  he=gethostbyname(smtp_host);
  if(!he)return 0;
  sock=socket(AF_INET,SOCK_STREAM,0);
  if(sock<0)return 0;
  memset(&addr,0,sizeof(addr));
  addr.sin_family=AF_INET;
  addr.sin_port=htons(smtp_port);
  memcpy(&addr.sin_addr,he->h_addr,he->h_length);
  if(connect(sock,(struct sockaddr *)&addr,sizeof(addr))<0){close(sock); return 0;}
  n=recv(sock,buf,8191,0);
  if(n<=0){close(sock); return 0;}
  buf[n]='\0';

  // EHLO + STARTTLS in chiaro
  send(sock,"EHLO localhost\r\n",16,0);
  n=recv(sock,buf,8191,0);
  if(n<=0){close(sock); return 0;}
  buf[n]='\0';
  send(sock,"STARTTLS\r\n",10,0);
  n=recv(sock,buf,8191,0);
  if(n<=0){close(sock); return 0;}
  buf[n]='\0';

  // TLS
  SSL_library_init();
  ctx=SSL_CTX_new(TLS_client_method());
  if(!ctx){close(sock); return 0;}
  ssl=SSL_new(ctx);
  SSL_set_fd(ssl,sock);
  if(SSL_connect(ssl)<=0){SSL_free(ssl); SSL_CTX_free(ctx); close(sock); return 0;}

  // EHLO dopo TLS
  SSL_write(ssl,"EHLO localhost\r\n",16);
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  buf[n]='\0';

  // AUTH LOGIN
  SSL_write(ssl,"AUTH LOGIN\r\n",12);
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  buf[n]='\0';
  sprintf(buf,"%s\r\n",mail_user_b64);
  SSL_write(ssl,buf,strlen(buf));
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  buf[n]='\0';
  sprintf(buf,"%s\r\n",mail_pass_b64);
  SSL_write(ssl,buf,strlen(buf));
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  buf[n]='\0';

  // MAIL FROM / RCPT TO / DATA
  sprintf(buf,"MAIL FROM:<%s>\r\n",from);
  SSL_write(ssl,buf,strlen(buf));
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  sprintf(buf,"RCPT TO:<%s>\r\n",to);
  SSL_write(ssl,buf,strlen(buf));
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;
  SSL_write(ssl,"DATA\r\n",6);
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;

  // HEADERS + BODY + terminatore
  sprintf(buf,"Subject: %s\r\nFrom: %s\r\nTo: %s\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n%s\r\n.\r\n",subject,from,to,body);
  SSL_write(ssl,buf,strlen(buf));
  n=SSL_read(ssl,buf,8191);
  if(n<=0)goto end;

  // QUIT
  SSL_write(ssl,"QUIT\r\n",6);
  SSL_read(ssl,buf,8191);

  end:
  SSL_shutdown(ssl);
  SSL_free(ssl);
  SSL_CTX_free(ctx);
  close(sock);
}
