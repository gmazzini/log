#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <stdint.h>
#include <mysql/mysql.h>
#include "log.def"
#define TOT3 5
#define TOTL2 400
#define TOTL3 200000
#define MAXFF 20000000L
#define QSLWIN 240

MYSQL_ROW searchcty(MYSQL *,char *);
long incdata3(int,int,char *);
long numdata3(int,int,char *);
char * wpx(char *);
long min(long,long);
int cmp3(const void *,const void *);
char *mymode(char *);
int adifextract(char *,int);

struct data3 {char lab[20]; long num; long idx;} ***data3;
long **ndata3;
char adif[20][200],adif1[20][20];
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

int main(void){
  int c,act,vv,gg;
  char buf[1000],aux1[300],aux2[300],aux3[300],aux4[300],aux5[300],aux6[300],aux7[300],aux8[300],tok[5][100],mycall[16],*ff,*pp,*qq;
  struct tm ts,te,*tm_now;
  uint8_t in[4];
  uint32_t t;
  time_t epoch,td;
  long lastserial,l1,l2,l3,idx,suml[10],lff;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row,row1;
  FILE *fp;
  const char *l11[]={"call","band","mode","lotw","eqsl","qrz"};
 
  data3=(struct data3 ***)malloc(TOT3*sizeof(struct data3 **)); ndata3=malloc(TOT3*sizeof(long *));
  for(l1=0;l1<TOT3;l1++){
    data3[l1]=(struct data3 **)malloc(TOTL2*sizeof(struct data3 *));
    ndata3[l1]=malloc(TOTL2*sizeof(long));
    for(l2=0;l2<TOTL2;l2++)data3[l1][l2]=(struct data3 *)malloc(TOTL3*sizeof(struct data3));
  }
  ff=(char *)malloc((MAXFF+1)*sizeof(char));
  // reading elements in csv with last file ff in base64 decoded with assuntion last quartet not usefull
  for(vv=0,gg=0,lff=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<5)tok[vv][gg++]=(char)c;
    else {
      if(c=='=')break;
      in[gg]=c;
      if(gg==3){
        t=((uint32_t)B64DEC[in[0]] << 18) | ((uint32_t)B64DEC[in[1]] << 12) | ((uint32_t)B64DEC[in[2]] <<  6) | ((uint32_t)B64DEC[in[3]]);
        if(lff<MAXFF)ff[lff++]=(uint8_t)(t >> 16);
        if(lff<MAXFF)ff[lff++]=(uint8_t)(t >> 8);
        if(lff<MAXFF)ff[lff++]=(uint8_t)(t);
        gg=0;
      }
      else gg++;
    }
  }
  ff[lff]='\0';

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
      td=timegm(&te)-timegm(&ts);
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
      idx=incdata3(0,0,aux1);
      incdata3(1,idx,row[0]);
      incdata3(1,TOTL2-1,row[0]);
      incdata3(3,idx,aux2);
      incdata3(3,TOTL2-1,aux2);
      if(atoi(row[3])==1)incdata3(0,1,aux1);
      if(atoi(row[4])==1)incdata3(0,2,aux1);
      if(atoi(row[5])==1)incdata3(0,3,aux1);
      sprintf(aux1,"%03d",atoi(row[6]));
      idx=incdata3(0,4,aux1);
      incdata3(2,idx,row[0]);
      incdata3(4,idx,aux2);
      if(atoi(row[3])==1)incdata3(0,5,aux1);
      if(atoi(row[4])==1)incdata3(0,6,aux1);
      if(atoi(row[5])==1)incdata3(0,7,aux1);
    }
    mysql_free_result(res);
    printf("<p id=\"myh1\">%6s %7s %8s %8s %8s %8s %8s</p>","B/Mode","QSO","QSO.uniq","QSO.wpx","QSL.LOTW","QSL.EQSL","QSL.QRZ");
    for(c=0;c<4;c++)for(suml[c]=0,l1=0;l1<ndata3[0][c];l1++)suml[c]+=data3[0][c][l1].num;
    printf("<p id=\"myh2\">%6s %7ld %8ld %8ld %8ld %8ld %8ld</p>","Tot",suml[0],ndata3[1][TOTL2-1],ndata3[3][TOTL2-1],suml[1],suml[2],suml[3]);
    for(l1=0;l1<ndata3[0][0];l1++)printf("%6s %7ld %8ld %8ld %8ld %8ld %8ld\n",data3[0][0][l1].lab,data3[0][0][l1].num,ndata3[1][data3[0][0][l1].idx],ndata3[2][data3[0][0][l1].idx],numdata3(0,1,data3[0][0][l1].lab),numdata3(0,2,data3[0][0][l1].lab),numdata3(0,3,data3[0][0][l1].lab));
    printf("\n");
    qsort(data3[0][4],ndata3[0][4],sizeof(struct data3),cmp3);
    printf("<p id=\"myh1\">%6s %7s %8s %8s %8s %8s %8s %s</p>","dxcc","QSO","QSO.uniq","QSO.wpx","QSL.LOTW","QSL.EQSL","QSL.QRZ","Country");
    printf("<p id=\"myh2\">%6s %7ld %8s %8s %8ld %8ld %8ld</p>","Tot",ndata3[0][4],"","",ndata3[0][5],ndata3[0][6],ndata3[0][7]);
    for(l1=0;l1<ndata3[0][4];l1++){
      printf("%6s %7ld %8ld %8ld %8ld %8ld %8ld",data3[0][4][l1].lab,data3[0][4][l1].num,ndata3[2][data3[0][4][l1].idx],ndata3[4][data3[0][4][l1].idx],numdata3(0,5,data3[0][4][l1].lab),numdata3(0,6,data3[0][4][l1].lab),numdata3(0,7,data3[0][4][l1].lab));
      sprintf(buf,"select name from cty where dxcc='%d' limit 1",atoi(data3[0][4][l1].lab));
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

  if(act==12){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    for(l1=0;l1<TOT3;l1++)for(l2=0;l2<TOTL2;l2++)ndata3[l1][l2]=0;
    epoch=time(NULL);
    tm_now=gmtime(&epoch); ts=*tm_now;
    ts.tm_year-=2; timegm(&ts);
    strftime(aux3,sizeof(aux3),"%Y-%m",&ts);
    strftime(aux4,sizeof(aux4),"%Y-%m",tm_now);
    ts.tm_year+=2; ts.tm_mon-=1; timegm(&ts);
    strftime(aux5,sizeof(aux5),"%Y-%m-%d",&ts);
    strftime(aux6,sizeof(aux6),"%Y-%m-%d",tm_now);
    sprintf(buf,"select callsign,start,mode,lotw,eqsl,qrz,dxcc from log where mycall='%s'",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      strcpy(aux2,mymode(row[2]));
      sprintf(aux1,"%.4s",row[1]);
      idx=incdata3(0,0,aux1);
      incdata3(1,idx,row[0]);
      incdata3(2,idx,wpx(row[0]));
      incdata3(3,idx,row[6]);
      if(atoi(row[3])==1)incdata3(0,1,aux1);
      if(atoi(row[4])==1)incdata3(0,2,aux1);
      if(atoi(row[5])==1)incdata3(0,3,aux1);
      if(strcmp(aux2,"CW")==0)incdata3(0,4,aux1);
      if(strcmp(aux2,"DG")==0)incdata3(0,5,aux1);
      if(strcmp(aux2,"PH")==0)incdata3(0,6,aux1);
      sprintf(aux1,"%.7s",row[1]);
      if(strcmp(aux1,aux3)>=0 && strcmp(aux1,aux4)<=0){
        idx=incdata3(0,0,aux1);
        incdata3(1,idx,row[0]);
        incdata3(2,idx,wpx(row[0]));
        incdata3(3,idx,row[6]);
        if(atoi(row[3])==1)incdata3(0,1,aux1);
        if(atoi(row[4])==1)incdata3(0,2,aux1);
        if(atoi(row[5])==1)incdata3(0,3,aux1);
        if(strcmp(aux2,"CW")==0)incdata3(0,4,aux1);
        if(strcmp(aux2,"DG")==0)incdata3(0,5,aux1);
        if(strcmp(aux2,"PH")==0)incdata3(0,6,aux1);
      }
      sprintf(aux1,"%.10s",row[1]);
      if(strcmp(aux1,aux5)>=0 && strcmp(aux1,aux6)<=0){
        idx=incdata3(0,0,aux1);
        incdata3(1,idx,row[0]);
        incdata3(2,idx,wpx(row[0]));
        incdata3(3,idx,row[6]);
        if(atoi(row[3])==1)incdata3(0,1,aux1);
        if(atoi(row[4])==1)incdata3(0,2,aux1);
        if(atoi(row[5])==1)incdata3(0,3,aux1);
        if(strcmp(aux2,"CW")==0)incdata3(0,4,aux1);
        if(strcmp(aux2,"DG")==0)incdata3(0,5,aux1);
        if(strcmp(aux2,"PH")==0)incdata3(0,6,aux1);
      } 
    }
    mysql_free_result(res);
    suml[0]=4; suml[1]=7; suml[2]=10;
    strcpy(aux1,"YYYY-MM-DD");
    for(c=0;c<3;c++){
      printf("<p id=\"myh1\">%10.*s %8s %8s %8s %8s %8s %8s %8s %8s %8s %8s</p>",(int)suml[c],aux1,"QSO","QSO.cw","QSO.dg","QSO.ph","QSO.uniq","QSO.wpx","DXCC","QSL.LOTW","QSL.EQSL","QSL.QRZ");
      for(l1=ndata3[0][0]-1;l1>0;l1--){
        if(strlen(data3[0][0][l1].lab)==suml[c]){
          printf("%10s %8ld %8ld %8ld %8ld",data3[0][0][l1].lab,data3[0][0][l1].num,numdata3(0,4,data3[0][0][l1].lab),numdata3(0,5,data3[0][0][l1].lab),numdata3(0,6,data3[0][0][l1].lab));
          printf(" %8ld %8ld %8ld",ndata3[1][data3[0][0][l1].idx],ndata3[2][data3[0][0][l1].idx],ndata3[3][data3[0][0][l1].idx]);
          printf(" %8ld %8ld %8ld\n",numdata3(0,1,data3[0][0][l1].lab),numdata3(0,2,data3[0][0][l1].lab),numdata3(0,3,data3[0][0][l1].lab));
        }
      }
    }
    printf("</pre>");
    goto end;
  }

  if(act==13 || act==14){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    epoch=time(NULL);
    tm_now=gmtime(&epoch); ts=*tm_now;
    ts.tm_mon-=(act==13)?1:6; 
    timegm(&ts);
    strftime(aux3,sizeof(aux3),"%Y-%m-%d %H:%M:%S",&ts);
    sprintf(buf,"select serial from log where mycall='%s' and start>='%s' order by start limit 1",mycall,aux3);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    l1=(row==NULL)?1:atol(row[0]);
    mysql_free_result(res);
    printf("<pre>");
    sprintf(buf,"select callsign,start from log where mycall='%s' and start>='%s' order by start",mycall,aux3);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(l2=l1;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      sprintf(aux1,"update log set serial=%ld where mycall='%s' and callsign='%s' and start='%s'",l1,mycall,row[0],row[1]);
      mysql_query(con,aux1);
      l1++;
    }
    res=mysql_store_result(con);
    printf("Serialized QSO: %ld\n",l1-l2);
    printf("</pre>");
    goto end;
  }

  if(act>=17 && act<=19){
    strcpy(adif1[0],"CALL"); strcpy(adif1[1],"TIME_ON"); strcpy(adif1[2],"QSO_DATE");
    if(act==17){strcpy(adif1[3],"APP_LoTW_RXQSL"); strcpy(aux4,"lotw");}
    else if(act==18){strcpy(adif1[3],"EQSL_QSLRDATE"); strcpy(aux4,"eqsl");}
    else if(act==19){strcpy(adif1[3],"app_qrzlog_status"); strcpy(aux4,"qrz");}
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    vv=4; gg=adifextract(ff,vv);
    for(;gg>0;){
      sscanf(adif[2],"%4ld%2ld%2ld",&l1,&l2,&l3); ts.tm_year=l1-1900; ts.tm_mon=l2-1; ts.tm_mday=l3;
      l3=0; sscanf(adif[1],"%2ld%2ld%2ld",&l1,&l2,&l3); ts.tm_hour=l1; ts.tm_min=l2; ts.tm_sec=l3;
      epoch=timegm(&ts);
      epoch-=QSLWIN; strftime(aux1,sizeof(aux1),"%Y-%m-%d %H:%M:%S",gmtime(&epoch));
      epoch+=2*QSLWIN; strftime(aux2,sizeof(aux2),"%Y-%m-%d %H:%M:%S",gmtime(&epoch));
      if(adif[3][0]!='\0'){
        sprintf(buf,"update log set %s=1 where mycall='%s' and callsign='%s' and start>='%s' and start<='%s'",aux4,mycall,adif[0],aux1,aux2);
        mysql_query(con,buf);
        printf("%s\n",buf);
      }
      gg=adifextract(NULL,vv);
    }  
    printf("</pre>");
    goto end;
  }

  if(act==15){
    strcpy(adif1[0],"call"); strcpy(adif1[1],"freq"); strcpy(adif1[2],"freq_rx"); strcpy(adif1[3],"rst_sent"); strcpy(adif1[4],"rst_rcvd"); strcpy(adif1[5],"mode");
    strcpy(adif1[6],"time_on"); strcpy(adif1[7],"time_off"); strcpy(adif1[8],"stx_string"); strcpy(adif1[9],"stx"); strcpy(adif1[10],"srx_string"); strcpy(adif1[11],"srx");
    strcpy(adif1[12],"contest_id"); strcpy(adif1[13],"qso_date"); strcpy(adif1[14],"qso_date_off");
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    vv=15; gg=adifextract(ff,vv);
    for(;gg>0;){
      if(adif[6][4]=='\0'){adif[6][4]='0'; adif[6][5]='0'; adif[6][6]='\0';}
      sprintf(aux1,"%.4s-%.2s-%.2s %.2s:%.2s:%.2s",adif[13],adif[13]+4,adif[13]+6,adif[6],adif[6]+2,adif[6]+4);
      if(adif[14][0]=='\0')strcpy(adif[14],adif[13]);
      if(adif[7][0]=='\0')strcpy(adif[7],adif[6]);
      if(adif[7][4]=='\0'){adif[7][4]='0'; adif[7][5]='0'; adif[7][6]='\0';}
      sprintf(aux2,"%.4s-%.2s-%.2s %.2s:%.2s:%.2s",adif[14],adif[14]+4,adif[14]+6,adif[7],adif[7]+2,adif[7]+4);
      sprintf(aux3,"('%s','%s','%s','%s','%s',%ld,%ld,'%s','%s','%s','%s','%s')",mycall,adif[0],aux1,aux2,adif[5],(long)(atof(adif[1])*1000000.0),(long)(atof(adif[2])*1000000.0),adif[3],adif[4],(adif[8][0]=='\0')?adif[9]:adif[8],(adif[10][0]=='\0')?adif[11]:adif[10],adif[12]);
      printf("%s\n",aux3);
      sprintf(buf,"insert ignore into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value %s",aux3);
      mysql_query(con,buf);
      gg=adifextract(NULL,vv);
    }  
    printf("</pre>");
    goto end;
  }

   if(act==20){
     strcpy(adif1[0],"export_from"); strcpy(adif1[1],"export_to"); strcpy(adif1[2],"export_contest");
     printf("Status: 200 OK\r\n");
     printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
     vv=3; gg=adifextract(ff,vv);
     if(gg==0)goto end;
     srand((unsigned)time(NULL));
     sprintf(aux1,"%d%d%d%d.adi",rand(),rand(),rand(),rand());
     sprintf(aux2,"/home/www/log/files/%s",aux1);
     fp=fopen(aux2,"w");
     strcpy(aux3,"PROGRAMID"); fprintf(fp,"<LZHlogger:%d>%s\n",strlen(aux3),aux3);
     fprintf(fp,"<EOH>\n\n");
     if(adif[2][0]=='\0')sprintf(buf,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx,contest from log where mycall='%s' and start>='%s' and start<='%s' order by start",mycall,adif[0],adif[1]);
     else sprintf(buf,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx,contest from log where mycall='%s' and contest='%s' order by start",mycall,adif[2]);
     mysql_query(con,buf);
     res=mysql_store_result(con);
     for(l1=0;;l1++){
       row=mysql_fetch_row(res);
       if(row==NULL)break;
       fprintf(fp,"<CALL:%d>%s\n",strlen(row[1]),row[1]);
       fprintf(fp,"<QSO_DATE:8>%.4s%.2s%.2s\n",row[0],row[0]+5,row[0]+8);
       fprintf(fp,"<QSO_DATE_OFF:8>%.4s%.2s%.2s\n",row[6],row[6]+5,row[6]+8);
       fprintf(fp,"<TIME_ON:6>%.2s%.2s%.2s\n",row[0]+11,row[0]+14,row[0]+17);
       fprintf(fp,"<TIME_OFF:6>%.2s%.2s%.2s\n",row[6]+11,row[6]+14,row[6]+17);
       sprintf(aux4,"%7.5f",atol(row[2])/1000000.0); fprintf(fp,"<FREQ:%d>%s\n",strlen(aux4),aux4);
       sprintf(aux4,"%7.5f",atol(row[7])/1000000.0); fprintf(fp,"<FREQ_RX:%d>%s\n",strlen(aux4),aux4);
       fprintf(fp,"<MODE:%d>%s\n",strlen(row[3]),row[3]);
       fprintf(fp,"<RST_SENT:%d>%s\n",strlen(row[4]),row[4]);
       fprintf(fp,"<RST_RCVD:%d>%s\n",strlen(row[5]),row[5]);
       fprintf(fp,"<STX_STRING:%d>%s\n",strlen(row[8]),row[8]);
       fprintf(fp,"<SRX_STRING:%d>%s\n",strlen(row[9]),row[9]);
       fprintf(fp,"<CONTEST_ID:%d>%s\n",strlen(row[10]),row[10]);
       fprintf(fp,"\n");
     }
     res=mysql_store_result(con);
     fclose(fp);
     printf("<pre>");
     printf("<pre><a href='https://log.mazzini.org/files/%s' download>Download ADIF</a>\n",aux1);
     if(adif[2][0]=='\0')printf("from:%s to:%s\n",adif[0],adif[1]);
     else printf("contest:%s\n",adif[2]);
     printf("</pre>");
     goto end;
  }

  if(act==21){
    strcpy(adif1[0],"export_from"); strcpy(adif1[1],"export_to"); strcpy(adif1[2],"export_contest");
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    vv=3; gg=adifextract(ff,vv);
    if(gg==0)goto end;
    srand((unsigned)time(NULL));
    sprintf(aux1,"%d%d%d%d.cbr",rand(),rand(),rand(),rand());
    sprintf(aux2,"/home/www/log/files/%s",aux1);
    fp=fopen(aux2,"w");
    fprintf(fp,"START-OF-LOG: 3.0\nCREATED-BY: IK4LZH logger\n");
    fprintf(fp,"CONTEST: xxxxxx\nCALLSIGN: %s\nOPERATORS: %s\n",mycall,mycall);
    fprintf(fp,"CATEGORY-OPERATOR: SINGLE-OP\nCATEGORY-ASSISTED: ASSISTED\nCATEGORY-BAND: ALL\nCATEGORY-POWER: LOW\nCATEGORY-TRANSMITTER: ONE\n");    
    sprintf(buf,"select firstname,lastname,addr1,addr2,state,zip,country,email from who where callsign='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    fprintf(fp,"NAME: %s %s\n",row[0],row[1]);
    if(row[7][0]!='\0')fprintf(fp,"EMAIL: %s\n",row[7]);
    if(row[2][0]!='\0')fprintf(fp,"ADDRESS: %s\n",row[2]);
    if(row[3][0]!='\0')fprintf(fp,"ADDRESS-CITY: %s\n",row[3]);
    if(row[4][0]!='\0')fprintf(fp,"ADDRESS-STATE-PROVINCE: %s\n",row[4]);
    if(row[5][0]!='\0')fprintf(fp,"ADDRESS-POSTALCODE: %s\n",row[5]);
    if(row[6][0]!='\0')fprintf(fp,"ADDRESS-COUNTRY: %s\n",row[6]);
    fprintf(fp,"CLUB: Italian Contest Club\n");
    mysql_free_result(res);
    if(adif[2][0]=='\0')sprintf(buf,"select start,callsign,freqtx,mode,signaltx,signalrx,contesttx,contestrx from log where mycall='%s' and start>='%s' and start<='%s' order by start",mycall,adif[0],adif[1]);
    else sprintf(buf,"select start,callsign,freqtx,mode,signaltx,signalrx,contesttx,contestrx from log where mycall='%s' and contest='%s' order by start",mycall,adif[2]);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(l1=0;;l1++){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      fprintf(fp,"QSO: %5ld %2s %.4s-%.2s-%.2s %.2s%.2s",atol(row[2])/1000L,mymode(row[3]),row[0],row[0]+5,row[0]+8,row[0]+11,row[0]+14);
      fprintf(fp," %-13s %3s %-6s %-13s %3s %-6s 0\n",mycall,row[4],row[6],row[1],row[5],row[7]);
    }
    res=mysql_store_result(con);
    fprintf(fp,"END-OF-LOG:\n");
    fclose(fp);
    printf("<pre>");
    printf("<pre><a href='https://log.mazzini.org/files/%s' download>Download Cabrillo</a>\n",aux1);
    if(adif[2][0]=='\0')printf("from:%s to:%s\n",adif[0],adif[1]);
    else printf("contest:%s\n",adif[2]);
    printf("</pre>");
    goto end;
  }
  
  if(act==16){
    printf("Status: 200 OK\r\n");
    printf("Content-Type: text/html; charset=utf-8\r\n\r\n");
    printf("<pre>");
    aux1[0]=aux2[0]=aux3[0]='\0';
    pp=strtok(ff,"\n");
    for(;;){
      if(pp==NULL)break;
      if(pp[0]=='D')strcpy(aux1,pp+1);
      else if(pp[0]=='F')strcpy(aux2,pp+1);
      else if(pp[0]=='M')strcpy(aux3,pp+1);
      else if(aux1[0]!='\0' && aux2[0]!='\0' && aux3[0]!='\0'){
        aux7[0]=aux8[0]='\0'; sscanf(pp,"%s %s %s %s",aux5,aux6,aux7,aux8);
        for(qq=aux6;*qq!='\0';qq++)*qq=toupper(*qq);
        sprintf(aux4,"%.4s-%.2s-%.2s %.2s:%.2s",aux1,aux1+4,aux1+6,aux5,aux5+2);
        sprintf(buf,"('%s','%s','%s:00','%s:59','%s',%ld,%ld,'','','','','')",mycall,aux6,aux4,aux4,aux3,atol(aux2)*1000L,atol(aux2)*1000L);
        printf("%s\n",buf);
      }
      pp=strtok(NULL,"\n");
    }
    printf("</pre>");
    goto end;
  }

//     echo "('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','','','')\n";

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

long incdata3(int cha,int idx,char *key){
  long n,lo,hi,mid,cmp,j;
  n=ndata3[cha][idx];
  lo=0;
  hi=n-1;
  while(lo<=hi){
    mid=lo+(hi-lo)/2;
    cmp=strcmp(data3[cha][idx][mid].lab,key);
    if(cmp==0){
      data3[cha][idx][mid].num++;
      return data3[cha][idx][mid].idx;;
    }
    else if(cmp<0)lo=mid+1;
    else hi=mid-1;
  }
  if(n<TOTL3){
    for(j=n;j>lo;--j)data3[cha][idx][j]=data3[cha][idx][j-1];
    strcpy(data3[cha][idx][lo].lab,key);
    data3[cha][idx][lo].idx=n;
    data3[cha][idx][lo].num=1;
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
