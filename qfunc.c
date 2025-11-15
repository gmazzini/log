#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <curl/curl.h>

static size_t write_cb(void *ptr,size_t size,size_t nmemb,void *userdata) {
  size_t realsize=size * nmemb;
  char **buffer=(char **)userdata;
  char *newbuf;
  newbuf=realloc(*buffer, (*buffer ? strlen(*buffer) : 0) + realsize + 1);
    if (!newbuf) return 0;

    if (!*buffer) newbuf[0] = '\0';
    strncat(newbuf, ptr, realsize);

    *buffer = newbuf;
    return realsize;
}

char *myget(char *call){
  CURL *ch;
  CURLcode res;
  char *out;
  char agent[256];
  char url[256];

  out=NULL;
  sprintf(agent,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36");
  sprintf(url,"https://www.qrz.com/lookup/%s",call);
  ch=curl_easy_init();
  if(!ch)return NULL;
  curl_easy_setopt(ch,CURLOPT_URL,url);
  curl_easy_setopt(ch,CURLOPT_SSL_VERIFYPEER,0L);
  curl_easy_setopt(ch,CURLOPT_FOLLOWLOCATION,1L);
  curl_easy_setopt(ch,CURLOPT_USERAGENT,agent);
  curl_easy_setopt(ch,CURLOPT_WRITEFUNCTION,write_cb);
  curl_easy_setopt(ch,CURLOPT_WRITEDATA,&out);
  res=curl_easy_perform(ch);
  curl_easy_cleanup(ch);
  if(res!=CURLE_OK){free(out); return NULL;}
  return out;
}

int readqrz(char *call,long *visit){
  char *out,tok[100],*p1,*p2,aux[100],tmpc,myurl;
  
  out=myget(call);
  if(out==NULL)return 0;
  // number of visit
  strcpy(tok,"<span class=\"ml1\">Lookups: ");
  p1=strstr(out,tok);
  if(p1==NULL){free(out); return 0;}
  p1+=strlen(tok);
  p2=strstr(p1,"</span>");
  if(p2==NULL){free(out); return 0;}
  tmpc=*p2; *p2='\0'; *visit=atol(p1); *p2=tmpc;
  strncpy(aux,p1,p2-p1);
  *visit=atol(aux);
  // url
  strcpy(tok,"var wc_summary = \"");
  p1=strstr(out,tok);
  if(p1==NULL){free(out); return 0;}
  p2=strstr(p1,"\"");
  if(p2==NULL){free(out); return 0;}
  strncpy(myurl,p1,p2-p1); myurl[p2-p1]='\0';

  printf("%s\n",myurl);
  
  
  


  
  free(out);
}
