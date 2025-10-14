#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOTTOK 5
#define TOT2 8
#define TOT3 5
#define TOTL2 400
#define TOTL3 200000

MYSQL_ROW searchcty(MYSQL *,char *);
int incdata2(int,char *);
void incdata3(int,int,char *);
long numdata2(int,char *);
char * wpx(char *);
long min(long,long);
struct data2 {char lab[20]; long num; int idx;} **data2;
struct data3 {char lab[20]; long num;} ***data3;
int myband[434]={[0]=0,[1]=1600,[3]=800,[5]=600,[7]=400,[10]=300,[14]=200,[18]=170,[21]=150,[24]=120,[28]=100,[29]=100,[50]=60,[144]=20,[145]=20,[430]=7,[431]=7,[432]=7,[433]=7};
int *ndata2;
long **ndata3;
char *mymode(char *s){
  if(!s)return"ND";
  if(!strcmp(s,"CW"))return"CW";
  if(!strcmp(s,"FT8")||!strcmp(s,"RTTY")||!strcmp(s,"MFSK")||!strcmp(s,"FT4")||!strcmp(s,"PKT")||!strcmp(s,"TOR")||!strcmp(s,"AMTOR")||!strcmp(s,"PSK"))return"DG";
  if(!strcmp(s,"SSB")||!strcmp(s,"USB")||!strcmp(s,"LSB")||!strcmp(s,"FM")||!strcmp(s,"AM"))return"PH";
  return"ND";
}
int cmp1(const void *a,const void *b){
  const struct data2 *x=a;
  const struct data2 *y=b;
  return strcmp(x->lab,y->lab);
}
int cmp2(const void *a,const void *b){
  const struct data2 *x=a;
  const struct data2 *y=b;
  return y->num-x->num;
}
int cmp3(const void *a,const void *b){
  const struct data3 *x=a;
  const struct data3 *y=b;
  return y->num-x->num;
}

int main(void){
  int c,len,act,idx;
  char buf[1001],aux1[300],aux2[300],*token,tok[TOTTOK][100],mycall[16];
  struct tm ts,te;
  time_t epoch,td;
  long lastserial,l1,l2,suml[10];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row,row1;
  const char *l11[]={"call","band","mode","lotw","eqsl","qrz"};
 
  data2=(struct data2 **)malloc(TOT2*sizeof(struct data2 *)); ndata2=malloc(TOT2*sizeof(int));
  for(l1=0;l1<TOT2;l1++)data2[l1]=(struct data2 *)malloc(TOTL2*sizeof(struct data2));
  data3=(struct data3 ***)malloc(TOT3*sizeof(struct data3 **)); ndata3=malloc(TOT3*sizeof(long *));
  for(l1=0;l1<TOT3;l1++){
    data3[l1]=(struct data3 **)malloc(TOTL2*sizeof(struct data3 *));
    ndata3[l1]=malloc(TOTL2*sizeof(long));
    for(l2=0;l2<TOTL2;l2++)data3[l1][l2]=(struct data3 *)malloc(TOTL3*sizeof(struct data3));
  }
  for(len=0;;){
    c=getchar();
    if(c==EOF)break;
    buf[len]=(char)c;
    if(len<1000)len++;
  }
  buf[len++]='\0';
  token=strtok(buf,",");
  for(c=0;;c++){
    strcpy(tok[c],token);
    token=strtok(NULL,",");
    if(token==NULL)break;
  }
  if(c!=TOTTOK-1)exit(1);
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
  epoch=time(NULL);
  sprintf(buf,"select mycall from user where ota='%s' and lota>%ld limit 1",tok[0],epoch);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
  if(row==NULL)exit(1);
  strcpy(mycall,row[0]);
  mysql_free_result(res);
  act=0; if(tok[1][0]=='a')act=atoi(tok[1]+1);

  if(act==5){
    printf("Content-Type: text/plain\r\n\r\n");
    sprintf(buf,"select max(serial) from log where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    lastserial=atol(row[0]);
    mysql_free_result(res);
    sprintf(aux1,"%.4s-%.2s-%.2s 00:00:00",tok[4],tok[4]+5,tok[4]+8);
    sprintf(buf,"select serial from log where mycall='%s' and start>='%s' order by start limit 1",mycall,aux1);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    printf("%ld\n",lastserial-atol(row[0]));
    mysql_free_result(res);
    goto end;
  }

  if(act<=8){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    sprintf(buf,"select max(serial) from log where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    lastserial=atol(row[0]);
    mysql_free_result(res);
    sprintf(buf,"select callsign,start from log where mycall='%s' and serial=0 order by start",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      lastserial++;
      sprintf(aux1,"update log set serial=%ld where mycall='%s' and callsign='%s' and start='%s'",lastserial,mycall,row[0],row[1]);
      mysql_query(con,aux1);
    }
    mysql_free_result(res);
    if(act<=5)sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where mycall='%s' and serial<=%ld order by serial desc limit %d",mycall,lastserial-atol(tok[2]),atoi(tok[3]));
    else sprintf(buf,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest \
      from log where callsign like '%s' and mycall='%s' order by start desc limit %d offset %ld",tok[4],mycall,atoi(tok[3]),atol(tok[2]));
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      aux1[0]='\0';
      if(atoi(row[8])==1)strcat(aux1,"L");
      if(atoi(row[9])==1)strcat(aux1,"E");
      if(atoi(row[10])==1)strcat(aux1,"Q");
      sscanf(row[1],"%d-%d-%d %d:%d:%d",&te.tm_year,&te.tm_mon,&te.tm_mday,&te.tm_hour,&te.tm_min,&te.tm_sec); te.tm_year-=1900; te.tm_mon-=1;
      sscanf(row[0],"%d-%d-%d %d:%d:%d",&ts.tm_year,&ts.tm_mon,&ts.tm_mday,&ts.tm_hour,&ts.tm_min,&ts.tm_sec); ts.tm_year-=1900; ts.tm_mon-=1;
      td=mktime(&te)-mktime(&ts);
      if(td==0)strcpy(aux2,"(0s)");
      else if(td<60)sprintf(aux2,"(%lds)",td);
      else if(td<3600)sprintf(aux2,"(%ldm)",td/60);
      else sprintf(aux2,"(%ldh)",td/3600);
      // MANCA BUTTON
      printf("%s%5s %12s %7.1f %4s %5s %5s %-3s ",row[0],aux2,row[2],atol(row[3])/1000.0,row[5],row[6],row[7],aux1);
      if(row[13][0]!='\0')printf(" (%s,%s,%s)",row[13],row[11],row[12]);
      if(atol(row[4])>0&&atol(row[4])!=atol(row[3]))printf(" [%+.1f]",(atol(row[4])-atol(row[3]))/1000.0);
      printf("\n");
    }
    mysql_free_result(res);
    printf("</pre>");
    goto end;
  }
 
  if(act==9){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    l1=l2=0;
    sprintf(buf,"select start,callsign from log where mycall='%s' and dxcc=0",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      row1=searchcty(con,row[1]);
      if(row1!=NULL){
        sprintf(aux1,"Update log set dxcc=%d where mycall='%s' and start='%s' and callsign='%s' and dxcc=0",atoi(row1[2]),mycall,row[0],row[1]);
        mysql_query(con,aux1);
        l1++;
      }
      else l2++;
    }
    // MANCA PROCESSA LABEL
    mysql_free_result(res);
    printf("Set dxcc: %ld\nNot found dxcc: %ld</pre>",l1,l2);
    goto end;
  }

  if(act==10){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    for(l1=0;l1<TOT2;l1++)ndata2[l1]=0;
    for(l1=0;l1<TOT3;l1++)for(l2=0;l2<TOTL2;l2++)ndata3[l1][l2]=0;
    sprintf(buf,"select callsign,freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='%s'",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      c=(int)(atol(row[1])/1000000.0);
      if(c>433)continue;
      sprintf(aux1,"%04d%s",myband[c],mymode(row[2]));
      strcpy(aux2,wpx(row[0]));
      idx=incdata2(0,aux1);
      incdata3(1,idx,row[0]);
      incdata3(1,TOT2-1,row[0]);
      incdata3(3,idx,aux2);
      incdata3(3,TOT2-1,aux2);
      if(atoi(row[3])==1)incdata3(0,1,aux1);
      if(atoi(row[4])==1)incdata3(0,2,aux1);
      if(atoi(row[5])==1)incdata3(0,3,aux1);
      sprintf(aux1,"%03d",atoi(row[6]));
      idx=incdata2(4,aux1);
      incdata3(2,idx,row[0]);
      incdata3(4,idx,aux2);
      if(atoi(row[3])==1)incdata2(5,aux1);
      if(atoi(row[4])==1)incdata2(6,aux1);
      if(atoi(row[5])==1)incdata2(7,aux1);
    }
    mysql_free_result(res);
    qsort(data2[0],ndata2[0],sizeof(struct data2),cmp1);
    qsort(data2[4],ndata2[4],sizeof(struct data2),cmp2);
    printf("<p id=\"myh1\">%6s %7s %8s %8s %8s %8s %8s</p>","B/Mode","QSO","QSO.uniq","QSO.wpx","QSL.LOTW","QSL.EQSL","QSL.QRZ");
    for(c=0;c<4;c++)for(suml[c]=0,l1=0;l1<ndata2[c];l1++)suml[c]+=data2[c][l1].num;

    for(c=1;c<4;c++)for(suml[c]=0,l1=0;l1<ndata3[0][c];l1++)suml[c]+=data3[0][c][l1].num;

    
    printf("<p id=\"myh2\">%6s %7ld %8ld %8ld %8ld %8ld %8ld</p>","Tot",suml[0],ndata3[1][TOT2-1],ndata3[3][TOT2-1],suml[1],suml[2],suml[3]);
    for(l1=0;l1<ndata2[0];l1++)printf("%6s %7ld %8ld %8ld %8ld %8ld %8ld\n",data2[0][l1].lab,data2[0][l1].num,ndata3[1][data2[0][l1].idx],ndata3[2][data2[0][l1].idx],numdata2(1,data2[0][l1].lab),numdata2(2,data2[0][l1].lab),numdata2(3,data2[0][l1].lab));
    printf("\n");
    printf("<p id=\"myh1\">%6s %7s %8s %8s %8s %8s %8s %s</p>","dxcc","QSO","QSO.uniq","QSO.wpx","QSL.LOTW","QSL.EQSL","QSL.QRZ","Country");
    printf("<p id=\"myh2\">%6s %7d %8s %8s %8d %8d %8d</p>","Tot",ndata2[4],"","",ndata2[5],ndata2[6],ndata2[7]);
    for(l1=0;l1<ndata2[4];l1++){
      printf("%6s %7ld %8ld %8ld %8ld %8ld %8ld",data2[4][l1].lab,data2[4][l1].num,ndata3[2][data2[4][l1].idx],ndata3[4][data2[4][l1].idx],numdata2(5,data2[04][l1].lab),numdata2(6,data2[4][l1].lab),numdata2(7,data2[4][l1].lab));
      sprintf(buf,"select name from cty where dxcc='%d' limit 1",atoi(data2[4][l1].lab));
      mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
      if(row!=NULL)printf(" %s",row[0]);
      mysql_free_result(res);
      printf("\n");
    }
    printf("</pre>");
    goto end;
  }

  if(act==11){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    for(l1=0;l1<TOT2;l1++)ndata2[l1]=0;
    for(l1=0;l1<TOT3;l1++)for(l2=0;l2<TOTL2;l2++)ndata3[l1][l2]=0;
    sprintf(buf,"select callsign,freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='%s'",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      c=(int)(atol(row[1])/1000000.0);
      if(c>433)continue;
      incdata3(0,0,row[0]);
      sprintf(aux1,"%04d",myband[c]);
      incdata3(0,1,aux1);
      incdata3(0,2,row[2]);
      if(atoi(row[3])==1)incdata3(0,3,row[0]); 
      if(atoi(row[4])==1)incdata3(0,4,row[0]);
      if(atoi(row[5])==1)incdata3(0,5,row[0]);
    }
    mysql_free_result(res);
    printf("<table>");
    for(c=0;c<6;c++){
      qsort(data3[0][c],ndata3[0][c],sizeof(struct data3),cmp3);
      printf("<td><pre><b>%7s     #</b>\n",l11[c]);
      for(l1=0,l2=min(ndata3[0][c],atol(tok[3]));l1<l2;l1++)printf("%7.7s %6ld\n",data3[0][c][l1].lab,data3[0][c][l1].num);
      printf("</pre></td>");
    }
    printf("</table>");
    goto end;
  }
  
  end:
  mysql_close(con);
  return 0;
}

MYSQL_ROW searchcty(MYSQL *con,char *incall){
  char buf[1000],*p,call[20];
  static MYSQL_RES *res;
  static MYSQL_ROW row;
  int i,n;
  const char *suffixes[]={"P","M","LH","MM","AM","A","QRP","0","1","2","3","4","5","6","7","8","9"};
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
  }
  return row;
}

int incdata2(int cha,char *key){
  int n,lo,hi,mid,cmp,j;
  n=ndata2[cha];
  lo=0;
  hi=n-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data2[cha][mid].lab,key);
    if(cmp==0){
      data2[cha][mid].num++;
      return data2[cha][mid].idx;
    }
    else if(cmp<0)lo=mid+1;
    else hi=mid-1;
  }
  if(n<TOTL2){
    for(j=n;j>lo;--j)data2[cha][j]=data2[cha][j-1];
    strcpy(data2[cha][lo].lab,key);
    data2[cha][lo].idx=n;
    data2[cha][lo].num=1;
    ndata2[cha]=n+1;
  }
  return n;
}

void incdata3(int cha,int idx,char *key){
  long n,lo,hi,mid,cmp,j;
  n=ndata3[cha][idx];
  lo=0;
  hi=n-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data3[cha][idx][mid].lab,key);
    if(cmp==0){
      data3[cha][idx][mid].num++;
      return;
    }
    else if(cmp<0)lo=mid+1;
    else hi=mid-1;
  }
  if(n<TOTL3){
    for(j=n;j>lo;--j)data3[cha][idx][j]=data3[cha][idx][j-1];
    strcpy(data3[cha][idx][lo].lab,key);
    data3[cha][idx][lo].num=1;
    ndata3[cha][idx]=n+1;
  }
  return;
}

long numdata2(int cha,char *key){
  int lo,hi,mid,cmp;
  lo=0;
  hi=ndata2[cha]-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data2[cha][mid].lab,key);
    if(cmp==0)return data2[cha][mid].num;
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
