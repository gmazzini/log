#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <string.h>
#include <time.h>
#include <stdint.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netdb.h>
#include <mysql/mysql.h>
#include <curl/curl.h>
#include <arpa/inet.h>
#include "/home/www/data/log.def"

#define TOT3 5
#define TOTL2 400
#define TOTL3 200000
#define MAXFF 20000000L
#define QSLWIN 240

struct data3 {char lab[20]; long num; long idx;} ***data3;
long **ndata3;
size_t wrused;
char adif[20][200],adif1[20][20],wrbuf[10000];
int myband[434]={[0]=0,[1]=1600,[3]=800,[5]=600,[7]=400,[10]=300,[14]=200,[18]=170,[21]=150,[24]=120,[28]=100,[29]=100,[50]=60,[144]=20,[145]=20,[430]=7,[431]=7,[432]=7,[433]=7};
static const uint8_t B64DEC[256] = {
  [0 ... 255] = 0,
  ['A']=0,  ['B']=1,  ['C']=2,  ['D']=3,  ['E']=4,  ['F']=5,  ['G']=6,  ['H']=7,
  ['I']=8,  ['J']=9,  ['K']=10, ['L']=11, ['M']=12, ['N']=13, ['O']=14, ['P']=15,
  ['Q']=16, ['R']=17, ['S']=18, ['T']=19, ['U']=20, ['V']=21, ['W']=22, ['X']=23,
  ['Y']=24, ['Z']=25,
  ['a']=26, ['b']=27, ['c']=28, ['d']=29, ['e']=30, ['f']=31, ['g']=32, ['h']=33,
  ['i']=34, ['j']=35, ['k']=36, ['l']=37, ['m']=38, ['n']=39, ['o']=40, ['p']=41,
  ['q']=42, ['r']=43, ['s']=44, ['t']=45, ['u']=46, ['v']=47, ['w']=48, ['x']=49,
  ['y']=50, ['z']=51,
  ['0']=52, ['1']=53, ['2']=54, ['3']=55, ['4']=56, ['5']=57, ['6']=58, ['7']=59,
  ['8']=60, ['9']=61,
  ['+']=62, ['/']=63,
  ['-']=62, ['_']=63
};

MYSQL_ROW searchcty(MYSQL *con,char *incall){
  char buf[1000],*p,call[20];
  static MYSQL_RES *res;
  static MYSQL_ROW row;
  int i,n;
  const char *suffixes[]={"P","M","LH","MM","AM","A","B","QRP","0","1","2","3","4","5","6","7","8","9"};
  n=sizeof(suffixes)/sizeof(suffixes[0]);
  strcpy(call,incall);
  p=strrchr(call,'/');
  if(p){
    for(i=0;i<n;i++)if(strcmp(p+1,suffixes[i])==0)break;
    if(i<n)*p='\0';
  }
  p=strrchr(call,'/');
  if(p){
    n=strlen(call);
    if((p-call)<(n-(p-call)-1))*p='\0';
    else strcpy(call,p+1);
  }
  n=strlen(call);
  for(i=n;i>0;i--){
    call[i]='\0';
    sprintf(buf,"select base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift from cty where prefix='%s' limit 1",call);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    row=mysql_fetch_row(res);
    if(row!=NULL)break;
    mysql_free_result(res);
  }
  return row;
}

long incdata3(int cha,int idx,char *key,long ss,long dd){
  long n,lo,hi,mid,cmp,j;
  n=ndata3[cha][idx];
  lo=0;
  hi=n-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data3[cha][idx][mid].lab,key);
    if(cmp==0){
      data3[cha][idx][mid].num+=dd;
      return data3[cha][idx][mid].idx;
    }
    else if(cmp<0)lo=mid+1;
    else hi=mid-1;
  }
  if(n<TOTL3){
    for(j=n;j>lo;--j)data3[cha][idx][j]=data3[cha][idx][j-1];
    strcpy(data3[cha][idx][lo].lab,key);
    data3[cha][idx][lo].idx=n;
    data3[cha][idx][lo].num=ss;
    ndata3[cha][idx]=n+1;
  }
  return n;
}

long numdata3(int cha,int idx,char *key){
  int lo,hi,mid,cmp;
  lo=0;
  hi=ndata3[cha][idx]-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data3[cha][idx][mid].lab,key);
    if(cmp==0)return data3[cha][idx][mid].num;
    else if(cmp<0)lo=mid+1;
    else hi=mid-1;
  }
  return 0;
}

// MANCA analisi degli slash
char * wpx(char *s){
  int i;
  static char out[20];
  strcpy(out,s);
  for(i=strlen(out)-1;i>=0;i--)if(out[i]>'0' && out[i]<='9')break;
  out[i+1]='\0';
  return out;
}

long min(long a,long b){
  return (a<b)?a:b;
}

int cmp3(const void *a,const void *b){
  const struct data3 *x=a;
  const struct data3 *y=b;
  return y->num-x->num;
}

char *mymode(char *s){
  if(!s)return"ND";
  if(!strcmp(s,"CW"))return"CW";
  if(!strcmp(s,"FT8")||!strcmp(s,"RTTY")||!strcmp(s,"MFSK")||!strcmp(s,"FT4")||!strcmp(s,"PKT")||!strcmp(s,"TOR")||!strcmp(s,"AMTOR")||!strcmp(s,"PSK"))return"DG";
  if(!strcmp(s,"SSB")||!strcmp(s,"USB")||!strcmp(s,"LSB")||!strcmp(s,"FM")||!strcmp(s,"AM"))return"PH";
  return"ND";
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

char *search(char *buf,char *key){
  char aux1[100],*p,*q;
  static char out[100];
  out[0]='\0';
  sprintf(aux1,"<%s>",key);
  p=strstr(buf,aux1);
  if(p==NULL)return out;
  p+=strlen(aux1);
  sprintf(aux1,"</%s>",key);
  q=strstr(buf,aux1);
  if(q==NULL || q<p)return out;
  strncpy(out,p,q-p);
  out[q-p]='\0';
  return out;
}

int qrzcom(MYSQL *con,char *call){
  struct addrinfo h={0},*r=0;
  int s,n;
  char aux1[300],aux2[300],key[13][201],ee[40];
  time_t now;
  struct tm *utc;
  const char *qrzkey[13]={"fname","name","addr1","addr2","state","zip","country","grid","email","cqzone","ituzone","born","image"};
  
  h.ai_socktype=SOCK_STREAM;
  getaddrinfo("xmldata.qrz.com","80",&h,&r);
  s=socket(r->ai_family,r->ai_socktype,r->ai_protocol);
  connect(s,r->ai_addr,r->ai_addrlen);
  sprintf(aux2,"GET /xml/current/?username=%s;password=%s;agent=GM02 HTTP/1.0\r\nHost: xmldata.qrz.com\r\nConnection: close\r\n\r\n",qrzuser,qrzpassword);
  send(s,aux2,strlen(aux2),0);
  n=recv(s,wrbuf,10000,0);
  wrbuf[n]='\0';
  strcpy(aux1,search(wrbuf,"Key"));
  close(s);
  s=socket(r->ai_family,r->ai_socktype,r->ai_protocol);
  connect(s,r->ai_addr,r->ai_addrlen);
  sprintf(aux2,"GET /xml/current/?s=%s;callsign=%s HTTP/1.0\r\nHost: xmldata.qrz.com\r\nConnection: close\r\n\r\n",aux1,call);
  send(s,aux2,strlen(aux2),0);
  n=recv(s,wrbuf,10000,0);
  wrbuf[n]='\0';
  for(n=0;n<13;n++)strcpy(key[n],search(wrbuf,(char *)qrzkey[n]));
  if(key[6][0]!='\0'){
    now=time(NULL); utc=gmtime(&now); strftime(ee,39,"%Y-%m-%d %H:%M:%S",utc);
    sprintf(aux2,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,src) value ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%d,%d,%d,'%s','%s','qrz.com')",call,key[0],key[1],key[2],key[3],key[4],key[5],key[6],key[7],key[8],atoi(key[9]),atoi(key[10]),atoi(key[11]),key[12],ee);
    mysql_query(con,aux2);
    close(s);
    return 1;
  }
  close(s);
  return 0;
}

size_t write_cb(void *ptr,size_t size,size_t nmemb,void *userdata){
  size_t total=size*nmemb;
  if(wrused+total>=sizeof(wrbuf))total=sizeof(wrbuf)-wrused-1;
  memcpy(wrbuf+wrused,ptr,total);
  wrused+=total;
  wrbuf[wrused]=0;
  return size*nmemb;
}

char *cyrlat(char *input){
  static char output[800],*p;
  const char *cyr[]={
    "а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у",
    "ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я",
    "А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У",
    "Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я"
  };
  const char *lat[]={
    "a","b","v","g","d","e","e","zh","z","i","y","k","l","m","n","o","p","r","s","t","u",
    "f","h","ts","ch","sh","sht","i","y","y","e","yu","ya",
    "A","B","V","G","D","E","E","Zh","Z","I","Y","K","L","M","N","O","P","R","S","T","U",
    "F","H","Ts","Ch","Sh","Sht","I","Y","Y","E","Yu","Ya"
  };
  int matched;
  size_t i,clen,len;
  
  size_t n=sizeof(cyr)/sizeof(cyr[0]);
  output[0]='\0';
  p=input;
  while(*p && strlen(output)<sizeof(output)-1){
    matched=0;
    for(i=0;i<n;i++){
      clen=strlen(cyr[i]);
      if(strncmp(p,cyr[i],clen)==0){
        strncat(output,lat[i],sizeof(output)-strlen(output)-1);
        p+=clen;
        matched=1;
        break;
      }
    }
    if(!matched){
      len=strlen(output);
      if(len<sizeof(output)-1){
        output[len]=*p;
        output[len+1]='\0';
      }
      p++;
    }
  }
  return output;
}

int qrzru(MYSQL *con,char *call){
  CURL *h;
  char aux1[300],aux2[300],key[12][201],ee[40];
  int n;
  const char *qrzkey[12]={"name","surname","street","city","state","zip","country","qthloc","cq_zone","itu_zone","birthday","file"};
  time_t now;
  struct tm *utc;
  
  wrused=0;
  h=curl_easy_init();
  if(!h)return 0;
  sprintf(aux1,"https://api.qrz.ru/login?u=%s&p=%s&agent=LZH23",ruuser,rupassword);
  curl_easy_setopt(h,CURLOPT_URL,aux1);
  curl_easy_setopt(h,CURLOPT_FOLLOWLOCATION,1L);
  curl_easy_setopt(h,CURLOPT_SSL_VERIFYPEER,1L);
  curl_easy_setopt(h,CURLOPT_SSL_VERIFYHOST,2L);
  curl_easy_setopt(h,CURLOPT_WRITEFUNCTION,write_cb);
  curl_easy_perform(h);
  curl_easy_cleanup(h);
  strcpy(aux1,search(wrbuf,"session_id"));
  h=curl_easy_init();
  if(!h)return 0;
  sprintf(aux2,"https://api.qrz.ru/callsign?id=%s&callsign=%s",aux1,call);
  curl_easy_setopt(h,CURLOPT_URL,aux2);
  curl_easy_setopt(h,CURLOPT_FOLLOWLOCATION,1L);
  curl_easy_setopt(h,CURLOPT_SSL_VERIFYPEER,1L);
  curl_easy_setopt(h,CURLOPT_SSL_VERIFYHOST,2L);
  curl_easy_setopt(h,CURLOPT_WRITEFUNCTION,write_cb);
  curl_easy_perform(h);
  curl_easy_cleanup(h);
  for(n=0;n<12;n++)strcpy(key[n],cyrlat(search(wrbuf,(char *)qrzkey[n])));
  if(key[6][0]!='\0'){
    now=time(NULL); utc=gmtime(&now); strftime(ee,39,"%Y-%m-%d %H:%M:%S",utc);
    sprintf(aux2,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,src) value ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%d,%d,%d,'%s','%s','qrz.com')",call,key[0],key[1],key[2],key[3],key[4],key[5],key[6],key[7],"",atoi(key[8]),atoi(key[9]),atoi(key[10]+6),key[11],ee);
    mysql_query(con,aux2);
    return 1;
  }
  return 0;
}

double distance(double lat1,double lon1,double lat2,double lon2){
  double a;
  lat1*=M_PI/180.0;
  lon1*=M_PI/180.0;
  lat2*=M_PI/180.0;
  lon2*=M_PI/180.0;
  a=pow(sin((lat1-lat2)/2),2)+cos(lat1)*cos(lat2)*pow(sin((lon1-lon2)/2),2);
  return 6371.0*2*atan2(sqrt(a),sqrt(1-a));
}

double bearing(double lat1,double lon1,double lat2,double lon2){
  double b,dlon;
  lat1*=M_PI/180.0;
  lon1*=M_PI/180.0;
  lat2*=M_PI/180.0;
  lon2*=M_PI/180.0;
  dlon=lon2-lon1;
  b=atan2(sin(dlon)*cos(lat2),cos(lat1)*sin(lat2)-sin(lat1)*cos(lat2)*cos(dlon))*180.0/M_PI;
  if(b<0)b+=360.0;
  return b;
}

char *myts(long t){
  static char aux[10];
  if(t<3600)sprintf(aux,"%2dm",t/60);
  else if(t<86400)sprintf(aux,"%2dh",t/3600);
  else if(t<2592000)sprintf(aux,"%2dD",t/86400);
  else if(t<31536000)sprintf(aux,"%2dM",t/2592000);
  else sprintf(aux,"%2dY",t/31536000);
  return aux;
}

int nfields(char *s){
  int count=0,in_token=0;
  for(;*s;s++){
    if(isspace(*s))in_token=0;
    else if(!in_token){
      in_token=1;
      count++;
    }
  }
  return count;
}
