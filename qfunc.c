#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <curl/curl.h>
#include <mysql/mysql.h>
#include <unistd.h>
#include "/home/www/data/log.def"
#define BUFOUT 20000000L

char **wccall;
long wcn;

static size_t write_cb(void *ptr,size_t size,size_t nmemb,void *userdata){
  size_t realsize=size*nmemb;
  char **buffer=(char **)userdata;
  static char *out=NULL;
  if(out==NULL){
    out=(char *)malloc(BUFOUT*sizeof(char));
    if(out==NULL)printf("drmma\n"); else printf("Ok\n");
    if(out==NULL)return 0;
  }
  if(!*buffer)out[0]='\0';
  strncat(out,ptr,realsize);
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

char *myget(char *url,char *cookie){
  CURL *ch;
  CURLcode res;
  char *out;
  char agent[256];

  out=NULL;
  sprintf(agent,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36");
  ch=curl_easy_init();
  if(!ch)return NULL;
  curl_easy_setopt(ch,CURLOPT_URL,url);
  curl_easy_setopt(ch,CURLOPT_SSL_VERIFYPEER,0L);
  curl_easy_setopt(ch,CURLOPT_FOLLOWLOCATION,1L);
  curl_easy_setopt(ch,CURLOPT_USERAGENT,agent);
  if(cookie!=NULL)curl_easy_setopt(ch,CURLOPT_COOKIE,cookie);
  curl_easy_setopt(ch,CURLOPT_WRITEFUNCTION,write_cb);
  curl_easy_setopt(ch,CURLOPT_WRITEDATA,&out);
  res=curl_easy_perform(ch);
  curl_easy_cleanup(ch);
  // if(res!=CURLE_OK){free(out); return NULL;}
  if(res!=CURLE_OK)return NULL;
  return out;
}

int readqrz(char *call,long *visit,int *webcon){
  char *out,tok[100],*p1,*p2,*p3,tmpc,url[200];

  *visit=0; *webcon=0; wcn=0;
  sprintf(url,"https://www.qrz.com/lookup/%s",call);
  out=myget(url,NULL);
  if(out==NULL)return 0;
 
  // number of visit
  strcpy(tok,"<span class=\"ml1\">Lookups: ");
  p1=strstr(out,tok);
  if(p1==NULL){free(out); return 0;}
  p1+=strlen(tok);
  p2=strstr(p1,"</span>");
  if(p2==NULL){free(out); return 0;}
  tmpc=*p2; *p2='\0'; *visit=atol(p1); *p2=tmpc;
  
  // url
  strcpy(tok,"var wc_summary = \"");
  p1=strstr(out,tok);
  if(p1==NULL){free(out); return 0;}
  p1+=strlen(tok);
  p2=strstr(p1,"\"");
  if(p2==NULL){free(out); return 0;}
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
  free(out);
  
  // visit webcon page
  out=myget(url,NULL);
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
  free(out);
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
  char *out,tok[100],*p1,*p2,*p3,url[200],buf[10000],cookie[10000],*pc;
  FILE *fp;
  int i;
  
  sprintf(url,"https://www.qrz.com/lookup/%s",call);
  out=myget(url,NULL);
  if(out==NULL)return 0;
printf("%s\n",out);
  return 1;



  
  // url
  strcpy(tok,"var wc_summary = \"");
  p1=strstr(out,tok);
  if(p1==NULL){free(out); return 0;}
  p1+=strlen(tok);
  p2=strstr(p1,"\"");
  if(p2==NULL){free(out); return 0;}
  strncpy(url,p1,p2-p1); url[p2-p1]='\0';
  if(strlen(url)<5){free(out); return 0;}

  // create cookie
  fp=fopen("/home/www/data/qrz_cookie","r");
  if(fp==NULL){free(out); return 0;}
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
//  free(out);
return 1;
  // read userid
  
  printf("%s\n",url);
  printf("%s\n",cookie);

  out=myget(url,cookie);
  
//  free(out);
  
  return 1;
}
